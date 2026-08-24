<?php
// ============================================
// 考点：黑名单绕过（大小写 + 黑名单列不全）
// 提示：拦了 php/php3/php4/php5/phtml
//      读源码看检查方式——和 si2b 的大小写绕过同一个思路
//      flag 在 /flag_up2.txt
// ============================================
$blacklist = ['php', 'php3', 'php4', 'php5', 'phtml'];
$msg = '';
$blocked = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['f'])) {
    $name = basename($_FILES['f']['name']);
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    if (in_array($ext, $blacklist)) {   // ⚠️ 没有 strtolower → 大小写敏感；黑名单也没列全
        $blocked = '❌ 拦截：.' . $ext . ' 在黑名单里';
    } else {
        move_uploaded_file($_FILES['f']['tmp_name'], __DIR__ . '/uploads/' . $name);
        $msg = '上传成功：/up2/uploads/' . $name;
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>up2 头像上传 v2</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🖼️ 头像上传 v2（up2 黑名单）</h1>
<form method="post" enctype="multipart/form-data">
  选择文件：<input type="file" name="f">
  <button type="submit">上传</button>
</form>
<?php if ($blocked): ?><p style="color:#c00"><?= htmlspecialchars($blocked) ?></p><?php endif; ?>
<?php if ($msg): ?><p style="color:#080"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
<p style="color:#888">提示：黑名单拦了 php 系扩展名（读源码看拦了哪些、怎么拦的）。传上去之后还要能被服务器当代码解析——哪些扩展名会执行，取决于服务器配置。flag 在 /flag_up2.txt。</p>
</body>
</html>
