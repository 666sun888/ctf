<?php
require 'common.php';
$kw = $_GET['kw'] ?? '';
$sql = "SELECT id,name,price FROM lab_goods WHERE name LIKE '%$kw%'";
$res = mysqli_query($conn, $sql);
?>
<!DOCTYPE html><html lang="zh"><head><meta charset="utf-8"><title>商品搜索</title></head>
<body style="font-family:sans-serif;max-width:640px;margin:40px auto;">
<h1>🔍 商品搜索（LIKE 场景）</h1>
<form method="get">关键词：<input name="kw" value="<?= h($kw) ?>"><button>搜索</button></form>
<?php if ($res && mysqli_num_rows($res) > 0): while ($row = mysqli_fetch_assoc($res)): ?>
<p><?= h($row['name']) ?>，价格 <?= h($row['price']) ?> 元</p>
<?php endwhile; else: ?><p>无结果</p><?php endif; ?>
<?php footer($sql); ?>
<p><a href="index.php">返回菜单</a></p>
</body></html>
