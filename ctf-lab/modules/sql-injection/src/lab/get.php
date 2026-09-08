<?php
require 'common.php';
$id = $_GET['id'] ?? '1';
$sql = "SELECT id,name,price FROM lab_goods WHERE id=$id";
$res = mysqli_query($conn, $sql);
?>
<!DOCTYPE html><html lang="zh"><head><meta charset="utf-8"><title>商品查询</title></head>
<body style="font-family:sans-serif;max-width:640px;margin:40px auto;">
<h1>🛒 商品查询（GET 数字型）</h1>
<form method="get">商品 ID：<input name="id" value="<?= h($id) ?>"><button>查询</button></form>
<?php if ($res && mysqli_num_rows($res) > 0): while ($row = mysqli_fetch_assoc($res)): ?>
<p>编号 <?= h($row['id']) ?>：<?= h($row['name']) ?>，价格 <?= h($row['price']) ?> 元</p>
<?php endwhile; else: ?><p>无结果</p><?php endif; ?>
<?php footer($sql); ?>
<p><a href="index.php">返回菜单</a></p>
</body></html>
