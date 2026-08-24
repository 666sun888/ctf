<?php
// ============================================
// 考点：DOM 型 XSS（payload 不经过服务器，纯前端拼接）
// 提示：看前端 JS！payload 在 URL 的 # 后面（location.hash）
// ============================================
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>xs3 欢迎页</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>👋 欢迎页（xs3 DOM 型 XSS）</h1>
<p>你的名字：</p>
<div id="greet" style="font-size:24px; color:#06c;"></div>
<script>
// 漏洞点：从 URL 的 # 后面取名字，直接 innerHTML 插入（不过服务器！）
var name = decodeURIComponent(location.hash.substring(1));
document.getElementById('greet').innerHTML = name;
</script>
<p style="color:#888">提示：URL 里 # 后面的内容不会被发给服务器——payload 在这里：<code>#&lt;img src=x onerror=alert(1)&gt;</code></p>
</body>
</html>
