<?php
$DB = 'ctf5b';
require_once __DIR__ . '/../config.php';
// ============================================
// 考点：布尔盲注变种——空格被拦
// 速查：course/SQL注入速查.md 第 3 节（布尔盲注）+ 第 7 节（绕过）
// 提示：flag 在 flag5b_table 表的 flag 字段；空格被拦了，/**/ 还记得吗？
// ============================================
$msg = '';
$blocked = '';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    if (preg_match('/\s/', $id)) {
        $blocked = "❌ 拦截：不允许空白字符";
    } else {
        $sql = "SELECT name FROM products WHERE id=" . $id;
        $result = mysqli_query($conn, $sql);
        $msg = ($result && mysqli_num_rows($result) > 0) ? "✅ 查询到了商品信息" : "❌ 商品不存在";
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>si5b 商品查询（盲+过滤）</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🕶️ 商品查询 v5b（si5b 布尔盲注变种）</h1>
<form method="get">
  商品 ID：<input type="text" name="id" value="1">
  <button type="submit">查询</button>
</form>
<?php if ($blocked): ?><p style="color:#c00"><?= htmlspecialchars($blocked) ?></p><?php endif; ?>
<p><?= $msg ?: '&nbsp;' ?></p>
<p>提示：flag 在 flag5b_table 表的 flag 字段；空格被拦了——但 <code>/**/</code> 可以当空格用（si4 的招）。改改盲注脚本？</p>
</body>
</html>
