<?php
$conn = @mysqli_connect('sql-injection-db', 'ctf', 'ctfpass', 'ctf');
mysqli_report(MYSQLI_REPORT_OFF);
if (!$conn) { die('数据库连接失败'); }
mysqli_set_charset($conn, 'utf8mb4');
function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES); }
function footer($sql, $err = null) {
    echo '<p style="color:#888">执行的 SQL：<code>' . h($sql) . '</code></p>';
    // $err 不传时读当前错误状态;若页面在 footer 之前还跑过别的查询(SELECT 等),
    // 错误已被清零——INSERT 类页面必须把查询当场的错误显式传进来
    $e = ($err === null) ? mysqli_error($GLOBALS['conn']) : $err;
    if ($e) {
        echo '<p style="color:#c00">SQL 错误：<code>' . h($e) . '</code></p>';
    }
}
