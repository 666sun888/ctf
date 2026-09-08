<?php
$DB = 'ctf2';
require_once __DIR__ . '/../config.php';
// ============================================
// 考点：数字注入 + UNION 提取数据
// 速查：course/SQL注入速查.md 第 1 节（UNION 注入）
// 提示：flag 藏在数据库的某张表里，表名/列名都要自己找（information_schema）
// ============================================
$rows = [];
$error = '';
$sql = '';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // 漏洞点：数字参数直接拼接
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
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>si2 商品查询</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🛒 商品查询（si2 UNION 注入）</h1>
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

<p>提示：flag 藏在数据库的某张表里——表名、列名都要你自己找（提示：information_schema）。商品表 SELECT 了 2 列。</p>
</body>
</html>