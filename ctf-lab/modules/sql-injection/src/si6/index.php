<?php
$DB = 'ctf6';
require_once __DIR__ . '/../config.php';
// ============================================
// 考点：时间盲注（Time-based Blind）
// 讲义：lesson-03b 第 2 节
// 提示：flag 在 flag6_table 表的 flag 字段；页面永远一样，信号是时间
// ============================================
$msg = '';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT name FROM products WHERE id=" . $id;
    $result = mysqli_query($conn, $sql);
    $msg = ($result && mysqli_num_rows($result) > 0) ? "✅ 查询完成" : "❌ 查询完成";
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>si6 商品查询（更盲）</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🫥 商品查询（si6 时间盲注）</h1>
<form method="get">
  商品 ID：<input type="text" name="id" value="1">
  <button type="submit">查询</button>
</form>
<p><?= $msg ?: '&nbsp;' ?></p>
<p style="color:#888">页面永远显示一样的内容——但试试 <code>1 and sleep(3)</code>，感受一下时间。</p>
<p>提示：flag 在 flag6_table 表的 flag 字段。</p>
</body>
</html>