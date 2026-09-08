# 复习抽题器 v2：洗牌袋模式（2026-09-08 学员提案采纳）
# v1 纯随机的缺陷：随机抽有"饥饿问题"——题池 50 题、每天抽 2，个别题可能长期抽不到
# v2 洗牌袋：整池洗牌排成队列，每次从队头取；不够取就补洗剩余题——
#    数学保证：每一轮（25 天）里，每道题恰好被抽到一次，顺序仍然随机
# 用法：python review_random.py [数量]
import json
import os
import random
import sys

# Windows 控制台默认 GBK 编码,中文输出乱码 -> 强制 UTF-8
if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8")

# ---------- 题库：(题目, URL, 考点)，全部已确认完成 ----------
pool = [
  ("ch1 只许 POST", "http://localhost:8001/ch1/", "-X 指定请求方法"),
  ("ch2 改头换面", "http://localhost:8001/ch2/", "请求头伪造"),
  ("ch3 Cookie 的秘密", "http://localhost:8001/ch3/", "Cookie 伪造：-b 改身份"),
  ("ch4 神秘编码", "http://localhost:8001/ch4/", "URL 编码"),
  ("ci1 直接注入", "http://localhost:8082/ci1/", "分号拼接新命令"),
  ("ci2 拦分号", "http://localhost:8082/ci2/", "管道/换行替代分号"),
  ("ci3 拦空格关键字", "http://localhost:8082/ci3/", "命令注入 ${IFS}/通配符"),
  ("ci4 拦分隔符", "http://localhost:8082/ci4/", "反引号命令替换"),
  ("si1 万能密码", "http://localhost:8083/si1/", "字符串注入+注释符"),
  ("si2 UNION", "http://localhost:8083/si2/", "UNION+信息枚举"),
  ("si3 报错注入", "http://localhost:8083/si3/", "updatexml 报错信道"),
  ("si4 注释绕过", "http://localhost:8083/si4/", "拦 union select 的绕过"),
  ("si5 布尔盲注", "http://localhost:8083/si5/", "页面两种状态当信道"),
  ("si6 时间盲注", "http://localhost:8083/si6/", "响应时间当信道"),
  ("si7 堆叠", "http://localhost:8083/si7/", "分号+multi_query 执行第二条"),
  ("si8 读文件", "http://localhost:8083/si8/", "load_file+secure_file_priv"),
  ("si9 宽字节", "http://localhost:8083/si9/", "GBK 吃反斜杠"),
  ("si10 超长 flag", "http://localhost:8083/si10/", "报错注入+substr 分段"),
  ("si2b UNION 变种", "http://localhost:8083/si2b/", "大小写绕过"),
  ("si3b 报错变种", "http://localhost:8083/si3b/", "extractvalue+分段"),
  ("si5b 盲注变种", "http://localhost:8083/si5b/", "盲注+/**/"),
  ("si7b 堆叠变种", "http://localhost:8083/si7b/", "UPDATE 业务联动"),
  ("sifinal 毕业考", "http://localhost:8083/sifinal/?ticket=", "盲注全链(表名/列名/数据)"),
  ("xs1 反射型", "http://localhost:8084/xs1/", "反射 XSS+URL 参数"),
  ("xs2 存储型", "http://localhost:8084/xs2/", "留言持久化，所有人可中"),
  ("xs3 DOM 型", "http://localhost:8084/xs3/", "location.hash+innerHTML"),
  ("xs4 偷 Cookie", "http://localhost:8084/xs4/", "document.cookie+new Image 外带"),
  ("xs5 过滤绕过", "http://localhost:8084/xs5/", "换标签/换函数/双写"),
  ("xs6 打管理员", "http://localhost:8084/xs6/", "存储型偷管理员 Cookie 全链"),
  ("xs7 毕业考", "http://localhost:8084/xs7/", "重叠双写+外带"),
  ("up1 无过滤上传", "http://localhost:8085/up1/", "webshell 直传+触发"),
  ("up2 黑名单", "http://localhost:8085/up2/", "大小写/.pht 漏网"),
  ("up3 MIME", "http://localhost:8085/up3/", "伪造 Content-Type"),
  ("up4 毕业考", "http://localhost:8085/up4/", "图片马+include 联动"),
  ("fi1 任意包含", "http://localhost:8085/fi1/", "LFI 路径穿越"),
  ("fi2 拼接突围", "http://localhost:8085/fi2/", "../消前缀+后缀吸收"),
  ("fi3 filter", "http://localhost:8085/fi3/", "php://filter 读源码"),
  ("fi4 伪协议", "http://localhost:8085/fi4/", "data:// base64 RCE"),
  ("up6 双写", "http://localhost:8086/up6/", "str_replace 删一遍,嵌两个"),
  ("fi5 日志包含", "http://localhost:8086/fi5/", "UA 投毒+包含日志"),
  ("up5 .htaccess", "http://localhost:8086/up5/", "配置文件接管目录解析"),
  ("up7 竞争", "http://localhost:8086/up7/", "先保存后检查的时间窗"),
  ("fi6 session", "http://localhost:8086/fi6/", "/tmp/sess_ID 可控内容"),
  ("fi7 pearcmd", "http://localhost:8086/fi7/", "config-create 嵌代码写文件"),
  ("up8 解析漏洞", "http://localhost:8086/up8/", "AddHandler 从右往左"),
  ("L6 wakeup 绕过", "http://127.0.0.1:8090/vault.php", "属性数虚报+wakeup 跳过（php709 靶）"),
  ("L9 原生类 SSRF", "http://127.0.0.1:8093/", "SoapClient __call 借手打内网 8094"),
  ("L10 PHPGGC 量产", "http://127.0.0.1:8095/", "认库(composer.lock)选链生成+谢幕引爆"),
  ("L12 Java 借手", "http://127.0.0.1:8097/", "ysoserial CC6+两段式落盘取回"),
  ("L14 毕业考复测", "http://127.0.0.1:8099/", "手搓 POP+wakeup 绕过+base64"),
]

