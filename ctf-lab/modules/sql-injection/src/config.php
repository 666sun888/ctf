<?php
// 公共数据库连接：每个题目页面前先定义 $DB 选择自己的库（默认 ctf）
// 用法：$DB = 'ctf2'; require_once __DIR__ . '/../config.php';
if (!isset($DB)) { $DB = 'ctf'; }
mysqli_report(MYSQLI_REPORT_OFF); // 关闭异常模式：查询失败返回 false，由页面用 mysqli_error 显示
for ($i = 0; $i < 30; $i++) {
    $conn = @mysqli_connect('sql-injection-db', 'ctf', 'ctfpass', $DB);
    if ($conn) break;
    sleep(1); // 等数据库就绪
}
if (!$conn) {
    die("数据库连接失败: " . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');
