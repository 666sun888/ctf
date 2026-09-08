<?php
// L7 教学演示靶②:字符串逃逸(变短方向)
// 净化规则反过来:把 ** 收缩成 *(两个星当一个)
class Profile {
    public $name;
    public $role = 'guest';
}
$p = new Profile;
$p->name = isset($_GET['name']) ? $_GET['name'] : 'visitor';
$ser = str_replace('**', '*', serialize($p));   // 内容变短,长度声明还挂着旧值
echo "存档串: ", $ser, "\n";
$obj = unserialize($ser);
echo "还原结果: ", ($obj === false ? 'false(还原失败)' : $obj->role), "\n";
