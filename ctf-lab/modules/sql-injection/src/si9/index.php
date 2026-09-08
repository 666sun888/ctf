<?php
require_once __DIR__ . '/../config.php';
// ============================================
// 考点：宽字节注入（GBK 吃掉转义反斜杠）
// 速查：course/SQL注入速查.md 第 7 节（宽字节）
// 提示：%bf%27%20or%201=1%23
// ============================================
$flag = "flag{si9_widebyte}";
$msg = '';
$sql = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // "防御"：addslashes 转义引号
    $user = addslashes($_POST['username'] ?? '');
    $pass = addslashes($_POST['password'] ?? '');
    // 元凶：数据库连接用 GBK 字符集
    mysqli_set_charset($conn, 'gbk');
    $sql = "SELECT * FROM users WHERE username='$user' AND password='$pass'";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if ($row['username'] === 'admin') {
            $msg = "🎉 登录成功！flag: <code>" . $flag . "</code>";
        } else {
            $msg = "登录成功，但你不是 admin。";
        }
    } else {
        $msg = "❌ 登录失败";
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>si9 登录 v2</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🔐 登录 v2（si9 宽字节注入）</h1>
<p style="color:#888">这次有 addslashes 防御了，密码也检查了……真的防住了吗？</p>
<form method="post">
  用户名：<input type="text" name="username"><br><br>
  密　码：<input type="password" name="password"><br><br>
  <button type="submit">登录</button>
</form>
<?php if ($msg): ?><p><?= $msg ?></p><?php endif; ?>
<?php if ($sql): ?><p style="color:#888">执行的 SQL：<code><?= htmlspecialchars($sql) ?></code></p><?php endif; ?>
<p>提示：%bf%27%20or%201=1%23（建议用 curl 提交，避免浏览器编码干扰）</p>
</body>
</html>
