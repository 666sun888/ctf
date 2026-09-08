<?php
require 'common.php';
$id = $_GET['id'] ?? ''; $new = $_POST['newpass'] ?? '';
$stored = ''; $q1 = ''; $q2 = ''; $ok = false;
if ($id !== '') {
    $q1 = "SELECT username FROM lab_users WHERE id=$id";
    $r1 = mysqli_query($conn, $q1);
    $stored = $r1 ? (mysqli_fetch_assoc($r1)['username'] ?? '') : '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $new !== '') {
        $q2 = "UPDATE lab_users SET password='$new' WHERE username='$stored'";
        $ok = mysqli_query($conn, $q2);
    }
}
?>
<!DOCTYPE html><html lang="zh"><head><meta charset="utf-8"><title>修改密码</title></head>
<body style="font-family:sans-serif;max-width:640px;margin:40px auto;">
<h1>🔑 修改密码（按用户编号）</h1>
<form method="post">新密码：<input name="newpass"><button>改密</button></form>
<?php if ($id !== ''): ?>
<p>该编号的用户名：<code><?= h($stored) ?></code></p>
<?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
<p><?= $ok ? '✅ 密码已更新' : '❌ 更新失败' ?></p>
<?php footer($q2); endif; endif; ?>
<p><a href="index.php">返回菜单</a></p>
</body></html>
