# -*- coding: utf-8 -*-
import io

PATH = r'D:\deepseek\course\SQL注入速查.md'
with io.open(PATH, encoding='utf-8') as f:
    lines = f.readlines()

# --- 修1:1.2 代码块(定位"id=1||1=1"行,重写该块)---
for i, l in enumerate(lines):
    if 'id=1||1=1' in l and '等价 OR' in l:
        lines[i] = "id=1%7C%7C1%3D1    -> || 等价 OR;URL 写 %7C%7C(| 可裸写,编码更稳)\n"
        lines[i+1] = "id=1%26%261%3D1   -> && 等价 AND;URL 必须写 %26%26\n"
        # 在代码块结束后的空行处插入陷阱注
        j = i + 2
        trap = [
            "\n",
            "??? && 假阳性陷阱(2026-08-29 实证):URL 裸写 && 时,& 是参数分隔符,服务端只收到\n",
            "   id=1,&&1=1 丢失成独立参数。更毒:id=1&&1=1 仍返回原 id=1 的结果--看着像\n",
            "   注入成功,实为原查询结果。鉴别法:换假条件 id=1&&1=2,仍返回数据=payload 丢失;\n",
            "   id=1%26%261%3D2 无结果=注入真生效\n",
        ]
        for k, t in enumerate(trap):
            lines.insert(j + k, t)
        break

with io.open(PATH, 'w', encoding='utf-8') as f:
    f.writelines(lines)

# --- 修2+3:重新读入,按内容定位 ---
with io.open(PATH, encoding='utf-8') as f:
    lines = f.readlines()

for i, l in enumerate(lines):
    if 'and -> &&' in l and 'or ->' in l:
        lines[i] = "and -> &&   (URL 必须 %26%26,裸 && 是假阳性,见 1.2 陷阱)\n"
        lines[i+1] = "or  -> ||   (URL 可裸写 |,编码 %7C%7C 更稳)\n"
    if '浏览器表单提交的值' in l:
        lines[i] = ("+ 号三态(2026-08-29 实证,CMD):\n"
                    "- 浏览器表单:自动把 + 编码 %2B,存字面 +,安全\n"
                    "- curl 裸 -d \"username=admin+x\":+ 被解码成空格,存的是 'admin x'!\n"
                    "- curl --data-urlencode:自动编码,存字面 +,安全\n"
                    "- 结论:curl 发带 + 的 payload 用 --data-urlencode 或手写 %2B\n"
                    "打法与 GET 相同,只是数据走 POST:\n")

with io.open(PATH, 'w', encoding='utf-8') as f:
    f.writelines(lines)

print('all fixes done')
