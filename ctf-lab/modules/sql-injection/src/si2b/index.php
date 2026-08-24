<?php
$DB = 'ctf2b';
require_once __DIR__ . '/../config.php';
// ============================================
// 考点：UNION 变种——关键字过滤 + order by 探列数
// 讲义：lesson-03 第 5、7 节
// 提示：union/select 被拦了……MySQL 不区分关键字大小写，还记得吗？
// ============================================
$rows = [];
$error = '';
$blocked = '';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    if (preg_match('/union|select/', $id)) { // 注意：这个正则忘了 /i，大小写不敏感地拦——不，它只拦小写！
        $blocked = "❌ 拦截：不允许 union/select";
    } else {
        $sql = "SELECT name, price FROM products WHERE id=" . $id;
        $result = mysqli_query($conn, $sql);
        if (!$result) {
            $error = mysqli_error($conn);
        } else {
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>si2b 商品查询（过滤版）</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🛒 商品查询 v2b（si2b UNION 变种）</h1>
<form method="get">
  商品 ID：<input type="text" name="id" value="1">
  <button type="submit">查询</button>
</form>
<?php if ($blocked): ?><p style="color:#c00"><?= htmlspecialchars($blocked) ?></p><?php endif; ?>
<?php if ($error): ?><p style="color:#c00">❌ 查询出错：<?= htmlspecialchars($error) ?></p><?php endif; ?>
<?php if ($rows): ?>
<table border="1" cellpadding="6">
<tr><th>名称</th><th>价格</th></tr>
<?php foreach ($rows as $row): ?>
<tr><td><?= htmlspecialchars($row['name'] ?? '') ?></td><td><?= htmlspecialchars($row['price'] ?? '') ?></td></tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
<p>提示：union/select 被拦了（仔细看源码里的过滤规则！）……但 MySQL 对关键字大小写不敏感。列数这次没提示——<code>order by</code> 可以帮你数。</p>
</body>
</html>