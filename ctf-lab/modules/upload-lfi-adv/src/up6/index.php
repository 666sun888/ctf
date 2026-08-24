<?php
// ============================================
// 考点：双写绕过（过滤是"删除"而不是"拒绝"）
// 提示：str_replace 只扫一遍——删掉一个 php 后，
//      剩下的字符会不会重新拼出一个 php？
//      flag 在 /flag_up6.txt
// ============================================
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['f'])) {
    $name = str_replace('php', '', strtolower(basename($_FILES['f']['name'])));   // ⚠️ 删除而非拒绝，且只扫一遍
    if ($name === '') {
        $msg = '❌ 文件名处理完为空';
    } else {
        move_uploaded_file($_FILES['f']['tmp_name'], __DIR__ . '/uploads/' . $name);
        $msg = '上传成功：/up6/uploads/' . $name;
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>up6 黑名单 v4</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🖼️ 头像上传 v4（up6 删除式过滤）</h1>
<form method="post" enctype="multipart/form-data">
  选择文件：<input type="file" name="f">
  <button type="submit">上传</button>
</form>
<?php if ($msg): ?><p style="color:#080"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
<p style="color:#888">提示：这次的过滤不是"拒绝 php"，而是"把 php 从文件名里删掉"——注意看上传成功后服务器告诉你的文件名，和你的原始文件名有什么不同？flag 在 /flag_up6.txt。</p>
</body>
</html>
