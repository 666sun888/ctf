<?php
$DB = 'ctf3b';
require_once __DIR__ . '/../config.php';
// ============================================
// 考点：报错注入变种——updatexml 被禁 + 超长 flag 强制分段
// 讲义：lesson-03 第 6.1 节
// 提示：updatexml 被禁了，但它有个同族兄弟（参数还更少）；flag 共 47 个字符
// ============================================
$rows = [];
$error = '';
$blocked = '';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    if (preg_match('/updatexml/i', $id)) {
        $blocked = "❌ 拦截：不允许 updatexml";
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
<head><meta charset="utf-8"><title>si3b 商品查询（禁函数版）</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🛒 商品查询 v3b（si3b 报错注入变种）</h1>
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
<p>提示：updatexml 被禁了，但它有个同族兄弟（参数还更少，讲义 6.1 有全家桶）；flag 共 47 个字符，一次报不完。</p>
</body>
</html>
