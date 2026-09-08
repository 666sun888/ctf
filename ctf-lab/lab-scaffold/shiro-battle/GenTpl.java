import com.sun.org.apache.xalan.internal.xsltc.trax.TemplatesImpl;
import java.io.*;
public class GenTpl {
    public static void main(String[] a) throws Exception {
        ObjectOutputStream o = new ObjectOutputStream(new FileOutputStream("tpl_probe.ser"));
        o.writeObject(new TemplatesImpl());
        o.close();
        System.out.println("tpl_probe.ser ok");
    }
}
