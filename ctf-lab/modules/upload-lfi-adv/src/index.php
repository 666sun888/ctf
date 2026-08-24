<?php
$l1 = [
  ["up6", "黑名单 v4", "双写绕过"],
  ["fi5", "导航 v5", "日志包含"],
];
$l2 = [
  ["up5", "黑名单 v5", ".htaccess 接管"],
  ["up7", "黑名单 v6", "竞争条件"],
  ["fi6", "导航 v6", "Session 包含"],
  ["fi7", "导航 v7", "pearcmd RCE"],
];
$l3 = [
  ["up8", "白名单 v7", "Apache 解析漏洞"],
  ["fi8", "导航 v8", "拼接+日志+吸收 毕业考"],
];
function render($list) {
  foreach ($list as [$dir, $name, $tag]) {
    echo '<li><a href="/' . $dir . '/">' . htmlspecialchars($name) . '</a> —— ' . htmlspecialchars($tag) . '</li>';
  }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>上传/包含 进阶（第 5b 课）</title></head>
<body style="font-family: sans-serif; max-width: 680px; margin: 40px auto;">
<h1>🚀 上传/包含 进阶（第 5b 课）</h1>
<p>本模块 <b>allow_url_include 已关闭</b>——fi4 的 data:// 在这里全部失效。难度：L1 → L2 → L3。先读源码。</p>
<h2>🟢 L1</h2>
<ul><?php render($l1); ?></ul>
<h2>🟡 L2</h2>
<ul><?php render($l2); ?></ul>
<h2>🔴 L3</h2>
<ul><?php render($l3); ?></ul>
<p style="color:#888">题解：writeups/upload-lfi-adv.md（做完再看）</p>
</body>
</html>
