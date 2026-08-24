# 模块：sql-injection（SQL 注入）

对应讲义：`course/lesson-03-SQL注入.md`（含 6.1 长数据四件套）+ `course/lesson-03b-SQL注入进阶.md`
难度阶梯：L1 基础（si1~10）→ L2 变种（si2b/3b/5b/7b）→ L3 综合（sifinal）
端口：8083（PHP 页面）；数据库 mysql:5.7 为内部服务，不对外

## 题目列表

| 路径 | 题目 | 考点 |
|------|------|------|
| /si1/ | 万能密码 | 字符串注入 + 注释符 |
| /si2/ | UNION 注入 | 数字注入 + UNION 提取数据 |
| /si3/ | 报错注入 | updatexml |
| /si4/ | 拦 union select | 注释当空格 |
| /si5/ | 查得到吗 | 布尔盲注 |
| /si6/ | 快还是慢 | 时间盲注 |
| /si7/ | 堆叠注入 | 多语句执行 |
| /si8/ | 读文件 | LOAD_FILE |
| /si9/ | 宽字节 | GBK 吃转义反斜杠 |
| /si10/ | 超长 flag | 报错注入 + substr 分段 |
| /si2b/ | UNION 变种 | 大小写绕过 + order by 探列数（L2） |
| /si3b/ | 报错变种 | 禁 updatexml + 超长 flag（L2） |
| /si5b/ | 盲注变种 | 布尔盲注 + 拦空格（L2） |
| /si7b/ | 堆叠变种 | UPDATE 业务联动（L2） |
| /sifinal/ | 毕业考 | 综合全链路（L3） |

## 结构（v2：每道题独立数据库）

- `db/init.sql` + `db/init-flag.sh` —— 数据库初始化
  - `ctf` 公共库：si1 / si8 / si9
  - `ctf2/3/4/7/10`：每道题独立库（products + secret_table，**表名列名都不提示**，必须用 information_schema / show tables 自己找）
  - `ctf5/6`：盲注题（products + flag5/6_table，表名在提示里给出——盲注枚举表名是折磨，不考这个）
- `tools/` —— 盲注解题脚本（blind_boolean.py / blind_time.py）
- `src/config.php` —— 公共数据库连接
- `src/si1~si4/` —— 四道题

## 做题前必读

`src/config.php` 和四道题的源码；再看一眼 `db/init.sql`（知道表结构比盲猜快得多）。