<?php
// ============================================
// 考点：白名单 + 内容校验 + 包含联动（L3 毕业考）
// 提示：只收 jpg/jpeg/png/gif（白名单），且 getimagesize 必须通过（内容校验）
//      但页面还有"图片查看器"：?img= 会把你传的图片 include 进来渲染
//      flag 在 /flag_up4.php（.php 结尾，直接包含读不到内容——要 RCE）
// ============================================
$allow = ['jpg', 'jpeg', 'png', 'gif'];
$msg = '';
$blocked = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['f'])) {
    $name = basename($_FILES['f']['name']);
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, $allow)) {
        $blocked = '❌ 拦截：只允许 jpg/jpeg/png/gif';
    } elseif (@getimagesize($_FILES['f']['tmp_name']) === false) {
        $blocked = '❌ 拦截：文件内容不是合法图片';
    } else {
        move_uploaded_file($_FILES['f']['tmp_name'], __DIR__ . '/uploads/' . $name);
        $msg = '上传成功：/up4/uploads/' . $name;
    }
}
// 图片查看器（漏洞点：include 用户可控的文件名）
$img = basename($_GET['img'] ?? '');   // basename 防穿越 → 只能包含本目录 uploads/ 下的文件
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>up4 云相册</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>📷 云相册（up4 毕业考）</h1>
<form method="post" enctype="multipart/form-data">
  上传照片：<input type="file" name="f">
  <button type="submit">上传</button>
</form>
<?php if ($blocked): ?><p style="color:#c00"><?= htmlspecialchars($blocked) ?></p><?php endif; ?>
<?php if ($msg): ?><p style="color:#080"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
<hr>
<h3>图片查看器</h3>
<form method="get">
  文件名（如 photo.gif）：<input type="text" name="img" size="20">
  <button type="submit">查看</button>
</form>
<?php if ($img !== '' && is_file(__DIR__ . '/uploads/' . $img)): ?>
<div style="border:1px dashed #999; padding:12px; margin-top:12px;">
<?php include(__DIR__ . '/uploads/' . $img); ?>
</div>
<?php endif; ?>
<p style="color:#888">提示：白名单 + getimagesize 内容校验都过了才算上传成功；但查看器会把你传的图片 <b>include</b> 进来渲染——GIF89a 开头的文件也是合法 GIF。flag 在 /flag_up4.php。</p>
</body>
</html>
