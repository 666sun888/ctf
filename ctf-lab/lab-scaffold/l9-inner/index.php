<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>内网服务器（模拟）</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>🖥️ 内网服务器（模拟）</h1>
<p>你居然能直接看到这台机器的首页——因为本地靶场里所有机器都在 127.0.0.1。</p>
<p>真实场景里，这台服务器只在内网可达，你在家/学校的浏览器<strong>根本路由不到它</strong>。
只有<strong>靶机自己发出去的请求</strong>（SSRF）才能摸到它。</p>
<p>上面那个秘密接口是 <code>/flag_l9.php</code>——但它只吃 SOAP 调用，你用浏览器点它是白搭。</p>
<p style="color:#888">（老规矩：直连伪造头作弊没意义——过门要能复述机制，hit 日志也记着你是用什么 UA 打的。）</p>
</body>
</html>
