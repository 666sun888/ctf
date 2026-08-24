#!/usr/bin/env node
/**
 * configure-ark-agentplan.cjs
 * ------------------------------------------------------------------
 * 一键把 DeepSeek Harness (DSH) 指向火山方舟 (Volcano Ark) Agent Plan：
 *   1. 输入方舟 API Key
 *   2. 自动调用方舟 /models 列出该 Key 可访问的全部模型（即套餐覆盖的模型）
 *   3. 自动写入 DSH 配置：
 *        - DeepSeek 系列模型  -> deepseek-official 路由（走 DSH 原生 DeepSeek 适配器）
 *        - 其他模型 (doubao/glm/... ) -> pi-ai 自定义路由 `ark`（纯 OpenAI 兼容协议）
 *        - 默认模型设为套餐里的 deepseek-v4-flash（不存在则选第一个 deepseek*）
 *        - API Key 写入 $DSH_HOME/.credentials.yaml 的 ARK_API_KEY
 *   4. 可选 --test：发一条真实请求验证连通
 *
 * 用法：
 *   node configure-ark-agentplan.cjs --api-key <你的方舟APIKey>
 *   node configure-ark-agentplan.cjs                          # 交互式输入 Key（不回显）
 *   node configure-ark-agentplan.cjs --models "deepseek-v4-flash,deepseek-v3-250324" --api-key <key>
 *        # 方舟 /models 列表失败时手动指定模型 ID
 *   node configure-ark-agentplan.cjs --reset                  # 恢复最近一次备份
 *
 * 其他选项：
 *   --base-url <url>   默认 https://ark.cn-beijing.volces.com/api/v3
 *   --no-reasoning     关闭思考模式（deepseek 路由只发 thinking:disabled，兼容性最好）
 *   --compat-openai    纯 OpenAI 兼容模式：所有模型走 pi-ai 路由，思考模式关闭
 *   --test             配置完成后发一条真实请求验证
 *   --home <dir>       指定 DSH 家目录（默认 $DSH_HOME 或 ~/.dsh）
 *   --help             帮助
 */

'use strict';

const fs = require('fs');
const os = require('os');
const path = require('path');
const readline = require('readline');

// ---------------------------------------------------------------- 参数解析
function parseArgs(argv) {
  const args = { models: null, reasoning: true, compatOpenAI: false, test: false, reset: false, home: null, baseUrl: 'https://ark.cn-beijing.volces.com/api/v3', apiKey: null, help: false };
  for (let i = 0; i < argv.length; i++) {
    const a = argv[i];
    const next = () => argv[++i];
    switch (a) {
      case '--api-key': args.apiKey = next(); break;
      case '--base-url': args.baseUrl = next().replace(/\/+$/, ''); break;
      case '--models': args.models = next().split(',').map(s => s.trim()).filter(Boolean); break;
      case '--home': args.home = next(); break;
      case '--no-reasoning': args.reasoning = false; break;
      case '--compat-openai': args.compatOpenAI = true; break;
      case '--test': args.test = true; break;
      case '--reset': args.reset = true; break;
      case '--help': case '-h': args.help = true; break;
      default:
        console.error(`未知参数: ${a}（用 --help 查看用法）`);
        process.exit(2);
    }
  }
  return args;
}

