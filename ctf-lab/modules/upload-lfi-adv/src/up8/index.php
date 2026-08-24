<?php
// ============================================
// 考点：Apache 解析漏洞（服务器配置背刺白名单）
// 提示：白名单 jpg/jpeg/png/gif + getimagesize 全开——
//      但本服务器 Apache 配置里有老式 AddHandler（把 .php 扩展按"从右往左"匹配处理器）
//      shell.php.jpg 在服务器眼里是什么？
//      flag 在 /flag_up8.txt
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
        $msg = '上传成功：/up8/uploads/' . $name;
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>up8 解析漏洞</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🖼️ 云相册 v7（up8 白名单铁壁）</h1>
<form method="post" enctype="multipart/form-data">
  上传照片：<input type="file" name="f">
  <button type="submit">上传</button>
</form>
<?php if ($blocked): ?><p style="color:#c00"><?= htmlspecialchars($blocked) ?></p><?php endif; ?>
<?php if ($msg): ?><p style="color:#080"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
<p style="color:#888">提示：白名单 + 内容校验全开，你连 .htaccess 都传不进来（注意看黑名单——哦这次根本没有黑名单，是纯白名单）。但"你的代码能不能跑"最后由谁说了算？——Apache 的配置。本服务器用了老式 AddHandler 解析规则。flag 在 /flag_up8.txt。</p>
</body>
</html>
