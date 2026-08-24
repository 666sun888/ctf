<?php
// ============================================
// 考点：前后缀拼接的文件包含（突围）
// 提示：include('pages/' . $page . '.php')
//      前面的 pages/ 用 ../ 消掉；后面的 .php 甩不掉——怎么办？
//      flag 文件叫 /flag_fi2.php（想想为什么偏偏给你 .php 结尾）
// ============================================
$page = $_GET['page'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>fi2 站点导航 v2</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>📑 站点导航 v2（fi2 前后缀拼接）</h1>
<p>
  <a href="/fi2/?page=home">首页</a> |
  <a href="/fi2/?page=about">关于</a>
</p>
<div style="background:#f6f6f6; padding:12px;">
<?php include('pages/' . $page . '.php'); ?>
</div>
<p style="color:#888">提示：这次前后都拼了东西。flag 文件在根目录，叫 /flag_fi2.php。</p>
</body>
</html>
