<?php
// ============================================
// 考点：无分隔符注入（命令替换 $( ) 与反引号）
// 速查：course/命令注入速查.md 第 3 节（绕过速查：命令替换）
// 提示：flag 在 /fourth.txt；; | & 和换行都被拦了，但执行命令不一定要分隔符
// ============================================
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if (preg_match('/[;|&\n]/', $msg)) {
        die("❌ 黑名单：不允许 ; | & 换行");
    }
    // 漏洞点：输入拼进 echo 命令
    system("echo 你输入的是: " . $msg);
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>ci4 回显器</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>📣 回显器</h1>
<form method="get">
  输入内容：<input type="text" name="msg" value="hello" size="30">
  <button type="submit">回显</button>
</form>
<p>提示：flag 在 /fourth.txt；; | & 和换行都被拦了，但执行命令不一定要分隔符</p>
</body>
</html>
