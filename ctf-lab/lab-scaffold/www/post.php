<?php
require 'common.php';
$u = $_POST['username'] ?? ''; $p = $_POST['password'] ?? '';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "SELECT * FROM lab_users WHERE username='$u' AND password='$p'";
    $res = mysqli_query($conn, $sql);
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        if ($row['username'] === 'admin') {
            $msg = "🎉 管理员登录成功！flag: flag{lab_admin_pwned}";
        } else {
            $msg = "登录成功：" . $row['username'];
        }
    } else {
        $msg = "用户名或密码错误";
    }
}
?>
<!DOCTYPE html><html lang="zh"><head><meta charset="utf-8"><title>登录</title></head>
<body style="font-family:sans-serif;max-width:640px;margin:40px auto;">
<h1>🔐 登录（POST 字符串场景）</h1>
<form method="post">用户名：<input name="username"><br><br>密　码：<input name="password"><br><br><button>登录</button></form>
<?php if ($msg): ?><p><b><?= h($msg) ?></b></p><?php endif; ?>
<?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): footer($sql); endif; ?>
<p><a href="index.php">返回菜单</a></p>
</body></html>
