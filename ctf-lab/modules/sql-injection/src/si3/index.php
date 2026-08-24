<?php
$DB = 'ctf3';
require_once __DIR__ . '/../config.php';
// ============================================
// 考点：报错注入（updatexml）
// 讲义：lesson-03 第 6.1 节
// 提示：flag 藏在某张表里；页面会把报错显示出来——先报表名，再报列名，最后报数据
// ============================================
$rows = [];
$error = '';
$sql = '';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT name, price FROM products WHERE id=" . $id;
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        $error = mysqli_error($conn); // 漏洞点：报错直接显示给用户
    } else {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>si3 商品查询 v2</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🛒 商品查询 v2（si3 报错注入）</h1>
<form method="get">
  商品 ID：<input type="text" name="id" value="1">
  <button type="submit">查询</button>
</form>
<?php if ($sql): ?><p style="color:#888">执行的 SQL：<code><?= htmlspecialchars($sql) ?></code></p><?php endif; ?>
<?php if ($error): ?><p style="color:#c00">❌ 查询出错：<?= htmlspecialchars($error) ?></p><?php endif; ?>
<?php if ($rows): ?>
<table border="1" cellpadding="6">
<tr><th>名称</th><th>价格</th></tr>
<?php foreach ($rows as $row): ?>
<tr><td><?= htmlspecialchars($row['name']) ?></td><td><?= htmlspecialchars($row['price']) ?></td></tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<p>提示：flag 藏在某张表里。页面会把报错显示出来——先让报错带出表名，再带列名，最后带数据。</p>
</body>
</html>