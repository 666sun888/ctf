<?php
$l1 = [
  ["up1", "头像上传", "无过滤直接传 webshell"],
  ["fi1", "站点导航", "LFI 路径穿越"],
];
$l2 = [
  ["up2", "头像上传 v2", "黑名单绕过"],
  ["up3", "头像上传 v3", "黑名单 + MIME 伪造"],
  ["fi2", "站点导航 v2", "前后缀拼接突围"],
  ["fi3", "站点导航 v3", "php://filter 读源码"],
  ["fi4", "站点导航 v4", "data:// 伪协议 RCE"],
];
$l3 = [
  ["up4", "云相册", "图片马 + 包含联动（毕业考）"],
];
function render($list) {
  foreach ($list as [$dir, $name, $tag]) {
    echo '<li><a href="/' . $dir . '/">' . htmlspecialchars($name) . '</a> —— ' . htmlspecialchars($tag) . '</li>';
  }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>文件上传/文件包含 模块（难度阶梯）</title></head>
<body style="font-family: sans-serif; max-width: 680px; margin: 40px auto;">
<h1>📤 文件上传 / 文件包含模块（难度阶梯）</h1>
<p>做题顺序：L1 基础 → L2 进阶 → L3 综合。先读 <code>modules/upload-lfi/src/</code> 源码。</p>
<h2>🟢 L1 基础</h2>
<ul><?php render($l1); ?></ul>
<h2>🟡 L2 进阶</h2>
<ul><?php render($l2); ?></ul>
<h2>🔴 L3 综合</h2>
<ul><?php render($l3); ?></ul>
<p style="color:#888">提示：上传题全部可以用 curl.exe -F 完成；做不出再看速查表对应小节。题解在 writeups/upload-lfi.md，做完再看。</p>
</body>
</html>
