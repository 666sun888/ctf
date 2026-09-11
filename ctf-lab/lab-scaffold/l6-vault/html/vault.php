<?php
// ============================================
// L6 靶场：wakeup 防线（Vault 挑战，Web 版）
// 考点：__wakeup 绕过（CVE-2016-7124，靶机 PHP 7.0.9）
// 2026-09-11 修洞：旗原来躺在网站根目录，直接 GET /flag_l6.txt 就能拿——
//                  现已移出 docroot（docroot = 本目录，旗在上一级）
// 旗在 ../flag_l6.txt；safe.txt 是空城
// 参数：d = 你的序列化串（GET/POST 都收）
// ============================================
highlight_file(__FILE__);   // 源码全透明（与 L8/L9/L14 一致）

class Vault {
    public $target = 'safe.txt';
    function __wakeup(){
        $this->target = 'safe.txt';          // 防线：还原瞬间消毒
        echo "  [wakeup 防线启动：target 被消毒成 safe.txt]\n";
    }
    function __destruct(){
        echo "  [destruct 开火] 读取 ", $this->target, " -> ";
        echo @file_get_contents(__DIR__ . '/' . $this->target), "\n";   // 绝对路径：内置服务器关闭期会改回 CWD
    }
}
$d = isset($_REQUEST['d']) ? $_REQUEST['d'] : '';
if ($d === '') {
    echo "\n参数 d 收你的序列化串（GET/POST 均可）。目标文件不在本目录，想想相对路径怎么写。\n";
} else {
    $r = unserialize($d);
    if ($r === false) echo "  [unserialize 返回 false：串没被接受]\n";
}
