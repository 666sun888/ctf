<?php require 'common.php'; ?>
<!DOCTYPE html><html lang="zh"><head><meta charset="utf-8"><title>SQL 注入练武场</title></head>
<body style="font-family:sans-serif;max-width:640px;margin:40px auto;">
<h1>🏋️ SQL 注入练武场</h1>
<p>七个场景，每种注入语境一个入口：</p>
<ol>
<li><a href="get.php?id=1">get.php</a> —— GET 数字型 SELECT（UNION/报错/布尔/时间 全适用）</li>
<li><a href="like.php?kw=a">like.php</a> —— LIKE 搜索型（% 通配场景）</li>
<li><a href="post.php">post.php</a> —— POST 登录（字符串闭合场景）</li>
<li><a href="insert.php">insert.php</a> —— INSERT 留言板（INSERT 场景报错注入）</li>
<li><a href="header.php">header.php</a> —— UA 入库（HTTP 头注入）</li>
<li><a href="reg.php">reg.php</a> + <a href="pass.php?id=1">pass.php</a> —— 注册/改密 = 二次注入对</li>
<li><a href="noinfo.php?id=1">noinfo.php</a> —— information_schema 被禁（无列名注入）</li>
</ol>
</body></html>