const HELP = `
configure-ark-agentplan.cjs — 一键配置 DeepSeek Harness 使用火山方舟 Agent Plan

用法:
  node configure-ark-agentplan.cjs --api-key <方舟APIKey>     # 直接传 Key
  node configure-ark-agentplan.cjs                            # 交互式输入（不回显）
  node configure-ark-agentplan.cjs --reset                    # 恢复最近一次备份

选项:
  --api-key <key>   方舟 API Key（也可用环境变量 ARK_API_KEY）
  --base-url <url>  方舟 endpoint，默认 https://ark.cn-beijing.volces.com/api/v3
  --models <ids>    跳过自动发现，手动指定模型 ID（逗号分隔，控制台"开通管理"里查）
  --no-reasoning    关闭思考模式（deepseek 只发 thinking:disabled）
  --compat-openai   纯 OpenAI 兼容模式：全部模型走 pi-ai 路由，思考关闭
  --test            配置后发一条真实请求验证连通性
  --home <dir>      DSH 家目录（默认 $DSH_HOME 或 ~/.dsh）
  --help            本帮助

说明:
  - 自动调用 GET {base}/models 发现该 Key 可访问的全部模型；
    如果方舟侧列表接口不可用，请用 --models 手动指定
    （模型 ID 在方舟控制台 → 开通管理 中查看）。
  - 写入前会自动备份 settings.yaml 和 .credentials.yaml。
  - Key 只写入本地 .credentials.yaml（GUI 界面只写不读），不会上传任何地方。
`;

// ---------------------------------------------------------------- 工具函数
function log(step, msg) { console.log(`\x1b[36m[${step}]\x1b[0m ${msg}`); }
function ok(msg) { console.log(`\x1b[32m[OK]\x1b[0m ${msg}`); }
function warn(msg) { console.log(`\x1b[33m[注意]\x1b[0m ${msg}`); }
function fail(msg) { console.error(`\x1b[31m[失败]\x1b[0m ${msg}`); process.exit(1); }

function resolveDshHome(args) {
  if (args.home) return path.resolve(args.home);
  if (process.env.DSH_HOME) return path.resolve(process.env.DSH_HOME);
  return path.join(os.homedir(), '.dsh');
}

function resolveYaml(home) {
  const candidates = [
    path.join(home, 'profiles', 'node_modules', 'yaml'),
    path.join(__dirname, 'node_modules', 'yaml'),
  ];
  try {
    const root = require('child_process').execSync('npm root -g', { encoding: 'utf8' }).trim();
    if (root) {
      candidates.push(path.join(root, '@deepseek-ai', 'dsh', 'node_modules', 'yaml'));
      candidates.push(path.join(root, 'yaml'));
    }
  } catch { /* npm 不可用时忽略 */ }
  candidates.push('yaml');
  for (const c of candidates) {
    try { return require(c); } catch { /* 继续尝试 */ }
  }
  fail('找不到 yaml 包。请通过 npm 全局安装 @deepseek-ai/dsh 后再运行本脚本。');
}

async function promptSecret(question) {
  return new Promise((resolvePromise) => {
    const stdin = process.stdin;
    const wasRaw = stdin.isRaw;
    let buf = '';
    process.stdout.write(question);
    stdin.setRawMode(true);
    stdin.resume();
    stdin.setEncoding('utf8');
    const onData = (chunk) => {
      for (const ch of chunk) {
        if (ch === '\u0003') { // Ctrl+C
          cleanup();
          process.stdout.write('\n');
          process.exit(130);
        } else if (ch === '\r' || ch === '\n') {
          cleanup();
          process.stdout.write('\n');
          resolvePromise(buf);
          return;
        } else if (ch === '\u007f' || ch === '\b') {
          buf = buf.slice(0, -1);
        } else {
          buf += ch;
        }
      }
    };
    const cleanup = () => {
      stdin.removeListener('data', onData);
      stdin.setRawMode(wasRaw);
      stdin.pause();
    };
    stdin.on('data', onData);
  });
}

async function acquireApiKey(args) {
  if (args.apiKey) return args.apiKey.trim();
  if (process.env.ARK_API_KEY) return process.env.ARK_API_KEY.trim();
  const key = (await promptSecret('请输入火山方舟 API Key（输入时不回显，回车确认）: ')).trim();
  if (!key) fail('未输入 API Key。');
  return key;
}

