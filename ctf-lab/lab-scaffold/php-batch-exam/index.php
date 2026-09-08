<?php
# 变体版:GET 只收 key,旗在根目录,名字自己找
echo "GET 只收 key 欧,flag 藏在根目录,文件名自己找<br>";
highlight_file(__FILE__);
if(isset($_GET['key'])){
    if (!preg_match('/session_id\(|readfile\(/i', $_GET['key']))
     {
        if(';' === preg_replace('/[a-z,_]+\((?R)?\)/', NULL, $_GET['key'])) {
                @eval($_GET['key']);
            }
    }
    else{
        die("不让用session欧，readfile也不行");
    }
}
?>
