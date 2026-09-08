import com.butler.springboot14shiro.Util.MyObjectInputStream;
import java.io.*;

public class Repro2 {
    public static void main(String[] a) throws Exception {
        byte[] data = java.util.Base64.getDecoder().decode(
            java.nio.file.Files.readAllLines(java.nio.file.Paths.get(a[0])).get(0).trim());
        try {
            new MyObjectInputStream(new ByteArrayInputStream(data)).readObject();
            System.out.println("[OK] no exception");
        } catch (Throwable t) {
            t.printStackTrace();
        }
    }
}
