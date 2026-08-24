<?php
// ============================================
// 模拟管理员审核页面
// 管理员访问留言板 → 执行留言里的 payload → Cookie 被外带
// ============================================
// 管理员的会话 Cookie（flag 就在这里！）
setcookie('admin_session', 'flag{xs6_admin_cookie}', 0, '/');
$msgs = [];
$file = '/var/www/html/xs6/msgs.txt';
if (file_exists($file)) {
    $msgs = array_filter(explode("
", file_get_contents($file)));
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>管理员后台</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🔐 管理员后台（xs6）</h1>
<p>欢迎回来，admin！以下是待审核的留言：</p>
<?php foreach ($msgs as $m): ?>
  <div style="border-bottom:1px solid #eee; padding:8px;"><?= $m ?></div>
<?php endforeach; ?>
</body>
</html>
