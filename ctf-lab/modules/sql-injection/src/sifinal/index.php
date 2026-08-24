<?php
$DB = 'ctfF';
require_once __DIR__ . '/../config.php';
// ============================================
// 考点：综合毕业考
//  - 注入点要自己找（读源码！参数名不是 id）
//  - 表名、列名未知（提示：表名 5 字符 f 开头；数据列 4 字符 d 开头）
//  - 过滤：空白 / union / sleep / #（大小写不敏感）
//  - 报错被吞（页面只有 有/无 两种反应）→ 布尔盲注
//  - flag 44 字符 → 脚本化提取
// ============================================
$msg = '';
if (isset($_GET['ticket'])) {
    $ticket = $_GET['ticket'];
    if (preg_match('/\s|union|sleep|#/i', $ticket)) { // 不拦 select：MySQL 不允许注释插在关键字内部，拦了就没法查了
        $msg = "❌ 输入不合法";
    } else {
        $sql = "SELECT title, status FROM tickets WHERE id=" . $ticket;
        $result = mysqli_query($conn, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $msg = "✅ 工单状态：处理中（管理员正在跟进）";
        } else {
            $msg = "❌ 无此工单";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>工单查询系统</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🎫 工单查询系统（sifinal 毕业考）</h1>
<form method="get">
  工单号：<input type="text" name="ticket" value="1">
  <button type="submit">查询</button>
</form>
<p><?= $msg ?: '&nbsp;' ?></p>
<hr>
<p style="color:#888">提示清单：</p>
<ul style="color:#888">
  <li>注入点在哪里？先读源码（src/sifinal/index.php）</li>
  <li>flag 表：5 个字符，f 开头；数据列：4 个字符，d 开头；flag 共 43 个字符</li>
  <li>过滤：空白 / union / sleep / #（大小写不敏感）</li>
  <li>报错被吞了，页面只有"有/无"两种反应——盲注</li>
  <li>45 个字符用手猜太慢了——写个脚本（布尔盲注脚本改改？）</li>
</ul>
</body>
</html>