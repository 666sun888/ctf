<?php
// ============================================
// 考点：Cookie
// 讲义：lesson-01 第 5 节
// 提示：身份就在 Cookie 里，试着改改
// ============================================
$flag = "flag{ch3_cookie}";

if (!isset($_COOKIE['role'])) {
    setcookie('role', 'guest', 0, '/');
    die("<p>你还没有身份。按 F12 → Application → Cookies 看看自己的 Cookie（或用 curl 观察 Set-Cookie 响应头）。</p>");
}

if ($_COOKIE['role'] === 'admin') {
    echo "<h1>🎉 恭喜！flag: <code>$flag</code></h1>";
} else {
    echo "<h1>你是 guest。管理员(role=admin)才能看到 flag。</h1>";
}
