import java.io.*;
import java.util.*;
public class GenProbe {
    public static void main(String[] a) throws Exception {
        java.util.HashMap<String,String> m = new java.util.HashMap<>();
        m.put("probe", "1");
        ObjectOutputStream o = new ObjectOutputStream(new FileOutputStream("probe.ser"));
        o.writeObject(m); o.close();
        System.out.println("probe.ser ok");
    }
}
