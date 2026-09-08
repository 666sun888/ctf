import com.sun.net.httpserver.HttpExchange;
import com.sun.net.httpserver.HttpServer;

import java.io.ByteArrayInputStream;
import java.io.IOException;
import java.io.ObjectInputStream;
import java.net.InetSocketAddress;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.util.Base64;

// ============================================
// L12 挑战：JavaConfigKeeper 老系统 · 配置导入
// 故事：上古 Java 系统，"配置导入"功能收 base64 的 Java 序列化对象，
//       readObject 恢复它。系统里挂着老版本 commons-collections（vendor 依赖）。
// 通关条件：把 flag_l12.txt 的内容拿到你眼前（靶机无回显——想想旗怎么回来）
// ============================================
public class L12Server {

    static final Path WEBROOT = Paths.get("webroot");

    public static void main(String[] args) throws Exception {
        HttpServer server = HttpServer.create(new InetSocketAddress("127.0.0.1", 8097), 0);
        server.createContext("/", L12Server::handle);
        server.setExecutor(null);
        server.start();
        System.out.println("L12 up on http://127.0.0.1:8097/");
    }

    static void handle(HttpExchange ex) throws IOException {
        String method = ex.getRequestMethod();
        String path = ex.getRequestURI().getPath();
        if ("POST".equals(method)) { doImport(ex); return; }
        if ("GET".equals(method)) {
            if ("/".equals(path)) { send(ex, page(), "text/html; charset=utf-8"); return; }
            serveFile(ex, path);
            return;
        }
        send(ex, "<p>405</p>", "text/html; charset=utf-8");
    }

    // ---- 反序列化点 ----
    static void doImport(HttpExchange ex) throws IOException {
        String body = new String(ex.getRequestBody().readAllBytes(), StandardCharsets.UTF_8);
        String d = formParam(body, "d");
        String result;
        if (d.isEmpty()) {
            result = "<p>没收到数据。</p>";
        } else {
            try {
                byte[] data = Base64.getDecoder().decode(d);
                ObjectInputStream ois = new ObjectInputStream(new ByteArrayInputStream(data));
                Object obj = ois.readObject();   // ← 反序列化点：链在此引爆
                ois.close();
                result = "<p>✅ 配置对象已导入：<code>" + esc(obj.getClass().getName())
                        + "</code>（老系统只回显类型名，不回显任何执行结果）</p>";
            } catch (Throwable t) {
                String msg = t.getMessage() == null ? "" : t.getMessage();
                result = "<p>❌ 导入失败：<code>" + esc(t.getClass().getName()) + ": " + esc(msg) + "</code></p>";
            }
        }
        send(ex, result + SOURCE, "text/html; charset=utf-8");
    }

    // ---- webroot：落盘区（可写、HTTP 可取回） ----
    static void serveFile(HttpExchange ex, String path) throws IOException {
        Path target = WEBROOT.resolve(path.substring(1)).normalize();
        if (!target.startsWith(WEBROOT.normalize()) || !Files.isRegularFile(target)) {
            send(ex, "<p>404：webroot 里没有这个文件——东西得先进去才有得读。</p>", "text/html; charset=utf-8");
            return;
        }
        byte[] content = Files.readAllBytes(target);
        send(ex, null, "text/plain; charset=utf-8", content);
    }

    // ---- 页面 ----
    static String page() {
        return "<!DOCTYPE html><html lang=\"zh\"><head><meta charset=\"utf-8\">"
                + "<title>JavaConfigKeeper · 配置导入</title></head>"
                + "<body style='font-family:sans-serif;max-width:760px;margin:40px auto'>"
                + "<h1>☕ JavaConfigKeeper（L12 挑战）</h1>"
                + "<p>上古 Java 系统。\"配置导入\"收 base64 的 <strong>Java 序列化对象</strong>，"
                + "<code>readObject</code> 恢复之。系统 classpath 上挂着老牌工具库 "
                + "<strong>commons-collections 3.1</strong>（跟 Monolog 一样，是它的\"可借的手\"）。</p>"
                + SOURCE_BODY
                + "<h3>情报：</h3><ul>"
                + "<li>提交口收 base64（表单 d 参数，POST）——base64 解开应以 <code>rO0AB</code> 开头"
                + "（Java 序列化的指纹：字节 <code>AC ED 00 05</code>）。</li>"
                + "<li><strong>无回显</strong>：系统只告诉你导入了什么类型，不回显任何执行结果。</li>"
                + "<li>落盘区 <code>webroot/</code>：<strong>可写</strong>，且 HTTP 可取回（GET /文件名）。</li>"
                + "<li>靶机 Windows；<code>Runtime.exec</code> 直接 spawn 进程<strong>不经 shell 解释</strong>"
                + "——cmd 内建命令（copy/type/dir）要 <code>cmd /c</code> 包一层。</li>"
                + "<li>旗在服务器运行目录的 <code>flag_l12.txt</code>。</li>"
                + "</ul>"
                + "<form method='post'>"
                + "<textarea name='d' rows='4' cols='80' placeholder='base64 的 Java 序列化弹药'></textarea><br>"
                + "<button type='submit'>导入配置</button></form>"
                + "</body></html>";
    }

    static final String SOURCE_BODY =
            "<h3>核心源码（全透明）：</h3><pre style='background:#f6f6f6;padding:10px;'>"
            + "byte[] data = Base64.getDecoder().decode(d);              // ← 你控制的 base64\n"
            + "ObjectInputStream ois = new ObjectInputStream(new ByteArrayInputStream(data));\n"
            + "Object obj = ois.readObject();                            // ← 反序列化点：链在此引爆\n"
            + "// 老系统只回显类型名，不回显执行结果</pre>";

    static final String SOURCE =
            "<hr>" + SOURCE_BODY + "<p style='color:#888'>靶场 8097 · 工厂：D:\\deepseek\\ysoserial-all.jar</p>";

    // ---- 小工具 ----
    static String formParam(String body, String name) {
        for (String pair : body.split("&")) {
            int i = pair.indexOf('=');
            if (i > 0 && pair.substring(0, i).equals(name)) {
                return java.net.URLDecoder.decode(pair.substring(i + 1), StandardCharsets.UTF_8);
            }
        }
        return "";
    }

    static String esc(String s) {
        return s.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;");
    }

    static void send(HttpExchange ex, String html, String type) throws IOException {
        send(ex, html, type, html.getBytes(StandardCharsets.UTF_8));
    }

    static void send(HttpExchange ex, String unused, String type, byte[] content) throws IOException {
        ex.getResponseHeaders().set("Content-Type", type);
        ex.sendResponseHeaders(200, content.length);
        ex.getResponseBody().write(content);
        ex.close();
    }
}
