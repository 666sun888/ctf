<?php
// ============================================
// 考点：空格与关键字绕过（黑名单：空白符 / cat / flag）
// 讲义：lesson-02 第 6.1 / 6.2 节
// 提示：flag 在 /flag.txt；空格被拦了，关键字也被拦了
// ============================================
if (isset($_GET['ip'])) {
    $ip = $_GET['ip'];
    if (preg_match('/[\s]|cat|flag/i', $ip)) {
        die("❌ 黑名单：不允许空白符 / cat / flag");
    }
    system("ping -c 1 " . $ip);
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>ci3 在线Ping工具</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🌐 在线 Ping 工具 v3</h1>
<form method="get">
  输入 IP：<input type="text" name="ip" value="127.0.0.1" size="30">
  <button type="submit">Ping 一下</button>
</form>
<p>提示：flag 在 /flag.txt；空格被拦了，cat 和 flag 也被拦了</p>
</body>
</html>
