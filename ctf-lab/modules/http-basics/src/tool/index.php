<?php
// ============================================
// 实验场：观察服务器如何解析你的请求
// 用法：往这里发任意 GET/POST 请求，看解析结果
// ============================================
function show($title, $arr) {
    echo "<p><b>" . htmlspecialchars($title) . "</b><br><code>" . htmlspecialchars(json_encode($arr, JSON_UNESCAPED_UNICODE)) . "</code></p>";
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>实验场</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🔬 实验场</h1>
<p>把你构造的请求发到这里，看看服务器"眼里"的你长什么样：</p>
<?php
show("请求方法 (REQUEST_METHOD)", $_SERVER['REQUEST_METHOD']);
show("GET 参数 (\$_GET)", $_GET);
show("POST 参数 (\$_POST)", $_POST);
show("Cookie (\$_COOKIE)", $_COOKIE);
show("User-Agent", $_SERVER['HTTP_USER_AGENT'] ?? '');
show("Referer", $_SERVER['HTTP_REFERER'] ?? '');
?>
<p>试试：<code>curl.exe --data-urlencode "a=1&b=2" http://localhost:8080/tool/</code></p>
</body>
</html>
