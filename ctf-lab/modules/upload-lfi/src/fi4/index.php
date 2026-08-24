<?php
// ============================================
// 考点：伪协议 RCE（data:// / php://input）
// 提示：这台服务器的 php.ini 开了 allow_url_include（真实环境默认关闭！）
//      include 不只能包含本地文件——伪协议能把"一段数据"当代码包含
//      flag 在 /flag_fi4.txt
// ============================================
$page = $_GET['page'] ?? '';
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>fi4 站点导航 v4</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>📑 站点导航 v4（fi4 伪协议 RCE）</h1>
<p>
  <a href="/fi4/?page=inc/home.php">首页</a> |
  <a href="/fi4/?page=inc/about.php">关于</a>
</p>
<div style="background:#f6f6f6; padding:12px;">
<?php
if ($page !== '') {
    include($page);
}
?>
</div>
<p style="color:#888">提示：服务器开了 allow_url_include。data:// 伪协议能把一段 base64 直接当 PHP 代码执行。flag 在 /flag_fi4.txt。</p>
</body>
</html>
