<?php
$DB = 'ctf10';
require_once __DIR__ . '/../config.php';
// ============================================
// 考点：报错注入 + 超长数据分段提取（substr）
// 速查：course/SQL注入速查.md 第 2 节（报错注入：超长分段）
// 提示：42字符的 flag 藏在某张表的 data 字段；extractvalue 一次只能报 32 个字符
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
<head><meta charset="utf-8"><title>si10 商品查询 v6</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>📏 商品查询 v6（si10 超长 flag）</h1>
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

<p>提示：flag 共 42 个字符，藏在某张表的 data 字段里——表名先自己找。extractvalue 报错最多显示 32 个字符，想想怎么分段取。</p>
</body>
</html>