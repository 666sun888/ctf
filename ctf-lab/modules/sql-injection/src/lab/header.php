<?php
require 'common.php';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '-';
$sql = "INSERT INTO lab_visit(ua) VALUES('$ua')";
$ok = mysqli_query($conn, $sql);
$ins_err = $ok ? '' : mysqli_error($conn);   // 当场抓错,下面 $recent 的 SELECT 会清掉错误状态
$recent = mysqli_query($conn, "SELECT ua FROM lab_visit ORDER BY id DESC LIMIT 8");
?>
<!DOCTYPE html><html lang="zh"><head><meta charset="utf-8"><title>访问记录</title></head>
<body style="font-family:sans-serif;max-width:640px;margin:40px auto;">
<h1>📍 访问记录（UA 入库）</h1>
<p>你的 User-Agent 已记录。</p>
<h2>最近 8 条</h2>
<?php while ($row = mysqli_fetch_assoc($recent)): ?>
<p><code><?= h($row['ua']) ?></code></p>
<?php endwhile; ?>
<?php footer($sql, $ins_err); ?>
<p><a href="index.php">返回菜单</a></p>
</body></html>
