# 📊 学习进度表

## 第 0 课：环境与工具
- [x] 读完讲义 lesson-00
- [x] 靶场 docker compose up 成功启动
- [x] curl.exe 常用用法练熟（-X / -d / -H / -b / -c / -e / --data-urlencode）
- [x] Burp Suite 安装并完成一次抓包（可选但推荐）

## 第 1 课：HTTP 基础
- [x] 读完讲义 lesson-01，自测题全部先写答案再看答案
- [x] 靶场 ch1 只许 POST —— 这题考：___用-X指定提交方法___
- [x] 靶场 ch2 改头换面 —— 这题考：___-H自制请求头___
- [x] 靶场 ch3 Cookie 的秘密 —— 这题考：___有两种方法，第一个就是-v看全部，再用-b提交cookie___
- [x] 靶场 ch4 神秘编码 —— 这题考：___url编码___
- [x] 对照 writeups/http-basics.md 复盘

## 第 2 课：命令注入
- [x] 读完讲义 lesson-02，自测题先写答案再看答案
- [x] 靶场 ci1 直接注入 —— 这题考：___；后面加命令___
- [x] 靶场 ci2 拦分号 —— 这题考：___| && %0a 都暂时可以代替；的作用___
- [x] 靶场 ci3 拦空格和关键字 —— 这题考：___关键字绕过和空格的代替___
- [x] 靶场 ci4 拦所有分隔符 —— 这题考：___用echo和`代替分隔符___
- [x] 对照 writeups/cmd-injection.md 复盘

## 第 3 课：SQL 注入（基础）
- [ ] 读完讲义 lesson-03，自测题先写答案再看答案
- [x] 靶场 si1 万能密码 —— 这题考：字符串注入闭合引号+注释符
- [x] 靶场 si2 UNION 注入 —— 这题考：information_schema 枚举表/列 + UNION 取数
- [x] 靶场 si3 报错注入 —— 这题考：报错信道 + 枚举表名列名
- [x] 靶场 si4 拦 union select —— 这题考：用 /**/ 代替空格（自己独立完成，2026-08-24）
- [ ] 对照 writeups/sql-injection.md 复盘

## 第 3b 课：SQL 注入（进阶）
- [ ] 读完讲义 lesson-03b，自测题先写答案再看答案
- [ ] 靶场 si5 布尔盲注 —— 这题考：______
- [ ] 靶场 si6 时间盲注 —— 这题考：______
- [ ] 靶场 si7 堆叠注入 —— 这题考：______
- [ ] 靶场 si8 读文件 —— 这题考：______
- [ ] 靶场 si9 宽字节 —— 这题考：______
- [ ] 靶场 si10 超长 flag —— 这题考：______
- [ ] 对照 writeups/sql-injection-adv.md 复盘
## 第 3c 课：SQL 注入难度阶梯（L2 变种 + L3 综合）
- [x] si2b UNION 变种 —— 这题考：正则漏 /i 只拦小写，大小写绕过 + order by 探列数
- [x] si3b 报错变种 —— 这题考：禁 updatexml 换 extractvalue + group_concat 合并多行 + 分段取超长
- [x] si5b 盲注变种 —— 这题考：布尔盲注 + /**/ 代替空格（第一支 exp 就是它！）
- [x] si7b 堆叠变种 —— 这题考：堆叠注入 + UPDATE 改身份（flag{si7b_stacked_update_wins}）
- [x] sifinal 毕业考 —— 这题考：盲注全链（长度→库名→表名→列名→数据）45 字符 flag
- [x] 对照 writeups/sql-injection-variants.md 复盘
- [ ] 用 tools/review_random.py 开始间隔复习（每板块完成后抽 2 题复测）
## 第 4 课：XSS（进行中）
- [x] xs1 反射型（重打通过）—— 这题考：payload 藏在 URL 参数，改参数就中，一次性
- [x] xs2 存储型（重打通过）—— 这题考：留言存服务器，谁访问谁执行，最危险
- [x] xs3 DOM 型（重打通过）—— 这题考：payload 在 # 后不经过服务器，F12 看 JS 拼 innerHTML；已修复缺 decodeURIComponent 的坑
- [x] **三类型毕业考：场景判定 5/5 全对**
- [x] xs4 偷 Cookie（重打通过）—— 这题考：document.cookie + new Image() 无痕外带；坑：URL 里 `+` 变空格要编码 %2B
- [x] xs5 过滤绕过（通过）—— 这题考：换标签(img onerror)+换函数(confirm)+双写嵌套(单次过滤)；alert 被删就用 confirm
- [x] xs6 存储型打管理员（综合通过）—— 这题考：存储型留言 + new Image 偷管理员 Cookie 全链（业务联动）
- [x] xs6 存储型打管理员（综合通过）—— 这题考：存储型留言 + new Image 偷管理员 Cookie 全链（业务联动）
- [x] xs7 裸子串过滤（毕业考通过）—— 这题考：重叠双写 <scscriptript> 还原 script / 换事件 onfocus+autofocus；裸子串删除下嵌套失效但重叠有效
- [x] **XSS 板块全通（重打版）**
- [ ] 对照 writeups/xss.md 复盘
- [ ] XSS 错题本闭卷复测（过几天）
- [ ] L4：BUUCTF 入门真题

## 第 5 课：文件上传 / 文件包含（Phase 2-3，2026-08-20 开课）
- [ ] 读速查 course/文件上传与包含速查.md（第 0 节流程 + 第 2 节绕过表）
- [x] 靶场 up1 无过滤上传 —— 这题考：webshell 三条件(可控名/可访问/可执行)+上传触发两步；坑:$_GET 手打翻车、curl 路径
- [x] 靶场 up2 黑名单 —— 这题考：in_array 大小写敏感 → .PhP 绕过(黑名单=有限名单拦无限集合)
- [x] 靶场 up3 黑名单+MIME —— 这题考：Content-Type 是客户端自我声明可伪造(;type=image/jpeg)；octet-stream=不认识的默认值
- [x] 靶场 fi1 任意包含 —— 这题考：include 双模式(读=原样输出/执行=当代码跑)+/etc/passwd 试金石；坑:马写成 $_POST 拿 GET 触发、路径要复制响应里的真实文件名
- [x] 靶场 fi2 前后缀拼接 —— 这题考：../ 消前缀 + 后缀吸收(.php 结尾的目标文件吞掉拼接的后缀)；路径:pages→fi2→html→www→var→/(5 层)
- [x] 靶场 fi3 filter 读源码 —— 这题考：php://filter 把执行降级为读取(base64 后无 PHP 标记)；执行模式吞注释所以读不到源码
- [x] 靶场 fi4 伪协议 RCE —— 这题考：data:// URL 造代码 + php://input POST 体当代码；前提 allow_url_include=On(默认关)；坑:PS 传带引号 POST 体用 --data-binary @文件
- [x] 靶场 up4 图片马毕业考 —— 这题考：白名单+getimagesize 全锁 → GIF89a 图片马过内容校验 + 查看器 include 执行；两个中危组合成 RCE
- [ ] 对照 writeups/upload-lfi.md 复盘

## 第 5b 课：上传/包含 进阶（Phase 2-3 续，2026-08-21 开课，端口 8086）
- [ ] 读速查 course/文件上传与包含速查.md（进阶篇 12-15 节）
- [x] 靶场 up6 双写绕过 —— 这题考：str_replace 只删一遍 → 删一个嵌两个(pphphp→php)
- [x] 靶场 fi5 日志包含 —— 这题考：UA 投毒(-A)+包含日志;毒行被执行所以页面上看不到字串(数据进日志、include 时才变代码)
- [x] 靶场 up5 .htaccess 接管 —— 这题考：配置文件不在黑名单→SetHandler/AddType 抢解析规则制定权;坑:GIF89a 误混进 .htaccess→500、$_POST[x] 裸键 PHP8 致命错、@吞错误变"空响应"、Defender 隔离本地 eval 马
- [x] 靶场 up7 竞争条件 —— 这题考：先保存后检查的时间窗;"已删除"≠"没存在过";治本=校验通过才落盘(顺序反过来)
- [x] 靶场 fi6 Session 包含 —— 这题考：/tmp/sess_ID 内容可控+-c存/-b带同Cookie;坑:<ID>占位符忘了替换原样发出去
- [x] 靶场 fi7 pearcmd RCE —— 这题考：找不到可控文件就造一个;config-create 嵌代码;三坑:单引号/-g 禁通配/argv 不解码 payload 裸传
- [x] 靶场 up8 Apache 解析漏洞 —— 这题考：白名单+内容校验无洞,输给 Apache AddHandler 从右往左解析;三系统单独对组合错;GIF89a 靠 WriteAllText 防 BOM
- [ ] 靶场 fi8 毕业考三合一 —— 这题考：______
- [ ] 对照 writeups/upload-lfi-adv.md 复盘

## 📅 待办（更新）
- [ ] 启动复习机制：tools/review_random.py 抽 2 题复测
- [ ] 注册 BUUCTF（buuoj.cn），从入门真题清单开始（EasySQL / LoveSQL / Exec）
- [ ] XSS 收尾：writeup 复盘 + 错题本复测 + L4 BUUCTF
- [x] si4 补完（2026-08-24 关账，flag 已拿到）
- [ ] 第 5 课完成后：writeup 复盘 + 隔天复测（老规矩）
- [ ] 第 5 课之后的下一板块（原规划 Phase 2-5）：CSRF