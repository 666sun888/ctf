<?php
$l1 = [
  ["si1", "万能密码", "字符串注入 + 注释符"],
  ["si2", "UNION 注入", "枚举流程（表→列→数据）"],
  ["si3", "报错注入", "报错信道 + 枚举"],
  ["si4", "拦 union select", "注释当空格"],
  ["si5", "查得到吗", "布尔盲注"],
  ["si6", "快还是慢", "时间盲注"],
  ["si7", "堆叠注入", "多语句执行"],
  ["si8", "读文件", "LOAD_FILE"],
  ["si9", "宽字节", "GBK 吃转义反斜杠"],
  ["si10", "超长 flag", "报错 + substr 分段"],
];
$l2 = [
  ["si2b", "UNION 变种", "拦 union/select + order by 探列数"],
  ["si3b", "报错变种", "禁 updatexml + 超长 flag"],
  ["si5b", "盲注变种", "布尔盲注 + 拦空格"],
  ["si7b", "堆叠变种", "UPDATE 改数据 + 业务联动"],
];
$l3 = [
  ["sifinal", "毕业考", "自寻注入点 + 未知表列 + 多重过滤 + 脚本化盲注"],
];
function render($list) {
  foreach ($list as [$dir, $name, $tag]) {
    echo '<li><a href="/' . $dir . '/">' . htmlspecialchars($name) . '</a> —— ' . htmlspecialchars($tag) . '</li>';
  }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>SQL 注入模块（难度阶梯）</title></head>
<body style="font-family: sans-serif; max-width: 680px; margin: 40px auto;">
<h1>🗄️ SQL 注入模块（难度阶梯）</h1>
<p>做题顺序：L1 基础 → L2 变种 → L3 综合。先读 <code>modules/sql-injection/src/</code> 源码。</p>

<h2>🟢 L1 基础（单一知识点）</h2>
<ul><?php render($l1); ?></ul>

<h2>🟡 L2 变种（同知识点 + 干扰条件）</h2>
<ul><?php render($l2); ?></ul>

<h2>🔴 L3 综合（全链路，毕业考）</h2>
<ul><?php render($l3); ?></ul>
<p style="color:#888">建议：L1 全部完成后挑战 L2；L2 完成后挑战 L3。题解在 writeups/，做完再看。</p>
</body>
</html>
