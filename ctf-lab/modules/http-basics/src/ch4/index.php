<?php
// ============================================
// 考点：URL 编码 + GET 参数
// 讲义：lesson-01 第 6 节
// 提示：服务器解析 ?name=xxx 时会先做 URL 解码
// ============================================
$flag = "flag{ch4_urlencode}";

$name = $_GET['name'] ?? '';

if ($name === '') {
    die("<p>用法：<code>/ch4/?name=xxx</code>。先试试 name=admin 看看效果。</p>");
}
if ($name === 'admin') {
    echo "<h1>🎉 恭喜！flag: <code>$flag</code></h1>";
} else {
    echo "<p>你好，{$name}。只有 admin 能看到 flag。</p>";
}
