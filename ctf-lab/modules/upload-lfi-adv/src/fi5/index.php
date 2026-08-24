<?php
// ============================================
// 考点：日志包含（Web 日志记录你的 UA → 投毒 → 包含执行）
// 提示：服务器把每个访问记进 /var/log/visit.log（combined 格式，含 User-Agent）
//      data:// 在本模块已关闭——想 RCE 得找"本来就在服务器上、内容可控"的文件
//      flag 在 /flag_fi5.txt
// ============================================
$page = $_GET['page'] ?? '';
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>fi5 导航 v5</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>📑 站点导航 v5（fi5 日志包含）</h1>
<p>
  <a href="/fi5/?page=inc/home.php">首页</a> |
  <a href="/fi5/?page=inc/about.php">关于</a>
</p>
<div style="background:#f6f6f6; padding:12px;">
<?php
if ($page !== '') {
    include($page);
}
?>
</div>
<p style="color:#888">提示：include 还在，但伪协议全关了。服务器访问日志在 <b>/var/log/visit.log</b>——日志里记着每位访客的 User-Agent，而 User-Agent 是谁发过去的？flag 在 /flag_fi5.txt。</p>
</body>
</html>
