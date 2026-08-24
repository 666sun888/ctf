<?php
$DB = 'ctf4';
require_once __DIR__ . '/../config.php';
// ============================================
// 考点：过滤绕过（拦空白字符 + union select 连写）
// 讲义：lesson-03 第 7 节
// 提示：空白/union select 连写被拦；表名列名自己找
// ============================================
$rows = [];
$error = '';
$sql = '';
$blocked = '';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    if (preg_match('/\s/', $id)) {
        $blocked = "❌ 拦截：不允许空白字符";
    } elseif (preg_match('/union\s+select/i', $id)) {
        $blocked = "❌ 拦截：union select";
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
<head><meta charset="utf-8"><title>si4 商品查询 v3</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🛒 商品查询 v3（si4 拦 union select）</h1>
<form method="get">
  商品 ID：<input type="text" name="id" value="1">
  <button type="submit">查询</button>
</form>
<?php if ($blocked): ?><p style="color:#c00"><?= htmlspecialchars($blocked) ?></p><?php endif; ?>
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

<p>提示：空白字符被拦、union select 连写被拦，但注释 /**/ 不在黑名单里。表名列名自己找。</p>
</body>
</html>