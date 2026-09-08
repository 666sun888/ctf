<?php
// 实验：另外两件原生类武器的序列化形态 + unserialize 后行为
echo "=== PHP 自带多少个类 ===\n";
$classes = get_declared_classes();
echo count($classes), " 个。抽样：", implode(', ', array_slice($classes, 0, 8)), " ...\n\n";

echo "=== 武器一：Error（__toString 触发）===\n";
$e = new Error("<script>alert(1)</script>", 42);
$s = serialize($e);
echo "形态：", addcslashes($s, "\0"), "\n";
$e2 = unserialize($s);
echo "echo 它 => ", $e2, "\n\n";

echo "=== 武器二：GlobIterator（列目录）===\n";
$g = new GlobIterator('glob://*');
$s2 = serialize($g);
echo "形态：", addcslashes($s2, "\0"), "\n";
$g2 = unserialize($s2);
foreach ($g2 as $f) { echo "  ", $f, "\n"; }
echo "\n";

echo "=== 武器三：SplFileObject（读文件）===\n";
$fo = new SplFileObject(__DIR__ . '/l9-probe.php');
$s3 = serialize($fo);
echo "形态前 80 字节：", substr(addcslashes($s3, "\0"), 0, 120), "\n";
$fo2 = @unserialize($s3);
if ($fo2 instanceof SplFileObject) {
    $fo2->setFlags(SplFileObject::READ_CSV);
    $n = 0;
    foreach ($fo2 as $line) { if (++$n > 3) break; echo "  行: ", substr((string)$line, 0, 40), "\n"; }
} else {
    echo "  unserialize 失败/非对象\n";
}
