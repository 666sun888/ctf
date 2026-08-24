<?php
// ============================================
// 考点：L3 毕业考——拼接包含 + 日志投毒 + 后缀吸收 三合一
// 提示：include('pages/' . $page . '.php') 前后焊死、伪协议全关、路径穿越被 basename 锁死
//      但运维为了"日志浏览插件"，把站点日志命名成了 /var/log/fi8log.php（.php 结尾！）
//      日志里记着所有人的 User-Agent……
//      flag 在 /flag_fi8.txt
// ============================================
$page = $_GET['page'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>fi8 导航 v8</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>📑 站点导航 v8（fi8 毕业考）</h1>
<p>
  <a href="/fi8/?page=home">首页</a> |
  <a href="/fi8/?page=about">关于</a>
</p>
<div style="background:#f6f6f6; padding:12px;">
<?php include('pages/' . $page . '.php'); ?>
</div>
<p style="color:#888">提示：前后缀焊死（fi2 同款）、伪协议全关（fi4 反制）、这题也没有上传点。你手里只剩 fi5/fi6 学的两样东西 + fi2 的一招——把它们串起来。运维说："日志文件叫 <b>/var/log/fi8log.php</b>，日志插件要求 .php 结尾才能渲染"。flag 在 /flag_fi8.txt。</p>
</body>
</html>
