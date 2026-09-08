<?php
// ============================================
// L9 挑战：无类可用的反序列化点 + 一台只吃 SOAP 的内网机器
// 通关条件：让 unserialize 出来的对象在 health_check() 调用处开火，
//           摸到内网 127.0.0.1:8094/flag_l9.php，旗会以异常消息的形式回来
// ============================================
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES); }
$d = isset($_REQUEST['d']) ? $_REQUEST['d'] : '';
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>L9 挑战 · 体检中心</title></head>
<body style="font-family: sans-serif; max-width: 760px; margin: 40px auto;">
<h1>🏥 万物体检中心（L9 挑战）</h1>

<?php if ($d === ''): ?>
<p>本站业务：你提交一个序列化对象，我们对它做<strong>健康检查</strong>（调用它的 <code>health_check()</code> 方法）。</p>

<h3>源码（全透明，随便审计）：</h3>
<pre style="background:#f6f6f6; padding:10px;">&lt;?php
$d = $_REQUEST['d'];                 // ← 你控制的串进来了
$obj = @unserialize($d);
if (!is_object($obj)) {
    die('反序列化失败或不是对象');
}
$result = $obj-&gt;health_check();      // ← 无论造出什么类，都会被调这个方法
var_dump($result);                   //    （本站没有任何类定义过 health_check）</pre>

<h3>情报：</h3>
<ul>
<li>本站<strong>一个自定义类都没有</strong>（这句话就是最大的提示）。</li>
<li>运维在内网放了一台机器：<code>http://127.0.0.1:8094/</code>，上面有个秘密接口 <code>/flag_l9.php</code>——<strong>只吃 SOAP 调用</strong>（POST + text/xml），浏览器直连会被 403。真实场景里那台机器你根本路由不到，只有"服务器自己发的请求"摸得到它。</li>
<li>旗在那台机器手里。体检中心的这个方法调用，是你唯一能借的手。</li>
</ul>

<h3>提交口（GET ?d= 或 POST 表单都收）：</h3>
<form method="post">
<textarea name="d" rows="4" cols="80" placeholder="把序列化弹药贴进来">O:8:"stdClass":0:{}</textarea><br>
<button type="submit">送进体检</button>
</form>
<p style="color:#888">提示：弹药得先造出来。工坊在靶机文件 <code>build_soap.php</code>（跟 L8 的 build_phar.php 一个用法）。stdClass 送进去只会哑火——它一个魔术方法都没有。</p>

<?php else:
    // ---------- 引爆区 ----------
    echo "<h3>体检结果：</h3>";
    $obj = @unserialize($d);
    if ($obj === false || !is_object($obj)) {
        echo "<pre style='background:#f6f6f6; padding:10px;'>反序列化失败或不是对象（串不合法/类不存在/被拒收名单拦下）\n提交内容开头：", h(substr($d, 0, 60)), "</pre>";
    } else {
        echo "<p>造出了 <code>", h(get_class($obj)), "</code> 类型的对象，开始健康检查……</p>";
        echo "<pre style='background:#f6f6f6; padding:10px;'>";
        try {
            $result = $obj->health_check();
            echo "health_check() 返回：\n";
            var_dump($result);
        } catch (Throwable $t) {
            echo "体检过程中抛出了异常（", h(get_class($t)), "）：\n";
            echo h($t->getMessage()), "\n";
        }
        echo "</pre>";
    }
endif;
?>
<hr>
<p style="color:#888">demo（教材实验台）在 <a href="/demo.php">/demo.php</a> · 武器工坊 build_soap.php 在靶机目录里</p>
</body>
</html>
