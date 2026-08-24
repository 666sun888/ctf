<?php
// ============================================
// 考点：.htaccess 上传（黑名单漏了它 → 接管目录解析规则）
// 提示：黑名单拦了 php 系扩展名（含大小写），但服务器目录允许 .htaccess 覆盖配置
//      Apache 的 .htaccess 能决定"什么后缀当什么跑"——你抢到了配置权
//      flag 在 /flag_up5.txt
// ============================================
$blacklist = ['php', 'php3', 'php4', 'php5', 'phtml', 'pht', 'phar'];
$msg = '';
$blocked = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['f'])) {
    $name = basename($_FILES['f']['name']);
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (in_array($ext, $blacklist)) {
        $blocked = '❌ 拦截：.' . $ext . ' 在黑名单里';
    } else {
        move_uploaded_file($_FILES['f']['tmp_name'], __DIR__ . '/uploads/' . $name);
        $msg = '上传成功：/up5/uploads/' . $name;
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>up5 黑名单 v5</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🖼️ 头像上传 v5（up5 黑名单 v2 加强版）</h1>
<form method="post" enctype="multipart/form-data">
  选择文件：<input type="file" name="f">
  <button type="submit">上传</button>
</form>
<?php if ($blocked): ?><p style="color:#c00"><?= htmlspecialchars($blocked) ?></p><?php endif; ?>
<?php if ($msg): ?><p style="color:#080"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
<p style="color:#888">提示：黑名单这次 lowercase 了、也把 .pht .phar 都补上了——php 系全灭。但黑名单只拦"文件"，拦得住"配置文件"吗？本站目录允许 .htaccess 生效（AllowOverride All）。flag 在 /flag_up5.txt。</p>
</body>
</html>
