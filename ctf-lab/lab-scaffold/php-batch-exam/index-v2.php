<?php
# 变体版2:GET 只收 cmd,旗在附近某一层目录,自己爬楼找
echo "GET 只收 cmd 欧,flag 在附近的某一层目录里,自己爬楼找<br>";
highlight_file(__FILE__);
if(isset($_GET['cmd'])){
    if (!preg_match('/session_id\(|readfile\(/i', $_GET['cmd']))
     {
        if(';' === preg_replace('/[a-z,_]+\((?R)?\)/', NULL, $_GET['cmd'])) {
                @eval($_GET['cmd']);
            }
    }
    else{
        die("不让用session欧，readfile也不行");
    }
}
?>
