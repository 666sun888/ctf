<?php
// ============================================
// 考点：竞争条件（先保存、后检查、检查不过再删）
// 提示：move_uploaded_file 在检查之前就执行了——
//      文件在服务器上真实存在过一段时间（sleep 1 秒模拟扫描耗时）
//      上传线程不断传，访问线程疯狂刷——看谁快
//      flag 在 /flag_up7.txt
// ============================================
$allow = ['jpg', 'jpeg', 'png', 'gif'];
$msg = '';
$blocked = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['f'])) {
    $name = basename($_FILES['f']['name']);
    $target = __DIR__ . '/uploads/' . $name;
    move_uploaded_file($_FILES['f']['tmp_name'], $target);   // ⚠️ 先保存
    sleep(1);                                                 // 模拟"病毒扫描"耗时 —— 文件此刻真实存在
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, $allow)) {
        unlink($target);                                      // ⚠️ 后删除 —— 1 秒窗口
        $blocked = '❌ 扫描发现非图片，已删除（该文件曾存在 1 秒）';
    } else {
        $msg = '上传成功：/up7/uploads/' . $name;
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>up7 竞争条件</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🖼️ 头像上传 v6（up7 带病毒扫描）</h1>
<form method="post" enctype="multipart/form-data">
  选择文件：<input type="file" name="f">
  <button type="submit">上传</button>
</form>
<?php if ($blocked): ?><p style="color:#c00"><?= htmlspecialchars($blocked) ?></p><?php endif; ?>
<?php if ($msg): ?><p style="color:#080"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
<p style="color:#888">提示：本站上传带"病毒扫描"，扫描约 1 秒，发现非图片才删除。你上传的文件被拒绝 ≠ 文件从未存在过。flag 在 /flag_up7.txt。</p>
</body>
</html>
