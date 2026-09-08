<?php
require_once __DIR__ . '/../config.php';
// ============================================
// 考点：字符串注入（登录绕过）
// 速查：course/SQL注入速查.md 第 6 节（字符串注入）
// 提示：admin'# 试试（# 是 MySQL 注释符）
// ============================================
$flag = "flag{si1_login_bypass}";
$msg = '';
$sql = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    // 漏洞点：用户输入直接拼接进 SQL
    $sql = "SELECT * FROM users WHERE username='$user' AND password='$pass'";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if ($row['username'] === 'admin') {
            $msg = "🎉 登录成功！flag: <code>" . $flag . "</code>";
        } else {
            $msg = "登录成功，但你不是 admin，拿不到 flag。";
        }
    } else {
        $msg = "❌ 用户名或密码错误";
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>si1 登录</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🔐 登录（si1 万能密码）</h1>
<form method="post">
  用户名：<input type="text" name="username"><br><br>
  密　码：<input type="password" name="password"><br><br>
  <button type="submit">登录</button>
</form>
<?php if ($msg): ?><p><?= $msg ?></p><?php endif; ?>
<?php if ($sql): ?><p style="color:#888">执行的 SQL：<code><?= htmlspecialchars($sql) ?></code></p><?php endif; ?>
</body>
</html>
