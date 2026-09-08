<?php
// 实验：SplFileObject 序列化 + 手搓 GlobIterator 串
echo "=== SplFileObject：serialize 允许吗 ===\n";
$fo = new SplFileObject(__DIR__ . '/l9-probe.php');
$s = serialize($fo);
echo "形态前 100 字节：", substr(addcslashes($s, "\0"), 0, 100), "\n";
$fo2 = @unserialize($s);
var_dump($fo2 instanceof SplFileObject);
if ($fo2 instanceof SplFileObject) {
    $n = 0;
    $fo2->rewind();
    while ($fo2->valid() && $n < 3) {
        echo "  行", $n + 1, ": ", substr($fo2->current(), 0, 40), "\n";
        $fo2->next(); $n++;
    }
}
echo "\n=== 手搓 GlobIterator 串（serialize 被禁，但 unserialize 收不收？）===\n";
// DirectoryIterator 家族属性是 protected：\0*\0pattern 之类，手搓带 \0
$cands = array(
    'O:13:"GlobIterator":2:{s:10:"' . "\0*\0" . 'flags";i:0;s:12:"' . "\0*\0" . 'pattern";s:7:"glob://*";}',
    'O:13:"GlobIterator":2:{s:5:"flags";i:0;s:7:"pattern";s:7:"glob://*";}',
);
foreach ($cands as $i => $c) {
    echo "候选", $i + 1, "：";
    $g = @unserialize($c);
    if ($g === false) { echo "unserialize 拒收\n"; continue; }
    var_dump($g instanceof GlobIterator);
    try {
        $n = 0;
        foreach ($g as $f) { echo "  ", $f, "\n"; if (++$n >= 5) break; }
    } catch (Throwable $t) {
        echo "  迭代报错：", $t->getMessage(), "\n";
    }
}
