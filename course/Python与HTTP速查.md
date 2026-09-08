# Python + HTTP 速查 v2

> 用法：写脚本/看请求不记得了 → 翻对应部分
> 命令环境：**CMD**（curl.exe；PowerShell 场景见第 9 节尾注）
> 验证标注：✓(YYYY-MM-DD)=当日实弹；未标注 = 教材知识
> 关联：盲注脚本唯一权威版本 = **SQL 注入速查 第八章**（本表只留指针，不维护副本）；CMD 的 % 与 findstr 坑 = **黑盒开局速查 第 3 节**

========================================================
# Python 速查
========================================================

## 1. 发请求（requests）✓(2026-08-30 实弹)

```python
import requests

# GET
resp = requests.get('http://target/?id=1').text

# GET 带参数（自动编码,不用自己拼 URL）
resp = requests.get('http://target/', params={'id': '1 and 1=1'}).text

# POST 表单
resp = requests.post('http://target/login', data={'username': 'admin', 'password': 'x'}).text

# 带 Cookie / 请求头
resp = requests.get(url, cookies={'role': 'admin'}, headers={'User-Agent': 'x'}).text
```

## 2. 常用操作

```python
'关键字' in resp          # 判断页面里有没有某段文字（盲注信号）
len(resp)                 # 页面长度（可用于判断页面是否变化）
time.time()               # 当前时间戳（时间盲注计时用）
chr(102)                  # 数字→字符：'f'
ord('f')                  # 字符→数字：102
str(5)                    # 数字→字符串
'%d' % 5                  # 占位符填值：'5'
'%s和%s' % (a, b)         # 多个占位符
f'结果:{1+1}'             # f-string 插值（Python3.6+）
```

## 3. 盲注脚本骨架

**唯一权威版本在 SQL 注入速查 第八章**（含：重试 3 次硬退、超时不判真、length 参数、ASCII 界注、POST/Cookie/请求头注入点的只换一行的差异块）。本表不维护副本——改那一份，两边才不会越改越岔。

## 4. 报错排查（Python）

| 报错 | 意思 | 怎么办 |
|------|------|--------|
| NameError: xx is not defined | 变量/函数名拼错 | 检查拼写 |
| SyntaxError | 语法错误 | 看报错指出的行 |
| TypeError: can only concatenate str | 字符串拼了非字符串 | 用 str() 转换 |
| ModuleNotFoundError | 库没装 | pip install requests |
| IndexError | 索引越界 | 检查列表长度 |
| requests 一直报错/超时 | 网络/URL 问题 | 先 curl 试试 URL 对不对 |

---

========================================================
# HTTP 速查
========================================================

## 5. 请求结构（读源码/看抓包用）

```
请求行：GET /path HTTP/1.1        （方法 + 路径 + 协议版本）
请求头：Host / User-Agent / Referer / Cookie / Content-Type ...
空行
请求体（POST 才有）
```

## 6. 方法速查

| 方法 | 用途 | 注入场景 |
|------|------|----------|
| GET | 取数据 | 参数在 URL 的 ? 后面 |
| POST | 提交 | 参数在请求体；登录/留言 |
| HEAD | 只要响应头 | 有时绕过只拦 GET/POST 的检查 |
| OPTIONS | 问支持哪些方法 | 探测 |
| PUT | 上传 | 老系统未授权 PUT 漏洞 |
| DELETE | 删除 | - |

## 7. 状态码速查

| 码 | 意思 | 做题含义 |
|----|------|----------|
| 200 | 成功 | 正常 |
| 301/302 | 重定向 | 看 Location 头（curl -L 跟随） |
| 403 | 禁止 | 换方法/换头/换路径试试 |
| 404 | 不存在 | 路径错了 |
| 405 | 方法不允许 | 提示换方法（ch1 考点） |
| 500 | 服务器错 | 可能触发了漏洞 |

## 8. 常用请求头（全部可伪造）

| 头 | 含义 | 伪造场景 |
|----|------|----------|
| User-Agent | 客户端标识 | 伪造特定 UA 过检查 |
| Referer | 来源页面 | 伪造来源过检查 |
| Cookie | 键值对 | 伪造身份（ch3） |
| X-Forwarded-For | 客户端IP（代理加） | 伪造 IP 过'只允许内网' |
| Content-Type | 请求体格式 | 改类型绕过解析 |
| Host | 目标主机 | Host 头注入 |

## 9. curl 速查 ✓(2026-08-30 CMD 全形态实弹)

```
curl.exe -X POST http://x/            指定方法
curl.exe -d "a=1&b=2" http://x/       POST 表单数据(CMD 双引号保护 &,别用单引号!)
curl.exe -H "User-Agent: x" http://x/ 自定义头
curl.exe -b "role=admin" http://x/    带 Cookie
curl.exe -c cookies.txt http://x/     存 Cookie 到文件
curl.exe -e "http://x/" http://x/     伪造 Referer
curl.exe -v http://x/                 显示完整请求响应
curl.exe -L http://x/                 跟随重定向(✓ 实测跟 302)
curl.exe -o 文件 http://x/            保存响应到文件(CMD 黑洞文件是 NUL)
```

**坑**：
- **CMD 只有双引号有保护力**，单引号是普通字符——老版这条表写"参数用单引号包"是 PowerShell 的习惯，**CMD 里照做会把引号原样发出去**（2026-08-30 更正）
- CMD 的 `%` 与 findstr 中文坑 → 黑盒开局速查 第 3 节（批处理文件里 % 要双写 %%）
- URL 里的特殊字符要编码：空格 %20、# %23、单引号 %27、; %3B、| %7C、& %26
- 若临时在 PowerShell：curl 是 Invoke-WebRequest 别名，认准 curl.exe（.exe 后缀两个 shell 都稳）

## 10. Cookie 与 Session

| | 存哪 | 浏览器里有什么 | 能伪造吗 |
|--|------|--------------|----------|
| Cookie | 浏览器 | 明文键值对 | 能，直接改 |
| Session | 服务器 | 一串随机 id | 不能改内容，可偷/爆破 |

**HttpOnly**：JS 读不到（防 XSS 偷 Cookie）——偷不到就换思路：让受害者浏览器自己发同源请求（NSSCTF paper 题 2026-08-30 实战）

## 11. URL 编码

```
%XX 十六进制：空格=%20，a=%61，?=%3F，#=%23，&=%26
服务器收到 URL 先解码再处理（$_GET 里已是解码后值）
```

## 12. PHP 读请求变量（读源码用）

```php
$_GET['x']            // URL 参数 ?x=...
$_POST['x']           // POST 表单参数
$_COOKIE['x']         // Cookie
$_SERVER['HTTP_USER_AGENT']   // 请求头 = HTTP_ + 大写 + 横线转下划线(User-Agent→HTTP_USER_AGENT)
$_SERVER['REQUEST_METHOD']  // 方法
$_FILES['f']          // 上传的文件
```

---

## 心法

> 请求头都可伪造 ｜ 先 F12 看请求再动手 ｜ CMD 只有双引号 ｜ 编码符号别忘 % ｜ 盲注脚本只认 SQL 表第八章一个版本
