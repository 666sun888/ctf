import java.io.*;
import java.security.SignedObject;
public class GenBlocked {
    public static void main(String[] a) throws Exception {
        ObjectOutputStream o = new ObjectOutputStream(new FileOutputStream("blocked_probe.ser"));
        o.writeObject(new SignedObject(null, null, null));
        o.close();
        System.out.println("ok");
    }
}
