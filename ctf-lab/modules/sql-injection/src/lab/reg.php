<?php
require 'common.php';
$raw_u = $_POST['username'] ?? ''; $raw_p = $_POST['password'] ?? '';
$uid = 0; $stored = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $raw_u !== '' && $raw_p !== '') {
    $u = addslashes($raw_u); $p = addslashes($raw_p);   // 本站注册口做了转义
    $sql = "INSERT INTO lab_users(username,password,role) VALUES('$u','$p','guest')";
    if (mysqli_query($conn, $sql)) {
        $uid = mysqli_insert_id($conn);
        $r = mysqli_query($conn, "SELECT username FROM lab_users WHERE id=$uid");
        $stored = mysqli_fetch_assoc($r)['username'];
    } else { $err = mysqli_error($conn); }
}
?>
<!DOCTYPE html><html lang="zh"><head><meta charset="utf-8"><title>注册</title></head>
<body style="font-family:sans-serif;max-width:640px;margin:40px auto;">
<h1>📌 注册（本入口做了转义）</h1>
<form method="post">用户名：<input name="username"><br><br>密　码：<input name="password"><br><br><button>注册</button></form>
<?php if ($uid): ?>
<p>✅ 注册成功，你的用户编号：<b><?= $uid ?></b>（改密页要用）</p>
<p>库内实际存储的用户名：<code><?= h($stored) ?></code></p>
<p><a href="pass.php?id=<?= $uid ?>">去改密页（pass.php?id=<?= $uid ?>）</a></p>
<?php elseif (!empty($err)): ?><p style="color:#c00">注册失败：<code><?= h($err) ?></code></p><?php endif; ?>
<p><a href="index.php">返回菜单</a></p>
</body></html>