async function fetchJson(url, options, timeoutMs = 30000) {
  const ctrl = new AbortController();
  const timer = setTimeout(() => ctrl.abort(), timeoutMs);
  try {
    const resp = await fetch(url, { ...options, signal: ctrl.signal });
    const text = await resp.text();
    let body = null;
    try { body = JSON.parse(text); } catch { body = text; }
    return { status: resp.status, ok: resp.ok, body };
  } finally {
    clearTimeout(timer);
  }
}

// 列出该 Key 可访问的全部模型（OpenAI 兼容 GET /models）
async function discoverModels(baseUrl, apiKey) {
  log('发现模型', `GET ${baseUrl}/models`);
  const { status, ok, body } = await fetchJson(`${baseUrl}/models`, {
    headers: { Authorization: `Bearer ${apiKey}` },
  });
  if (!ok) {
    const detail = (body && (body.error || body.message)) ? JSON.stringify(body.error || body.message) : '';
    if (status === 401 || status === 403) {
      warn(`方舟返回 ${status}（${detail}）：Key 无效或无权访问。请检查 API Key，或控制台确认 Agent Plan 已开通。`);
    } else {
      warn(`方舟模型列表接口返回 ${status}（${detail}）。`);
    }
    warn('请从方舟控制台 → 开通管理 复制套餐模型 ID，然后用 --models "id1,id2,..." 重跑。');
    process.exit(1);
  }
  const data = (body && Array.isArray(body.data)) ? body.data : [];
  const ids = data
    .map(m => (m && typeof m.id === 'string') ? m.id.trim() : null)
    .filter(Boolean)
    .filter(id => !id.startsWith('ep-') && !id.startsWith('bot-')) // 去掉用户创建的接入点/智能体
    .filter((id, i, arr) => arr.indexOf(id) === i);
  if (ids.length === 0) {
    warn('列表接口返回成功但没有可用的模型 ID。');
    warn('请从方舟控制台 → 开通管理 复制套餐模型 ID，然后用 --models "id1,id2,..." 重跑。');
    process.exit(1);
  }
  ok(`发现 ${ids.length} 个可访问模型`);
  return ids;
}

function pickPreferredModel(ids) {
  const pref = ['deepseek-v4-flash', 'deepseek-v4-pro', 'deepseek-v3', 'deepseek-r1'];
  for (const p of pref) {
    const hit = ids.find(id => id === p || id.startsWith(p + '-') || id.startsWith(p + '_'));
    if (hit) return hit;
  }
  return ids[0];
}

// 备份文件
function backupFile(file) {
  if (!fs.existsSync(file)) return null;
  const stamp = new Date().toISOString().replace(/[-:T]/g, '').slice(0, 14);
  const bak = `${file}.bak-${stamp}`;
  fs.copyFileSync(file, bak);
  return bak;
}

function listBackups(file) {
  const dir = path.dirname(file);
  const base = path.basename(file);
  if (!fs.existsSync(dir)) return [];
  return fs.readdirSync(dir)
    .filter(f => f.startsWith(base + '.bak-'))
    .map(f => path.join(dir, f))
    .sort();
}

// 更新 .credentials.yaml 中的 ARK_API_KEY（保留其它条目原样）
function writeCredentials(file, apiKey) {
  let lines = fs.existsSync(file) ? fs.readFileSync(file, 'utf8').split(/\r?\n/) : [];
  let found = false;
  lines = lines.map(line => {
    if (/^ARK_API_KEY\s*:/.test(line)) { found = true; return `ARK_API_KEY: ${apiKey}`; }
    return line;
  });
  if (!found) {
    if (lines.length && lines[lines.length - 1].trim() !== '') lines.push('');
    lines.push(`ARK_API_KEY: ${apiKey}`);
  }
  const out = lines.join('\n').replace(/\n+$/, '\n');
  fs.writeFileSync(file, out, 'utf8');
}

