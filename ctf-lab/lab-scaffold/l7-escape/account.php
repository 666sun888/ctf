<?php
// ============================================
// L7 挑战:字符串逃逸(变长)
// 情景:账户资料序列化存档,存档前"净化"——把 # 替换成 ##
// 旗在 flag_l7.txt,只有 is_admin === true 才能读
// 参数:user = 你的输入
// ============================================
highlight_file(__FILE__);
class Account {
    public $user;
    public $is_admin = false;
}
$a = new Account;
$a->user = isset($_GET['user']) ? $_GET['user'] : 'guest';
$ser = str_replace('#', '##', serialize($a));
echo "存档串: ", $ser, "\n";
$obj = unserialize($ser);
echo "还原结果: ", ($obj === false ? 'false(还原失败)' : ($obj->is_admin ? 'admin' : '普通用户')), "\n";
if ($obj && $obj->is_admin === true) {
    echo "[admin 验证通过] ", file_get_contents(__DIR__ . '/flag_l7.txt'), "\n";
}
