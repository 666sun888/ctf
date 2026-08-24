<?php
// ============================================
// 考点：XSS 偷 Cookie
// 提示：页面有 XSS，且 Cookie 没有 HttpOnly（F12 Application 看看）
//      让脚本把 Cookie 发给攻击者服务器：/xss/hack.php?c=
// ============================================
setcookie('session', 'flag{xs4_cookie_stolen}', 0, '/');  // 模拟登录态（没设 HttpOnly！）
$q = $_GET['q'] ?? '';
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>xs4 用户中心</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>👤 用户中心（xs4 偷 Cookie）</h1>
<p>你搜索的是：<?= $q ?></p>
<p style="color:#888">提示：你的 Cookie 值就是 flag；它没有 HttpOnly——想办法让浏览器把它发到攻击者服务器：<code>/xss/hack.php?c=</code></p>
</body>
</html>
