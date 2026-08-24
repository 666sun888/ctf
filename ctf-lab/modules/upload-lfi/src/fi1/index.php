<?php
// ============================================
// 考点：本地文件包含 LFI（无过滤）
// 提示：?page= 参数被 include——不止能切页面
//      试试 /etc/passwd（Linux 万能试金石）和 /flag_fi1.txt
// ============================================
$page = $_GET['page'] ?? '';
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>fi1 站点导航</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>📑 站点导航（fi1 页面包含）</h1>
<p>
  <a href="/fi1/?page=inc/home.php">首页</a> |
  <a href="/fi1/?page=inc/about.php">关于</a>
</p>
<div style="background:#f6f6f6; padding:12px;">
<?php
if ($page !== '') {
    include($page);   // 漏洞点：用户完全可控的 include
}
?>
</div>
<p style="color:#888">提示：page 参数会被 include。flag 在 /flag_fi1.txt。</p>
</body>
</html>
