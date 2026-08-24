<?php
// ============================================
// 考点：文件上传无过滤（最基础的上传漏洞）
// 提示：扩展名/类型/内容什么都没检查
//      上传成功的文件放在 /up1/uploads/，浏览器直接访问
//      flag 在 /flag_up1.txt
// ============================================
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['f'])) {
    $name = basename($_FILES['f']['name']);
    if (move_uploaded_file($_FILES['f']['tmp_name'], __DIR__ . '/uploads/' . $name)) {
        $msg = '上传成功：/up1/uploads/' . $name;
    } else {
        $msg = '上传失败';
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>up1 头像上传</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🖼️ 头像上传（up1 无过滤）</h1>
<form method="post" enctype="multipart/form-data">
  选择文件：<input type="file" name="f">
  <button type="submit">上传</button>
</form>
<?php if ($msg): ?><p style="color:#080"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
<p style="color:#888">提示：什么都没检查。传上去的文件在 /up1/uploads/ 下直接访问。flag 在 /flag_up1.txt。</p>
</body>
</html>
