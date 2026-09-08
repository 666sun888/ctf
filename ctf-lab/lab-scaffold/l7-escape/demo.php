<?php
// L7 教学演示靶①:字符串逃逸(变长方向)
// 情景:资料序列化存档,存档前经过"净化"——把 * 替换成 **(防刷屏,业务觉得无害)
class Profile {
    public $name;
    public $role = 'guest';
}
$p = new Profile;
$p->name = isset($_GET['name']) ? $_GET['name'] : 'visitor';
$ser = str_replace('*', '**', serialize($p));   // ← 先序列化、后过滤
echo "存档串: ", $ser, "\n";
$obj = unserialize($ser);
echo "还原结果: ", ($obj === false ? 'false(还原失败)' : $obj->role), "\n";
if ($obj && $obj->role === 'admin') {
    echo "[逃逸成功——演示靶无旗,挑战在 account.php]\n";
}
