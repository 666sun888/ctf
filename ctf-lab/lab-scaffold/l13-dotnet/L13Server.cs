using System;
using System.IO;
using System.Net;
using System.Runtime.Serialization.Formatters.Binary;
using System.Text;

// ============================================
// L13 挑战：DotNetKeeper 老系统 · 配置导入
// 故事：微软系上古系统，"配置导入"收 base64 的 .NET 序列化对象，
//       BinaryFormatter.Deserialize 恢复它。
// 通关条件：把 flag_l13.txt 的内容拿到你眼前（无回显——两段式）
// ============================================
public class L13Server
{
    static string WebRoot = "webroot";

    public static void Main(string[] args)
    {
        HttpListener listener = new HttpListener();
        listener.Prefixes.Add("http://127.0.0.1:8098/");
        listener.Start();
        Console.WriteLine("L13 up on http://127.0.0.1:8098/");
        while (true)
        {
            HttpListenerContext ctx = listener.GetContext();
            try { Handle(ctx); }
            catch (Exception e)
            {
                try { Send(ctx, "<p>500: " + Escape(e.Message) + "</p>", "text/html"); } catch { }
            }
        }
    }

    static void Handle(HttpListenerContext ctx)
    {
        string method = ctx.Request.HttpMethod;
        string path = ctx.Request.Url.AbsolutePath;
        if (method == "POST") { DoImport(ctx); return; }
        if (method == "GET")
        {
            if (path == "/") { Send(ctx, Page(), "text/html; charset=utf-8"); return; }
            ServeFile(ctx, path);
            return;
        }
        Send(ctx, "<p>405</p>", "text/html");
    }

    // ---- 反序列化点 ----
    static void DoImport(HttpListenerContext ctx)
    {
        string body = new StreamReader(ctx.Request.InputStream, Encoding.UTF8).ReadToEnd();
        string d = FormParam(body, "d");
        string result;
        if (d == "")
        {
            result = "<p>没收到数据。</p>";
        }
        else
        {
            try
            {
                byte[] data = Convert.FromBase64String(d);
                MemoryStream ms = new MemoryStream(data);
                BinaryFormatter bf = new BinaryFormatter();
                object obj = bf.Deserialize(ms);   // ← 反序列化点：链在此引爆
                result = "<p>✅ 配置对象已导入：<code>" + Escape(obj.GetType().FullName)
                       + "</code>（老系统只回显类型名，不回显执行结果）</p>";
            }
            catch (Exception e)
            {
                result = "<p>❌ 导入失败：<code>" + Escape(e.GetType().Name) + ": " + Escape(e.Message) + "</code></p>";
            }
        }
        Send(ctx, result + Source(), "text/html; charset=utf-8");
    }

    // ---- webroot：落盘区 ----
    static void ServeFile(HttpListenerContext ctx, string path)
    {
        string rel = path.TrimStart('/');
        string full = Path.GetFullPath(Path.Combine(WebRoot, rel));
        string rootFull = Path.GetFullPath(WebRoot);
        if (!full.StartsWith(rootFull) || !File.Exists(full))
        {
            Send(ctx, "<p>404：webroot 里没有这个文件——东西得先进去才有得读。</p>", "text/html; charset=utf-8");
            return;
        }
        byte[] content = File.ReadAllBytes(full);
        ctx.Response.ContentType = "text/plain; charset=utf-8";
        ctx.Response.ContentLength64 = content.Length;
        ctx.Response.OutputStream.Write(content, 0, content.Length);
        ctx.Response.OutputStream.Close();
    }

    static string Page()
    {
        StringBuilder sb = new StringBuilder();
        sb.Append("<!DOCTYPE html><html lang=\"zh\"><head><meta charset=\"utf-8\">");
        sb.Append("<title>DotNetKeeper · 配置导入</title></head>");
        sb.Append("<body style='font-family:sans-serif;max-width:760px;margin:40px auto'>");
        sb.Append("<h1>🔵 DotNetKeeper（L13 挑战）</h1>");
        sb.Append("<p>微软系上古系统。\"配置导入\"收 base64 的 <strong>.NET 序列化对象</strong>，");
        sb.Append("<code>BinaryFormatter.Deserialize</code> 恢复之。</p>");
        sb.Append(Source());
        sb.Append("<h3>情报：</h3><ul>");
        sb.Append("<li>提交口收 base64（表单 d 参数，POST）——.NET 的 BinaryFormatter 流");
        sb.Append("base64 后以 <code>AAEAAAD/////</code> 开头（指纹，认弹用的）。</li>");
        sb.Append("<li><strong>无回显</strong>：系统只回显导入的类型名。</li>");
        sb.Append("<li>落盘区 <code>webroot/</code>：可写，且 HTTP 可取回（GET /文件名）。</li>");
        sb.Append("<li>靶机 Windows。旗在服务器运行目录的 <code>flag_l13.txt</code>。</li>");
        sb.Append("<li>.NET 的弹药工厂（ysoserial.net）在 <code>D:\\deepseek\\ysoserial-net\\</code>。</li>");
        sb.Append("</ul>");
        sb.Append("<form method='post'>");
        sb.Append("<textarea name='d' rows='4' cols='80' placeholder='base64 的 .NET 序列化弹药'></textarea><br>");
        sb.Append("<button type='submit'>导入配置</button></form>");
        sb.Append("</body></html>");
        return sb.ToString();
    }

    static string Source()
    {
        return "<h3>核心源码（全透明）：</h3><pre style='background:#f6f6f6;padding:10px;'>"
            + "byte[] data = Convert.FromBase64String(d);        // ← 你控制的 base64\n"
            + "BinaryFormatter bf = new BinaryFormatter();\n"
            + "object obj = bf.Deserialize(new MemoryStream(data)); // ← 反序列化点：链在此引爆\n"
            + "// 老系统只回显类型名，不回显执行结果</pre>";
    }

    // ---- 小工具 ----
    static string FormParam(string body, string name)
    {
        foreach (string pair in body.Split('&'))
        {
            int i = pair.IndexOf('=');
            if (i > 0 && pair.Substring(0, i) == name)
            {
                return Uri.UnescapeDataString(pair.Substring(i + 1).Replace('+', ' '));
            }
        }
        return "";
    }

    static string Escape(string s)
    {
        return s.Replace("&", "&amp;").Replace("<", "&lt;").Replace(">", "&gt;");
    }

    static void Send(HttpListenerContext ctx, string html, string type)
    {
        byte[] content = Encoding.UTF8.GetBytes(html);
        ctx.Response.ContentType = type;
        ctx.Response.ContentLength64 = content.Length;
        ctx.Response.OutputStream.Write(content, 0, content.Length);
        ctx.Response.OutputStream.Close();
    }
}
