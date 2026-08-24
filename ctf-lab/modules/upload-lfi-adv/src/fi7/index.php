<?php
// ============================================
// 考点：pearcmd 利用（LFI 神器——让 PEAR 自带的命令行工具替你写文件）
// 提示：服务器 /usr/local/lib/php/pearcmd.php 是 PEAR 包管理器的 CLI 入口
//      register_argc_argv=On 时，URL 查询串会被当成它的命令行参数
//      config-create 子命令能把一个文件的内容写进另一个文件
//      flag 在 /flag_fi7.txt
// ============================================
$page = $_GET['page'] ?? '';
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>fi7 导航 v7</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>📑 站点导航 v7（fi7 无伪协议）</h1>
<p>
  <a href="/fi7/?page=inc/home.php">首页</a> |
  <a href="/fi7/?page=inc/about.php">关于</a>
</p>
<div style="background:#f6f6f6; padding:12px;">
<?php
if ($page !== '') {
    include($page);
}
?>
</div>
<p style="color:#888">提示：伪协议全关、日志/session 你都玩过了。这题考冷门但威力巨大的点：本机装了 PEAR（/usr/local/lib/php/pearcmd.php），且 register_argc_argv=On——include 它 + 在查询串里"传命令行参数"，它的 config-create 功能可以替你把 flag 抄到 /tmp 下。flag 在 /flag_fi7.txt。</p>
</body>
</html>
