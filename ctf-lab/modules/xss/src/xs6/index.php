<?php
// ============================================
// 考点：存储型 XSS 打管理员（业务联动）
// 提示：留言无过滤（存储型）；flag 在"管理员"的 Cookie 里；
//       访问 /xs6/admin.php 会模拟管理员查看留言板
// ============================================
$msgs = [];
$file = '/var/www/html/xs6/msgs.txt';
if (file_exists($file)) {
    $msgs = array_filter(explode("
", file_get_contents($file)));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['msg']) && $_POST['msg'] !== '') {
    file_put_contents($file, $_POST['msg'] . "
", FILE_APPEND);
    header('Location: /xs6/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>xs6 留言板</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>📝 留言板 v2（xs6 打管理员）</h1>
<form method="post">
  <input type="text" name="msg" placeholder="说点什么..." size="40">
  <button type="submit">留言</button>
</form>
<hr>
<h3>全部留言：</h3>
<?php foreach ($msgs as $m): ?>
  <div style="border-bottom:1px solid #eee; padding:8px;"><?= $m ?></div>
<?php endforeach; ?>
<p style="color:#888">提示：flag 在管理员 Cookie 里（<code>admin_session</code>，没设 HttpOnly）。留言里注入偷 Cookie 的 payload，然后访问 <a href="/xs6/admin.php">/xs6/admin.php</a> 模拟管理员查看留言——看看能不能偷到管理员身份。</p>
</body>
</html>
