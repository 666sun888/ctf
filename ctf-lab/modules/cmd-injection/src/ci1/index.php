<?php
// ============================================
// 考点：命令注入原理（分号拼接）
// 速查：course/命令注入速查.md 第 0、2 节（做题流程）
// 提示：flag 在 /first.txt
// ============================================
if (isset($_GET['ip'])) {
    $ip = $_GET['ip'];
    // 漏洞点：用户输入直接拼接进系统命令
    system("ping -c 1 " . $ip);
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>ci1 在线Ping工具</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🌐 在线 Ping 工具</h1>
<form method="get">
  输入 IP：<input type="text" name="ip" value="127.0.0.1" size="30">
  <button type="submit">Ping 一下</button>
</form>
<p>提示：flag 在 /first.txt</p>
</body>
</html>
