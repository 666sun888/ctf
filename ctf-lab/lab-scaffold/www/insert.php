<?php
require 'common.php';
$n = $_POST['name'] ?? ''; $m = $_POST['msg'] ?? '';
$done = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO lab_feedback(name,msg) VALUES('$n','$m')";
    $done = mysqli_query($conn, $sql);
    $ins_err = $done ? '' : mysqli_error($conn);   // 当场抓错,后面 $list 的 SELECT 会清掉错误状态
}
$list = mysqli_query($conn, "SELECT name,msg FROM lab_feedback ORDER BY id DESC LIMIT 10");
?>
<!DOCTYPE html><html lang="zh"><head><meta charset="utf-8"><title>留言板</title></head>
<body style="font-family:sans-serif;max-width:640px;margin:40px auto;">
<h1>📝 留言板（INSERT 场景）</h1>
<form method="post">昵称：<input name="name"><br><br>留言：<input name="msg" size="40"><br><br><button>提交</button></form>
<?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
<p><?= $done ? '留言已提交' : '提交失败（看下方报错）' ?></p>
<?php footer($sql, $ins_err); endif; ?>
<h2>最近留言</h2>
<?php while ($row = mysqli_fetch_assoc($list)): ?>
<p><?= h($row['name']) ?>：<?= h($row['msg']) ?></p>
<?php endwhile; ?>
<p><a href="index.php">返回菜单</a></p>
</body></html>
