<?php
// ============================================
// 考点：Session 包含（你可控的数据被存进服务器上的文件）
// 提示：?u= 的值会存进你的 session，session 文件在 /tmp/sess_<你的PHPSESSID>
//      文件内容 = 序列化后的你——而你可控
//      flag 在 /flag_fi6.txt
// ============================================
session_start();
if (isset($_GET['u'])) {
    $_SESSION['u'] = $_GET['u'];
}
$page = $_GET['page'] ?? '';
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>fi6 导航 v6</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>📑 站点导航 v6（fi6 会话中心）</h1>
<p>
  <a href="/fi5/?page=inc/home.php">首页</a> |
  <a href="/fi5/?page=inc/about.php">关于</a> |
  <a href="/fi6/?u=guest">以 guest 身份进入</a>
</p>
<div style="background:#f6f6f6; padding:12px;">
<?php if ($page !== '') { include($page); } ?>
</div>
<p style="color:#888">提示：本站用 session 记住你（?u= 设置身份，身份存进 /tmp/sess_你的会话ID）。session 也是服务器上的文件——内容跟着你的输入走。带上 Cookie 两次访问：第一次写入，第二次包含。flag 在 /flag_fi6.txt。</p>
</body>
</html>
