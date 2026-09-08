<?php
// ============================================
// 考点：HTTP 请求方法（Method）
// 速查：course/Python与HTTP速查.md 第 6 节（方法速查）
// 提示：服务器只检查了请求方法
// ============================================
$flag = "flag{ch1_post_me}";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die("<h1>405 Method Not Allowed</h1><p>管理员说：flag 只给 POST 请求。</p>");
}

echo "<h1>🎉 恭喜！flag: <code>$flag</code></h1>";
