import com.butler.springboot14shiro.Util.MyObjectInputStream;
import java.io.*;

public class Repro {
    public static void main(String[] a) throws Exception {
        byte[] data = java.util.Base64.getDecoder().decode(
            java.nio.file.Files.readAllLines(java.nio.file.Paths.get(a[0])).get(0).trim());
        try {
            ObjectInputStream o = new MyObjectInputStream(new ByteArrayInputStream(data));
            o.readObject();
            System.out.println("[OK] no exception - gadget worked");
        } catch (Throwable t) {
            System.out.println("[FAIL] " + t.getClass().getName() + ": " + t.getMessage());
            Throwable c = t.getCause();
            while (c != null) { System.out.println("  caused by " + c.getClass().getName() + ": " + c.getMessage()); c = c.getCause(); }
        }
    }
}
