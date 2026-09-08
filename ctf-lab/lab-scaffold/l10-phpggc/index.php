<?php
// ============================================
// L10 挑战：日志站 · 配置导入功能
// 情报（侦查材料）：
//   - 本站使用流行的 PHP 日志库记录访问日志（autoload 见下方源码）
//   - 运维失误：composer.lock 原样放在网站根目录，可以直接访问
//   - 靶机是 Windows PHP，读文件用 type 命令（不是 cat）
// 通关条件：RCE 读出 flag_l10.txt（就在本目录）
// ============================================
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

// 真实库加载：PSR-4 自动装载（真实世界里框架都这么装库，vendor 树含依赖）
spl_autoload_register(function ($class) {
    $map = array(
        'Monolog\\' => __DIR__ . '/vendor/monolog/monolog/src/Monolog/',
        'Psr\\Log\\' => __DIR__ . '/vendor/psr/log/Psr/Log/',
    );
    foreach ($map as $prefix => $dir) {
        if (strpos($class, $prefix) === 0) {
            $path = $dir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($path)) require $path;
            return;
        }
    }
});

$d = isset($_REQUEST['d']) ? $_REQUEST['d'] : '';
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES); }
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>日志站 · 配置导入</title></head>
<body style="font-family: sans-serif; max-width: 760px; margin: 40px auto;">
<h1>📋 日志站（L10 挑战）</h1>

<?php if ($d === ''): ?>
<p>本站用 <strong>Monolog</strong> 系日志组件记录访问日志。下面这个"旧配置导入"功能是从报废机器上搬过来的：</p>

<h3>源码（全透明，随便审计）：</h3>
<pre style="background:#f6f6f6; padding:10px;">&lt;?php
// 真实库已随站点装载（vendor/ 目录里躺着完整的 Monolog 源码）
$d = $_REQUEST['d'];                        // ← 你控制的 base64 串
$obj = @unserialize(base64_decode($d));     // ← 反序列化点：造出什么对象全看串
if (is_object($obj)) {
    echo "配置对象：" . get_class($obj) . " 已导入";
}                                           // ← 注意：没有任何方法调用——
?&gt;                                          //    对象在脚本结束时自己"谢幕"</pre>

<h3>情报：</h3>
<ul>
<li>导入的串是 <strong>base64</strong>（为什么？想想 protected 属性里那些 \0 字节怎么过 URL）。</li>
<li>靶面上没有任何自定义业务类——但 <strong>vendor 目录里躺着整个真库</strong>。</li>
<li>运维把 <code>composer.lock</code> 留在了网站根目录：<a href="/composer.lock">/composer.lock</a>（看看能认出什么）。</li>
<li>你手上应该已经有一个"弹药工厂"了（本课发的装备）。认库 → 选链 → 生成 → 发射。</li>
<li>靶机是 Windows：读文件 <code>type flag_l10.txt</code>，列目录 <code>dir</code>。</li>
</ul>

<h3>提交口（GET ?d= 或 POST 都收）：</h3>
<form method="post">
<textarea name="d" rows="4" cols="80" placeholder="base64 弹药贴进来"></textarea><br>
<button type="submit">导入配置</button>
</form>

<?php else:
    // ---------- 引爆区 ----------
    $obj = @unserialize(base64_decode($d));
    if ($obj === false || !is_object($obj)) {
        echo "<pre style='background:#f6f6f6; padding:10px;'>导入失败：串不合法/类不存在（base64 对吗？）</pre>";
    } else {
        echo "<p>配置对象：<code>", h(get_class($obj)), "</code> 已导入。</p>";
        echo "<p style='color:#888'>（没有方法调用？不急——对象在页面结束时自己谢幕，谢幕演出就在本页最底部。）</p>";
    }
endif;
?>
</body>
</html>
