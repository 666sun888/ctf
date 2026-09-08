<?php
// ============================================
// 考点：请求头（User-Agent / Referer / X-Forwarded-For）
// 速查：course/Python与HTTP速查.md 第 8 节（常用请求头）
// 提示：三个头都要伪造
// ============================================
$flag = "flag{ch2_headers}";

$ua  = $_SERVER['HTTP_USER_AGENT'] ?? '';
$ref = $_SERVER['HTTP_REFERER'] ?? '';
$xff = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';

if (strpos($ua, 'CTF-Browser') === false) {
    die("<p>❌ 只有用 CTF-Browser 访问的人才能看到 flag</p>");
}
if (strpos($ref, 'admin.ctf.local') === false) {
    die("<p>❌ 必须从 admin.ctf.local 跳转过来（Referer）</p>");
}
if ($xff !== '127.0.0.1') {
    die("<p>❌ 只允许本机(127.0.0.1)访问（X-Forwarded-For）</p>");
}

echo "<h1>🎉 恭喜！flag: <code>$flag</code></h1>";
