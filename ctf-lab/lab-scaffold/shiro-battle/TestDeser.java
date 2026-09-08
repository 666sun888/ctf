import java.io.*;
public class TestDeser {
    public static void main(String[] a) throws Exception {
        ObjectInputStream o = new ObjectInputStream(new FileInputStream(a[0]));
        o.readObject();
        System.out.println("deser done");
    }
}
