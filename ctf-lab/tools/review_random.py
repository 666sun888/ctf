# 复习抽题器：随机抽题闭卷复测（间隔重复，防遗忘）
# 用法：python review_random.py
#
# 题池维护规则（2026-08-24 重整）：
#   - 只收录【确认已完成】的题（以 modules/course/progress.md 打勾为准）
#   - 做完新题后手动把它从下方 pending 移进 pool
import random

# ---------- 题库：(题目, URL, 考点)，全部已确认完成 ----------
pool = [
  ("ch2 改头换面", "http://localhost:8080/ch2/", "请求头伪造"),
  ("ci3 拦空格关键字", "http://localhost:8082/ci3/", "命令注入 ${IFS}/通配符"),
  ("ci4 拦分隔符", "http://localhost:8082/ci4/", "反引号命令替换"),
  ("si1 万能密码", "http://localhost:8083/si1/", "字符串注入+注释符"),
  ("si2 UNION", "http://localhost:8083/si2/", "UNION+信息枚举"),
  ("si3 报错注入", "http://localhost:8083/si3/", "updatexml 报错信道"),
  ("si4 注释绕过", "http://localhost:8083/si4/", "拦 union select 的绕过"),
  ("si2b UNION 变种", "http://localhost:8083/si2b/", "大小写绕过"),
  ("si3b 报错变种", "http://localhost:8083/si3b/", "extractvalue+分段"),
  ("si5b 盲注变种", "http://localhost:8083/si5b/", "盲注+/**/"),
  ("si7b 堆叠变种", "http://localhost:8083/si7b/", "UPDATE 业务联动"),
  ("sifinal 毕业考", "http://localhost:8083/sifinal/?ticket=", "盲注全链(表名/列名/数据)"),
  ("xs1 反射型", "http://localhost:8084/xs1/", "反射 XSS+URL 参数"),
  ("xs3 DOM 型", "http://localhost:8084/xs3/", "location.hash+innerHTML"),
  ("xs5 过滤绕过", "http://localhost:8084/xs5/", "换标签/换函数/双写"),
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
]

# ---------- 待定区（没做完/状态不明，禁止抽！做完再移入上方 pool） ----------
# si5~si10          http://localhost:8083/si5~10/ ← 进度表未打勾,先确认是否做过
# fi8 三合一        http://localhost:8086/fi8/    ← 未做!且完整解法已在 writeups 里,抽到=看答案做题

picks = random.sample(pool, k=min(2, len(pool)))
print("今天闭卷复测这 2 题（15 分钟内完成）：")
for name, url, tag in picks:
    print(f"  - {name}")
    print(f"    {url}")
print()
print("—— 做完再看下面 ——")
for name, url, tag in picks:
    print(f"  {name}: {tag}")
print()
print("规则：先闭卷——不看任何资料，写出三行解法（漏洞点 / payload / 绕过思路）；")
print("      写不出来才准翻 course/ 速查表；writeups/ 最后对答案用；结果记进 progress.md。")
