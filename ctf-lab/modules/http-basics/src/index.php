<?php
$challenges = [
  ["ch1", "只许 POST", "请求方法"],
  ["ch2", "改头换面", "请求头伪造"],
  ["ch3", "Cookie 的秘密", "Cookie 伪造"],
  ["ch4", "神秘编码", "URL 编码"],
];
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>HTTP 基础模块</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🖥️ HTTP 基础模块</h1>
<p>先把 <code>modules/http-basics/src/ch*/index.php</code> 的源码全部读一遍，再开始做题。</p>
<ul>
<?php foreach ($challenges as [$dir, $name, $tag]): ?>
  <li><a href="/<?= $dir ?>/"><?= htmlspecialchars($name) ?></a> —— 考点：<?= htmlspecialchars($tag) ?></li>
<?php endforeach; ?>
</ul>
<li><a href="/tool/">🔬 实验场</a> —— 观察请求如何被服务器解析（不是题目，随便玩）</li>
<p>做完题后对照 <code>writeups/http-basics.md</code> 复盘。</p>
</body>
</html>
