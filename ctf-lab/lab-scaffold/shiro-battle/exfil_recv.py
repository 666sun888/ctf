# 外带收货器：打印收到的 POST 体和 GET 路径（打 Shiro 靶时挂着它）
from http.server import BaseHTTPRequestHandler, HTTPServer

class H(BaseHTTPRequestHandler):
    def do_POST(self):
        body = self.rfile.read(int(self.headers.get('Content-Length', 0)))
        print('外带到货:', body.decode(errors='replace'), flush=True)
        self.send_response(200)
        self.end_headers()
        self.wfile.write(b'ok')

    def do_GET(self):
        print('GET 路径:', self.path, flush=True)
        self.send_response(200)
        self.end_headers()

    def log_message(self, *a):
        pass

if __name__ == '__main__':
    # 2026-09-08 修正：默认只监听本机（原 0.0.0.0 会暴露在局域网）。
    # 容器回连宿主机的场景（host.docker.internal）仍可用 127.0.0.1 收到。
    print('收货器监听 127.0.0.1:9999 ...（Ctrl+C 停）', flush=True)
    HTTPServer(('127.0.0.1', 9999), H).serve_forever()