# ---------- 洗牌袋状态：队列存题名，持久化在同目录 json ----------
QUEUE_FILE = os.path.join(os.path.dirname(os.path.abspath(__file__)), "review_queue.json")

def load_queue():
    """读队列；池变动后自动剔除已不存在的题名，坏文件当作空袋重来"""
    try:
        with open(QUEUE_FILE, encoding="utf-8") as f:
            names = json.load(f)
    except Exception:
        names = []
    pool_names = {p[0] for p in pool}
    return [n for n in names if n in pool_names]

def save_queue(names):
    with open(QUEUE_FILE, "w", encoding="utf-8") as f:
        json.dump(names, f, ensure_ascii=False, indent=1)

count = int(sys.argv[1]) if len(sys.argv) > 1 else 2
queue = load_queue()

# 队头不够抽 -> 把"不在队里的剩余题"洗乱接到队尾（保证：一轮之内每题恰好一次，无重复）
if len(queue) < count:
    in_queue = set(queue)
    rest = [p[0] for p in pool if p[0] not in in_queue]
    random.shuffle(rest)
    queue.extend(rest)

by_name = {p[0]: p for p in pool}
picks = [by_name[queue.pop(0)] for _ in range(min(count, len(pool)))]
save_queue(queue)

print(f"今天闭卷复测这 {len(picks)} 题（15 分钟内完成）：")
for name, url, tag in picks:
    print(f"  - {name}")
    print(f"    {url}")
print()
print(f"（洗牌袋还剩 {len(queue)} 题，取空自动重洗整池——每题每轮必被抽到，v1 的饥饿问题已修）")
print()
print("—— 做完再看下面 ——")
for name, url, tag in picks:
    print(f"  {name}: {tag}")
print()
print("复测前先重置靶场状态（清掉上次的马/留言/权限改动，否则 si7b/xs/up 系列是已解状态）：")
print("      cd ctf-lab && docker compose up -d --force-recreate")
print()
print("规则：先闭卷——不看任何资料，写出三行解法（漏洞点 / payload / 绕过思路）；")
print("      写不出来才准翻 course/ 速查表；writeups/ 最后对答案用；结果记进 progress.md。")
