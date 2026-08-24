<?php
// ============================================
// 考点：综合毕业考——多重过滤 + 存储型 + 偷 Cookie
// 提示：过滤删了 script / onerror / onload / alert（大小写不敏感）
//       flag 在管理员 Cookie 里（/xs7/admin.php 模拟管理员查看）
// ============================================
$msgs = [];
$file = '/var/www/html/xs7/msgs.txt';
if (file_exists($file)) {
    $msgs = array_filter(explode("
", file_get_contents($file)));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['msg']) && $_POST['msg'] !== '') {
    // 多重过滤（删除式，大小写不敏感）
    $msg = str_ireplace(['script', 'onerror', 'onload', 'alert'], '', $_POST['msg']);
    file_put_contents($file, $msg . "
", FILE_APPEND);
    header('Location: /xs7/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>xs7 留言板（加固版）</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>📝 留言板 v3（xs7 综合毕业考）</h1>
<form method="post">
  <input type="text" name="msg" placeholder="说点什么..." size="40">
  <button type="submit">留言</button>
</form>
<hr>
<h3>全部留言：</h3>
<?php foreach ($msgs as $m): ?>
  <div style="border-bottom:1px solid #eee; padding:8px;"><?= $m ?></div>
<?php endforeach; ?>
<p style="color:#888">提示：script / onerror / onload / alert 都被删了（删除式+大小写不敏感）。flag 在管理员 Cookie（admin_session，无 HttpOnly），管理员在 <a href="/xs7/admin.php">/xs7/admin.php</a> 看留言。构造一个能绕过过滤、还能自动触发、还能外带 Cookie 的 payload——想想：事件不止 onerror/onload；函数不止 alert；删了还能嵌套。</p>
</body>
</html>
