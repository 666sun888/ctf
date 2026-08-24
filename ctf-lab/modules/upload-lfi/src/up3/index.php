<?php
// ============================================
// 考点：黑名单 + MIME 双重检查（伪造 Content-Type）
// 提示：扩展名黑名单这次 strtolower 了（大小写绕不过）
//      又加了 Content-Type 必须是 image/*
//      但 Content-Type 是请求头——回想 ch2 改头换面
//      flag 在 /flag_up3.txt
// ============================================
$blacklist = ['php', 'php3', 'php4', 'php5', 'phtml'];
$allow_type = ['image/jpeg', 'image/png', 'image/gif'];
$msg = '';
$blocked = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['f'])) {
    $name = basename($_FILES['f']['name']);
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (in_array($ext, $blacklist)) {
        $blocked = '❌ 拦截：.' . $ext . ' 在黑名单里';
    } elseif (!in_array($_FILES['f']['type'], $allow_type)) {
        $blocked = '❌ 拦截：Content-Type 必须是 image/jpeg|png|gif（你传的是 ' . $_FILES['f']['type'] . '）';
    } else {
        move_uploaded_file($_FILES['f']['tmp_name'], __DIR__ . '/uploads/' . $name);
        $msg = '上传成功：/up3/uploads/' . $name;
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>up3 头像上传 v3</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🖼️ 头像上传 v3（up3 黑名单 + MIME）</h1>
<form method="post" enctype="multipart/form-data">
  选择文件：<input type="file" name="f">
  <button type="submit">上传</button>
</form>
<?php if ($blocked): ?><p style="color:#c00"><?= htmlspecialchars($blocked) ?></p><?php endif; ?>
<?php if ($msg): ?><p style="color:#080"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
<p style="color:#888">提示：扩展名这关大小写绕不过了（源码里 strtolower 了）——但黑名单还漏着谁？MIME 检查的是请求头，而请求头是谁说了算？flag 在 /flag_up3.txt。</p>
</body>
</html>
