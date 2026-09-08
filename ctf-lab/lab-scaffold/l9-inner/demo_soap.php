<?php
// demo 专用内网端点：跟 flag_l9.php 机制一模一样，但回的话里没有 flag
// 用途：demo.php 的现场演示（教材弹打这里，挑战弹打 flag_l9.php）
$method = $_SERVER['REQUEST_METHOD'] ?? '';
$ct      = $_SERVER['CONTENT_TYPE'] ?? '';

if ($method !== 'POST' || stripos($ct, 'xml') === false) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "403 demo 端点：只接受 SOAP 调用（POST + text/xml）。\n";
    exit;
}

file_put_contents(
    __DIR__ . '/hit_l9.txt',
    date('Y-m-d H:i:s') . ' | demo_soap hit | UA=' . ($_SERVER['HTTP_USER_AGENT'] ?? '(no UA)') . "\n",
    FILE_APPEND
);

$msg = '内网服务回话：SSRF 演示成功！你的请求借 SoapClient 的手摸到了我这台"内网机器"。正式挑战去打 /flag_l9.php，旗在那边。';
header('Content-Type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">' . "\n";
echo '  <soap:Body>' . "\n";
echo '    <soap:Fault>' . "\n";
echo '      <faultcode>soap:Server</faultcode>' . "\n";
echo '      <faultstring>' . htmlspecialchars($msg, ENT_XML1) . '</faultstring>' . "\n";
echo '    </soap:Fault>' . "\n";
echo '  </soap:Body>' . "\n";
echo '</soap:Envelope>' . "\n";
