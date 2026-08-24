<?php
// ============================================
// 考点：XSS 过滤绕过（删除式过滤）
// 提示：删了 <script> 和 alert——但事件属性和其他函数还在！
// ============================================
$q = $_GET['q'] ?? '';
// 弱过滤：大小写不敏感地删除（注意：删除式！删完会重新拼接）
$q = str_ireplace(['<script>', '</script>', 'alert'], '', $q);
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>xs5 搜索（过滤版）</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🔍 搜索 v2（xs5 过滤绕过）</h1>
<form method="get">
  <input type="text" name="q" placeholder="搜索关键词" size="30">
  <button type="submit">搜索</button>
</form>
<?php if ($q !== ''): ?>
  <p>你搜索的是：<?= $q ?></p>
<?php endif; ?>
<p style="color:#888">提示：<code>script</code> 和 <code>alert</code> 被删了（大小写不敏感）。但过滤是"删字符串"——想想：<code>onerror</code>、<code>confirm</code>、嵌套 <code>&lt;scr&lt;script&gt;ipt&gt;</code>、还有 <code>onload</code>、<code>prompt</code>、<code>svg</code>…</p>
</body>
</html>
