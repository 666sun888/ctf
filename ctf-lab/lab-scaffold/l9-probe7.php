<?php
// 收货名单盘点：unserialize 到底收哪些原生类（PHP 7.0.9 实测）
error_reporting(E_ALL & ~E_NOTICE);
$cands = array(
    'SoapClient'       => 'O:10:"SoapClient":3:{s:3:"uri";s:4:"http";s:8:"location";s:4:"http";s:13:"_soap_version";i:1;}',
    'Error'            => 'O:5:"Error":1:{s:10:"' . "\0*\0" . 'message";s:1:"x";}',
    'Exception'        => 'O:9:"Exception":1:{s:10:"' . "\0*\0" . 'message";s:1:"x";}',
    'SimpleXMLElement' => 'O:17:"SimpleXMLElement":0:{}',
    'ArrayObject(跳过:Serializable家族,无害)',
    'stdClass'         => 'O:8:"stdClass":1:{s:3:"foo";s:3:"bar";}',
    'DirectoryIterator'=> 'O:17:"DirectoryIterator":0:{}',
);
foreach ($cands as $name => $payload) {
    $o = @unserialize($payload);
    $verdict = ($o === false) ? '拒收' : ('收了 -> ' . get_class($o));
    printf("  %-18s %s\n", $name, $verdict);
}
