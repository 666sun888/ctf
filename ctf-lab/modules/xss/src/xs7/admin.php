<?php
// 模拟管理员（xs7）
setcookie('admin_session', 'flag{xs7_final_graduate}', 0, '/');
$msgs = [];
$file = '/var/www/html/xs7/msgs.txt';
if (file_exists($file)) {
    $msgs = array_filter(explode("
", file_get_contents($file)));
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>管理员后台（xs7）</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🔐 管理员后台（xs7）</h1>
<p>欢迎回来，admin！待审核留言：</p>
<?php foreach ($msgs as $m): ?>
  <div style="border-bottom:1px solid #eee; padding:8px;"><?= $m ?></div>
<?php endforeach; ?>
</body>
</html>
