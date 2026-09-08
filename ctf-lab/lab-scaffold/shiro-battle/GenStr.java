import java.io.*;
public class GenStr {
    public static void main(String[] a) throws Exception {
        ObjectOutputStream o = new ObjectOutputStream(new FileOutputStream("str_probe.ser"));
        o.writeObject("PWNED_MARKER_123");
        o.close();
        System.out.println("ok");
    }
}
