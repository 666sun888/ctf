<?php
$DB = 'ctf7';
require_once __DIR__ . '/../config.php';
// ============================================
// 考点：堆叠注入（Stacked Injection，mysqli_multi_query）
// 速查：course/SQL注入速查.md 第 5 节（堆叠注入）
// 提示：union 被拦了，但分号没有被拦——show tables 会告诉你一切
// ============================================
$rows = [];
$error = '';
$sql = '';
$blocked = '';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    if (preg_match('/union/i', $id)) {
        $blocked = "❌ 拦截：不允许 union";
    } else {
        $sql = "SELECT id, name FROM products WHERE id=" . $id;
        // 注意：这里用的是 mysqli_multi_query（支持多条语句！）
        if (mysqli_multi_query($conn, $sql)) {
            do {
                if ($result = mysqli_store_result($conn)) {
                    while ($row = mysqli_fetch_row($result)) {
                        $rows[] = $row;
                    }
                    mysqli_free_result($result);
                }
            } while (mysqli_next_result($conn));
        } else {
            $error = mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>si7 商品查询 v4</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🗂️ 商品查询 v4（si7 堆叠注入）</h1>
<form method="get">
  商品 ID：<input type="text" name="id" value="1">
  <button type="submit">查询</button>
</form>
<?php if ($blocked): ?><p style="color:#c00"><?= htmlspecialchars($blocked) ?></p><?php endif; ?>
<?php if ($sql): ?><p style="color:#888">执行的 SQL：<code><?= htmlspecialchars($sql) ?></code></p><?php endif; ?>
<?php if ($error): ?><p style="color:#c00">❌ 出错：<?= htmlspecialchars($error) ?></p><?php endif; ?>
<?php if ($rows): ?>
<table border="1" cellpadding="6">
<?php foreach ($rows as $row): ?>
<tr><?php foreach ($row as $cell): ?><td><?= htmlspecialchars($cell ?? '') ?></td><?php endforeach; ?></tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<p>提示：union 被拦了，但分号没有被拦——show tables 会告诉你一切。</p>
</body>
</html>