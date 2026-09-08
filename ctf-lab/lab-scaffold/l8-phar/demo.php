<?php
// L8 演示靶:file_exists 引爆 phar metadata
// 用法: demo.php?path=phar://<绝对路径>/demo.phar
class P {
    public $msg = '默认消息';
    function __destruct(){ echo "  [P::destruct 被引爆] ", $this->msg, "\n"; }
}
if (isset($_GET['path'])) {
    var_dump(file_exists($_GET['path']));   // 就这一个"无害"的文件检查
} else {
    echo "参数 path = phar:// 打头的完整路径\n";
}
