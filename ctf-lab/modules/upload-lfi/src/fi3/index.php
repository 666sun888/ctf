<?php
// ============================================
// 考点：php://filter 伪协议（读源码而不是执行）
// 提示：flag 藏在本页源码的注释里——include 自己看不到（注释会被解析掉）
//      有一种伪协议能让 include "读"文件而不是"执行"文件
// ============================================
// ⭐ flag{fi3_filter_read_source}
$page = $_GET['page'] ?? '';
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>fi3 站点导航 v3</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>📑 站点导航 v3（fi3 filter 读源码）</h1>
<p>
  <a href="/fi3/?page=inc/home.php">首页</a> |
  <a href="/fi3/?page=inc/about.php">关于</a>
</p>
<div style="background:#f6f6f6; padding:12px;">
<?php
if ($page !== '') {
    include($page);
}
?>
</div>
<p style="color:#888">提示：flag 在本页（fi3/index.php）源码的注释里。直接 include 它没用——PHP 注释被解析掉了。怎么把源码"原样读出来"？</p>
</body>
</html>
