<?php
// ============================================
// L8 挑战:phar 反序列化(暗门引爆)
// 功能A: 头像上传(只收 GIF,查文件头前 6 字节)
// 功能B: 文件检查 ?check=完整路径(查文件在不在)
// 旗在 flag_l8.txt;Avatar 类是靶子
// ============================================
highlight_file(__FILE__);
class Avatar {
    public $target = 'safe.txt';
    function __destruct(){
        echo "  [Avatar::destruct 开火] ", @file_get_contents(__DIR__ . '/' . $this->target), "\n";
    }
}
$updir = __DIR__ . '/uploads';
if (!is_dir($updir)) mkdir($updir, 0777, true);
if (isset($_FILES['avatar'])) {
    $tmp = $_FILES['avatar']['tmp_name'];
    $head = $tmp ? (string)@file_get_contents($tmp, false, null, 0, 6) : '';
    if ($head === 'GIF89a') {
        $name = basename($_FILES['avatar']['name']);
        move_uploaded_file($tmp, $updir . '/' . $name);
        echo "上传成功,绝对路径: ", $updir, "/", $name, "\n";
    } else {
        echo "拒绝:文件头必须是 GIF89a\n";
    }
} elseif (isset($_GET['check'])) {
    echo "file_exists 结果: ", var_export(file_exists($_GET['check']), true), "\n";
}
