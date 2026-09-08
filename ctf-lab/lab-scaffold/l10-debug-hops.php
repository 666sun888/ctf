<?php
// L10 验靶调试二：手动逐跳走析构链，找静默断点
error_reporting(E_ALL);
ini_set('display_errors', '1');
spl_autoload_register(function ($class) {
    $map = array(
        'Monolog\\' => __DIR__ . '/vendor/monolog/monolog/src/Monolog/',
        'Psr\\Log\\' => __DIR__ . '/vendor/psr/log/Psr/Log/',
    );
    foreach ($map as $prefix => $dir) {
        if (strpos($class, $prefix) === 0) {
            $path = $dir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($path)) require $path;
            return;
        }
    }
});
$d = $argv[1] ?? '';
$obj = unserialize(base64_decode($d));
echo "复活: ", get_class($obj), "\n";

// 跳1：SyslogUdpHandler::close
try { $obj->close(); echo "跳1 close() 走完，无异常\n"; }
catch (Throwable $t) { echo "跳1炸: ", get_class($t), ": ", $t->getMessage(), "\n"; }

// 直接看 BufferHandler 内部状态
$rp = new ReflectionProperty('Monolog\Handler\SyslogUdpHandler', 'socket');
$rp->setAccessible(true);
$buf = $rp->getValue($obj);
echo "socket 是: ", get_class($buf), "\n";
foreach (array('handler', 'bufferSize', 'buffer', 'level', 'initialized', 'bufferLimit', 'processors') as $prop) {
    $rp = new ReflectionProperty('Monolog\Handler\BufferHandler', $prop);
    $rp->setAccessible(true);
    var_dump($prop, $rp->getValue($buf));
}
