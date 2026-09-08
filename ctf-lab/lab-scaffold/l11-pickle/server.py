import base64
import pickle
from http.server import BaseHTTPRequestHandler, HTTPServer
from urllib.parse import parse_qs

# ============================================
# L11 挑战：TodoLite · 会话恢复功能
# 故事：本站把用户会话序列化成 pickle 存库，登录时取回来恢复。
# "恢复" = pickle.loads —— 学过 pickle 危险性的你，知道这等于什么。
# 通关条件：把 flag_l11.txt（服务器运行目录）的内容拿到你眼前
# ============================================

PAGE = """<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>TodoLite · 会话恢复</title></head>
<body style="font-family: sans-serif; max-width: 760px; margin: 40px auto;">
<h1>✅ TodoLite（L11 挑战）</h1>
{body}
</body>
</html>"""

SOURCE = """<h3>源码（核心三行，全透明）：</h3>
<pre style="background:#f6f6f6; padding:10px;">import base64, pickle
d = 表单里的 d 参数                  # ← 你控制的 base64 串
obj = pickle.loads(base64.b64decode(d))   # ← 反序列化点：Python 世界
print("会话对象：", repr(obj))            # ← 恢复结果原样回显给你</pre>"""

INTRO = """<p>本站是 Python 写的待办应用：你的会话被序列化成 <strong>pickle</strong> 存库，
"恢复会话"就是把它 loads 回来。</p>
<h3>情报：</h3>
<ul>
<li>提交口收 base64 的 pickle 数据（表单 d 参数，POST）。</li>
<li>恢复出来的对象会 <strong>repr 回显在页面上</strong>。</li>
<li>靶机是 Windows；旗在服务器运行目录的 <code>flag_l11.txt</code>。</li>
<li>弹药得用 Python 造（你机器上有 3.12）——工坊在靶机文件 build_pickle.py。</li>
</ul>
<form method="post">
<textarea name="d" rows="4" cols="80" placeholder="base64 的 pickle 弹药贴进来"></textarea><br>
<button type="submit">恢复会话</button>
</form>"""


class Handler(BaseHTTPRequestHandler):
    def _page(self, body):
        data = PAGE.format(body=body).encode('utf-8')
        self.send_response(200)
        self.send_header('Content-Type', 'text/html; charset=utf-8')
        self.send_header('Content-Length', str(len(data)))
        self.end_headers()
        self.wfile.write(data)

    def do_GET(self):
        self._page(INTRO + SOURCE)

    def do_POST(self):
        length = int(self.headers.get('Content-Length', 0))
        body = self.rfile.read(length).decode('utf-8', 'replace')
        d = parse_qs(body).get('d', [''])[0]
        if not d:
            self._page('<p>没收到数据。</p>' + SOURCE)
            return
        try:
            obj = pickle.loads(base64.b64decode(d))
            result = '<p>✅ 会话恢复成功。会话对象：<code>%s</code></p>' % repr(obj)
        except Exception as e:
            result = '<p>❌ 恢复失败：<code>%s: %s</code></p>' % (type(e).__name__, e)
        self._page(result + SOURCE)

    def log_message(self, *args):
        pass


if __name__ == '__main__':
    HTTPServer(('127.0.0.1', 8096), Handler).serve_forever()
