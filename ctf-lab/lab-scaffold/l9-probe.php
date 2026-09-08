<?php
// L9 备课实验：SoapClient 序列化形态 + 回路 + __call 行为
$c = new SoapClient(null, array(
    'uri'      => 'http://127.0.0.1:9999/',
    'location' => 'http://127.0.0.1:9999/x',
));
$s = serialize($c);
echo "=== serialize 形态（cat -v 视角看不可见字节）===\n";
echo addcslashes($s, "\0..\37"), "\n\n";

$o = unserialize($s);
echo "=== 回路：unserialize 后还是 SoapClient 吗 ===\n";
var_dump($o instanceof SoapClient);

echo "=== 属性透视：location 活下来了吗 ===\n";
$r = new ReflectionObject($o);
foreach ($r->getProperties() as $p) {
    $p->setAccessible(true);
    echo $p->getName(), ' = ', var_export($p->getValue($o), true), "\n";
}

echo "\n=== 经典裸名 payload（无 \\0）能复活吗 ===\n";
$classic = 'O:10:"SoapClient":4:{s:3:"uri";s:22:"http://127.0.0.1:9999/";s:8:"location";s:27:"http://127.0.0.1:9999/classic";s:15:"_stream_context";i:0;s:11:"_user_agent";s:0:"";}';
$len_uri = strlen('http://127.0.0.1:9999/');
$len_loc = strlen('http://127.0.0.1:9999/classic');
$classic = 'O:10:"SoapClient":4:{s:3:"uri";s:'.$len_uri.':"http://127.0.0.1:9999/";s:8:"location";s:'.$len_loc.':"http://127.0.0.1:9999/classic";s:15:"_stream_context";i:0;s:11:"_user_agent";s:0:"";}';
$o2 = @unserialize($classic);
var_dump($o2 instanceof SoapClient);
if ($o2 instanceof SoapClient) {
    $r2 = new ReflectionObject($o2);
    foreach ($r2->getProperties() as $p) {
        $p->setAccessible(true);
        echo $p->getName(), ' = ', var_export($p->getValue($o2), true), "\n";
    }
}
