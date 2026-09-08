<?php
require_once __DIR__ . '/../config.php';
// ============================================
// 考点：读文件 LOAD_FILE()
// 速查：course/SQL注入速查.md 第 7 节（读文件 LOAD_FILE）
// 提示：flag 在 MySQL 容器的 /var/lib/mysql-files/flag.txt；试试 load_file
// ============================================
$rows = [];
$error = '';
$sql = '';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
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
<head><meta charset="utf-8"><title>si8 商品查询 v5</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>📁 商品查询 v5（si8 读文件）</h1>
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
<tr><td><?= htmlspecialchars($row['name'] ?? '') ?></td><td><?= htmlspecialchars($row['price'] ?? '') ?></td></tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
<p>提示：flag 在 MySQL 容器的 /var/lib/mysql-files/flag.txt；用 <code>load_file('/var/lib/mysql-files/flag.txt')</code> 读出来。</p>
</body>
</html>