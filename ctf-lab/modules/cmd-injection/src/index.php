<?php
$challenges = [
  ["ci1", "直接注入", "分号拼接"],
  ["ci2", "拦分号", "分隔符绕过"],
  ["ci3", "拦空格和关键字", "IFS/等价命令/通配符"],
  ["ci4", "拦所有分隔符", "命令替换"],
];
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>命令注入模块</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>💉 命令注入模块</h1>
<p>先把 <code>modules/cmd-injection/src/ci*/index.php</code> 的源码全部读一遍，再开始做题。</p>
<ul>
<?php foreach ($challenges as [$dir, $name, $tag]): ?>
  <li><a href="/<?= $dir ?>/"><?= htmlspecialchars($name) ?></a> —— 考点：<?= htmlspecialchars($tag) ?></li>
<?php endforeach; ?>
</ul>
<p>做题前建议先读讲义 lesson-02 第 3 节（分隔符全家桶）和第 6 节（过滤绕过）。</p>
</body>
</html>
