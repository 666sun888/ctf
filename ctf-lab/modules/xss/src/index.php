<?php
$l1 = [
  ["xs1", "搜索", "反射型 XSS"],
  ["xs2", "留言板", "存储型 XSS"],
  ["xs3", "欢迎页", "DOM 型 XSS"],
  ["xs4", "用户中心", "偷 Cookie"],
];
$l2 = [
  ["xs5", "搜索 v2", "过滤绕过"],
  ["xs6", "留言板 v2", "存储型打管理员"],
];
$l3 = [
  ["xs7", "留言板 v3", "综合毕业考"],
];
function render($list) {
  foreach ($list as [$dir, $name, $tag]) {
    echo '<li><a href="/' . $dir . '/">' . htmlspecialchars($name) . '</a> —— ' . htmlspecialchars($tag) . '</li>';
  }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>XSS 模块（难度阶梯）</title></head>
<body style="font-family: sans-serif; max-width: 680px; margin: 40px auto;">
<h1>⚡ XSS 模块（难度阶梯）</h1>
<p>做题顺序：L1 基础 → L2 进阶 → L3 综合。先读 <code>modules/xss/src/</code> 源码。</p>
<h2>🟢 L1 基础</h2>
<ul><?php render($l1); ?></ul>
<h2>🟡 L2 进阶</h2>
<ul><?php render($l2); ?></ul>
<h2>🔴 L3 综合</h2>
<ul><?php render($l3); ?></ul>
<p style="color:#888">注意：xs4/xs6/xs7 需要浏览器测试（curl 不执行 JS）。题解在 writeups/xss.md，做完再看。</p>
</body>
</html>
