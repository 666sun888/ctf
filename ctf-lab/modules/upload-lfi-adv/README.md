# 模块：upload-lfi-adv（上传/包含 进阶，第 5b 课）

对应速查：`course/文件上传与包含速查.md`（进阶篇）
端口：8086（allow_url_include=Off——data:// 那招在这全灭，逼你走新路线）

## 题目列表

| 路径 | 题目 | 考点 |
|------|------|------|
| /up6/ | 黑名单 v4 | 双写绕过（str_replace 删一次） |
| /fi5/ | 导航 v5 | 日志包含（UA 投毒） |
| /up5/ | 黑名单 v5 | .htaccess 接管目录解析 |
| /up7/ | 黑名单 v6 | 竞争条件（先保存后检查） |
| /fi6/ | 导航 v6 | Session 包含 |
| /fi7/ | 导航 v7 | pearcmd 写文件 RCE |
| /up8/ | 白名单 v7 | Apache 解析漏洞（shell.php.jpg） |
| /fi8/ | 导航 v8（毕业考） | 拼接包含 + 日志投毒 + 后缀吸收 三合一 |
