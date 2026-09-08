<?php
// ============================================
// 内网服务器（模拟）——秘密接口
// 真实场景：这台机器只在内网，外部根本路由不到
// 本地模拟：它只接受 SOAP 调用（POST + text/xml），浏览器直连会被拒
// ============================================
$method = $_SERVER['REQUEST_METHOD'] ?? '';
$ct      = $_SERVER['CONTENT_TYPE'] ?? '';

if ($method !== 'POST' || stripos($ct, 'xml') === false) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "403 内网服务：我不是网页，我只接受 SOAP 调用（POST + text/xml）。\n";
    echo "你的来源：{$method} " . ($ct !== '' ? "Content-Type: {$ct}" : '无 Content-Type') . "\n";
    exit;
}

$flag = trim(@file_get_contents(__DIR__ . '/flag_l9.txt'));
if ($flag === '') { $flag = '(flag 文件缺失，请联系教练)'; }

// 命中记账：谁、什么时候、用什么 UA 来调过我（SoapClient 的 UA 和 curl 的不一样，一眼可辨）
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '(no UA)';
file_put_contents(
    __DIR__ . '/hit_l9.txt',
    date('Y-m-d H:i:s') . ' | from ' . ($_SERVER['REMOTE_ADDR'] ?? '?') . ' | UA=' . $ua . ' | flag delivered' . "\n",
    FILE_APPEND
);

// 用 SOAP Fault 把旗"喊"回去：SoapClient 收到 Fault 会抛异常，
// 异常的 getMessage() 就是 faultstring —— 攻击者在挑战页上能直接看到这句话
header('Content-Type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">' . "\n";
echo '  <soap:Body>' . "\n";
echo '    <soap:Fault>' . "\n";
echo '      <faultcode>soap:Server</faultcode>' . "\n";
echo '      <faultstring>' . htmlspecialchars($flag, ENT_XML1) . '</faultstring>' . "\n";
echo '    </soap:Fault>' . "\n";
echo '  </soap:Body>' . "\n";
echo '</soap:Envelope>' . "\n";
