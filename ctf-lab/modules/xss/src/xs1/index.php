<?php
// ============================================
// 考点：反射型 XSS
// 提示：?q= 参数被直接输出，试试 <script>alert(1)</script>
// ============================================
$q = $_GET['q'] ?? '';
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>xs1 搜索</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🔍 搜索（xs1 反射型 XSS）</h1>
<form method="get">
  <input type="text" name="q" placeholder="搜索关键词" size="30">
  <button type="submit">搜索</button>
</form>
<?php if ($q !== ''): ?>
  <p>你搜索的是：<?= $q ?></p>
<?php endif; ?>
</body>
</html>
