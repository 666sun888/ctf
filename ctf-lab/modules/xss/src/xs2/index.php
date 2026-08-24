<?php
// ============================================
// 考点：存储型 XSS（留言板，payload 持久化）
// 提示：留言会存下来，所有人访问都看到——试试存个 <script>
// ============================================
$msgs = [];
$file = '/var/www/html/xs2/messages.txt';

// 读取已有留言
if (file_exists($file)) {
    $msgs = array_filter(explode("
", file_get_contents($file)));
}

// 提交新留言（直接拼接，不转义 → 存储型 XSS）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['msg']) && $_POST['msg'] !== '') {
    file_put_contents($file, $_POST['msg'] . "
", FILE_APPEND);
    header('Location: /xs2/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>xs2 留言板</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>📝 留言板（xs2 存储型 XSS）</h1>
<form method="post">
  <input type="text" name="msg" placeholder="说点什么..." size="40">
  <button type="submit">留言</button>
</form>
<hr>
<h3>全部留言（所有人可见）：</h3>
<?php foreach ($msgs as $m): ?>
  <div style="border-bottom:1px solid #eee; padding:8px;"><?= $m ?></div>
<?php endforeach; ?>
</body>
</html>
