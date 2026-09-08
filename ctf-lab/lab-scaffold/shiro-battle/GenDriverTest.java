import org.apache.commons.beanutils.BeanComparator;
import java.io.*;
import java.lang.reflect.Field;
import java.util.*;
public class GenDriverTest {
    public static void main(String[] a) throws Exception {
        BeanComparator cmp = new BeanComparator(null, String.CASE_INSENSITIVE_ORDER);
        PriorityQueue<Object> q = new PriorityQueue<>(2, cmp);
        q.add("1"); q.add("2");
        Field f = PriorityQueue.class.getDeclaredField("queue");
        f.setAccessible(true);
        Object[] arr = (Object[]) f.get(q);
        arr[0] = new Date(1000);
        arr[1] = new Date(2000);
        Field pf = BeanComparator.class.getDeclaredField("property");
        pf.setAccessible(true);
        pf.set(cmp, "time");
        ObjectOutputStream o = new ObjectOutputStream(new FileOutputStream("driver_test.ser"));
        o.writeObject(q);
        o.close();
        System.out.println("ok");
    }
}
