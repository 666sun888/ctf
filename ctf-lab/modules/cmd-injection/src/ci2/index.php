<?php
// ============================================
// 考点：分隔符绕过（黑名单只拦了分号）
// 讲义：lesson-02 第 3 节
// 提示：flag 在 /second.txt；分号被拦了，但还有很多分隔符
// ============================================
if (isset($_GET['ip'])) {
    $ip = $_GET['ip'];
    if (preg_match('/;/', $ip)) {
        die("❌ 黑名单：不允许分号 ;");
    }
    system("ping -c 1 " . $ip);
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>ci2 在线Ping工具</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🌐 在线 Ping 工具 v2</h1>
<form method="get">
  输入 IP：<input type="text" name="ip" value="127.0.0.1" size="30">
  <button type="submit">Ping 一下</button>
</form>
<p>提示：flag 在 /second.txt；分号被拦了，但还有很多分隔符</p>
</body>
</html>
