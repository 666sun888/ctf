# 题解：http-basics 模块

> ⚠️ 请先把 4 道题全部做出来，再来看这里！

## ch1 只许 POST（请求方法）

```powershell
curl.exe -X POST http://localhost:8080/ch1/
```

**讲解**：源码里 `$_SERVER['REQUEST_METHOD']` 读的是请求行里的方法。GET 访问时方法不是 POST，直接 405 拒绝。换成 POST 就拿到 flag。
**考点本质**：服务端对"客户端输入"（这里指方法）做了检查，但检查不完整——没有限制就只用 GET 访问。以后你会见到更多"检查了什么就绕过什么"的题。

## ch2 改头换面（请求头伪造）

```powershell
curl.exe http://localhost:8080/ch2/ -H "User-Agent: CTF-Browser" -H "Referer: http://admin.ctf.local/panel" -H "X-Forwarded-For: 127.0.0.1"
```

**讲解**：三个 `$_SERVER['HTTP_*']` 分别读三个请求头。请求头是客户端发的纯文本，服务器没有能力验证真伪，所以全部伪造即可。
**考点本质**：**一切请求头都不可信**。这也是为什么后来会有"只信任代理添加的 XFF"、"校验 Referer 防 CSRF"等话题——它们都是在和不靠谱的输入作斗争。

## ch3 Cookie 的秘密（Cookie 伪造）

```powershell
curl.exe -b "role=admin" http://localhost:8080/ch3/
```

**讲解**：第一次访问时服务器 `setcookie('role','guest')` 下发身份，之后读 `$_COOKIE['role']` 判断。浏览器只是忠实地把 Cookie 带回去，而 curl 的 `-b` 可以随意指定——改成 admin 即可。
**考点本质**：把"身份"这种敏感信息放在客户端可伪造的位置，是经典设计缺陷。正确的做法是存 Session（服务器端），或者对 Cookie 做签名/HMAC（后面 JWT 课会讲）。

## ch4 神秘编码（URL 编码）

```powershell
curl.exe "http://localhost:8080/ch4/?name=%61dmin"
```

**讲解**：`%61` 是字母 a 的 URL 编码。服务器解析 URL 时先解码，`$_GET['name']` 拿到的就是 `admin`。直接传 `name=admin` 也能过，但 `%61dmin` 让你亲眼看到"解码发生在代码执行之前"。
**考点本质**：编码与解码的时机差异是无数漏洞（二次编码绕过、双重 URL 编码、宽字节注入……）的根源。先记住"服务器先解码再处理"这一条。
