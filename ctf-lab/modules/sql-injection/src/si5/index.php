<?php
$DB = 'ctf5';
require_once __DIR__ . '/../config.php';
// ============================================
// 考点：布尔盲注（Boolean-based Blind）
// 讲义：lesson-03b 第 1 节
// 提示：flag 在 flag5_table 表的 flag 字段；页面只有"查到/没查到"两种反应
// ============================================
$msg = '';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT name FROM products WHERE id=" . $id;
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $msg = "✅ 查询到了商品信息";
    } else {
        $msg = "❌ 商品不存在";
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>si5 商品查询（盲）</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🕶️ 商品查询（si5 布尔盲注）</h1>
<form method="get">
  商品 ID：<input type="text" name="id" value="1">
  <button type="submit">查询</button>
</form>
<p><?= $msg ?: '&nbsp;' ?></p>
<p style="color:#888">页面不显示数据，只告诉你查没查到。先试试 <code>1 and 1=1</code> 和 <code>1 and 1=2</code> 的区别。</p>
<p>提示：flag 在 flag5_table 表的 flag 字段。</p>
</body>
</html>