// ---------------------------------------------------------------- 主流程
async function main() {
  const args = parseArgs(process.argv.slice(2));
  if (args.help) { console.log(HELP); return; }

  const home = resolveDshHome(args);
  const settingsFile = path.join(home, 'settings.yaml');
  const credentialsFile = path.join(home, '.credentials.yaml');

  if (args.reset) {
    const s = listBackups(settingsFile);
    const c = listBackups(credentialsFile);
    if (!s.length && !c.length) fail('没有找到备份，无法恢复。');
    const src = s.length ? s[0] : null;   // 最旧备份 = 脚本首次运行前的原始状态
    const crc = c.length ? c[0] : null;
    if (src) { fs.copyFileSync(src, settingsFile); ok(`已恢复 ${settingsFile} ← ${path.basename(src)}`); }
    if (crc) { fs.copyFileSync(crc, credentialsFile); ok(`已恢复 ${credentialsFile} ← ${path.basename(crc)}`); }
    log('完成', '刷新 DSH Web 页面即可生效。');
    return;
  }

  if (!fs.existsSync(settingsFile)) {
    fail(`找不到 ${settingsFile}\n请先至少运行过一次 dsh（web 或 headless），或使用 --home 指定正确的 DSH 家目录。`);
  }

  log('DSH 家目录', home);
  const YAML = resolveYaml(home);

  // 1. API Key
  const apiKey = await acquireApiKey(args);
  if (!/^[\x21-\x7E]+$/.test(apiKey)) fail('API Key 含有非法字符（必须是可打印 ASCII）。');

  // 2. 模型列表（自动发现或手动指定）
  let allIds;
  if (args.models && args.models.length) {
    allIds = [...new Set(args.models)];
    ok(`使用手动指定的 ${allIds.length} 个模型`);
  } else {
    allIds = await discoverModels(args.baseUrl, apiKey);
  }

  const deepseekIds = allIds.filter(id => /^deepseek/i.test(id));
  const otherIds = allIds.filter(id => !/^deepseek/i.test(id));
  const preferred = pickPreferredModel(allIds);

  // 3. 备份
  const bakS = backupFile(settingsFile);
  const bakC = backupFile(credentialsFile);
  if (bakS) ok(`已备份 ${path.basename(bakS)}`);
  if (bakC) ok(`已备份 ${path.basename(bakC)}`);

  // 4. 组装配置（深度合并，保留其它配置不动）
  let settings;
  try {
    settings = YAML.parse(fs.readFileSync(settingsFile, 'utf8')) || {};
  } catch (e) {
    fail(`解析 ${settingsFile} 失败: ${e.message}`);
  }

  const reasoningEffort = args.reasoning ? 'high' : 'off';
  const defaultProvider = (args.compatOpenAI || deepseekIds.length === 0) ? 'ark' : 'deepseek-official';

  // 4a. deepseek 路由（原生 DeepSeek 适配器，支持思考模式）
  if (args.compatOpenAI) {
    // 纯 OpenAI 兼容模式：移除 llm-deepseek 段，避免 DeepSeek 方言路由与纯 OpenAI 路由并存造成歧义
    delete settings['llm-deepseek'];
    if (deepseekIds.length > 0) warn('--compat-openai：DeepSeek 模型也走纯 OpenAI 兼容路由，思考模式关闭。');
  } else if (deepseekIds.length > 0) {
    const base = settings['llm-deepseek'] || {};
    settings['llm-deepseek'] = {
      ...base,
      baseURL: args.baseUrl,
      apiKeyEnv: 'ARK_API_KEY',
      reasoningEffort,
      models: deepseekIds.map(id => ({ id, name: id })),
    };
    ok(`deepseek-official 路由: ${deepseekIds.length} 个 DeepSeek 模型（reasoning=${reasoningEffort}）`);
  }

  // 4b. pi-ai 路由 `ark`（纯 OpenAI 兼容，服务 doubao/glm 等非 DeepSeek 模型）
  const arkModels = args.compatOpenAI ? allIds : otherIds;
  if (arkModels.length > 0) {
    const piBase = settings['llm-pi-ai'] || {};
    const provBase = (piBase.providers && typeof piBase.providers === 'object') ? piBase.providers : {};
    settings['llm-pi-ai'] = {
      ...piBase,
      providers: {
        ...provBase,
        ark: {
          displayName: '火山方舟 Agent Plan',
          api: 'openai-completions',
          baseURL: args.baseUrl,
          apiKeyEnv: 'ARK_API_KEY',
          models: arkModels.map(id => ({ id, name: id })),
        },
      },
    };
    ok(`pi-ai 路由 ark: ${arkModels.length} 个 OpenAI 兼容模型`);
  }

  // 4c. 默认模型
  const defBase = settings['agent-default-model'] || {};
  // compat 模式下 ark 路由模型无推理能力声明，effort 必须为 off，否则请求会被拒
  const defReasoning = (args.reasoning && !args.compatOpenAI) ? (defBase.reasoningEffort || 'high') : 'off';
  settings['agent-default-model'] = {
    provider: defaultProvider,
    model: preferred,
    reasoningEffort: defReasoning,
  };
  ok(`默认模型: ${defaultProvider} / ${preferred} (reasoningEffort=${defReasoning})`);

  // 5. 写入
  fs.writeFileSync(settingsFile, YAML.stringify(settings, { indent: 2 }) + '\n', 'utf8');
  writeCredentials(credentialsFile, apiKey);
  ok(`已写入 ${settingsFile}`);
  ok(`已写入 ${credentialsFile}（ARK_API_KEY）`);

  // 6. 可选连通性测试（纯 OpenAI 请求，不带推理参数）
  if (args.test) {
    log('测试', `POST ${args.baseUrl}/chat/completions (model=${preferred})`);
    const { status, ok: isOk, body } = await fetchJson(`${args.baseUrl}/chat/completions`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${apiKey}`, 'Content-Type': 'application/json' },
      body: JSON.stringify({
        model: preferred,
        messages: [{ role: 'user', content: '连通性测试，请回复 OK' }],
        max_tokens: 16,
        stream: false,
      }),
    }, 60000);
    if (isOk) {
      const text = body && body.choices && body.choices[0] && body.choices[0].message && body.choices[0].message.content;
      ok(`测试通过: ${String(text || '').trim().slice(0, 80)}`);
    } else {
      const err = (body && (body.error || body.message)) ? JSON.stringify(body.error || body.message) : body;
      warn(`测试请求返回 ${status}: ${String(err).slice(0, 300)}`);
      warn('配置已写入；若为参数类错误可加 --no-reasoning 或 --compat-openai 重跑。');
    }
  }

  // 7. 汇总
  console.log('\n' + '='.repeat(64));
  log('完成', '火山方舟 Agent Plan 配置已生效');
  if (args.compatOpenAI) {
    console.log(`  模型路由: ark (${arkModels.length} 个模型，纯 OpenAI 兼容，思考关闭)`);
  } else {
    console.log(`  模型路由: deepseek-official (${deepseekIds.length} 个 DeepSeek)${otherIds.length ? ` + ark (${otherIds.length} 个其他)` : ''}`);
  }
  console.log(`  默认模型: ${defaultProvider} / ${preferred}`);
  console.log('  下一步: 刷新 DSH Web 页面 (http://127.0.0.1:3080)，在模型选择器里即可挑选套餐内任意模型。');
  console.log(`  恢复:   node ${path.basename(process.argv[1])} --reset`);
  console.log('  注意:   模型列表来自该 Key 的可访问范围；若其中包含按量计费（非套餐）模型，使用时会额外计费。');
  console.log('='.repeat(64));
}

main().catch(e => {
  console.error('\x1b[31m[异常]\x1b[0m', e && e.stack ? e.stack : e);
  process.exit(1);
});
