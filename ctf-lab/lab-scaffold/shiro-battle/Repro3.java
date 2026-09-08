import java.io.*;
import java.lang.reflect.*;

public class Repro3 {
    public static void main(String[] a) throws Exception {
        byte[] ser = java.util.Base64.getDecoder().decode(
            java.nio.file.Files.readAllLines(java.nio.file.Paths.get(a[0])).get(0).trim());
        // extract the embedded evil class bytes: just try defineClass via TemplatesImpl manually
        // simpler: find bytecode from ser? Instead: deserialize and catch, unwrap
        try {
            new ObjectInputStream(new ByteArrayInputStream(ser)).readObject();
        } catch (Throwable t) {
            Throwable cur = t;
            while (cur != null) {
                System.out.println("== " + cur.getClass().getName() + ": " + cur.getMessage());
                if (cur instanceof InvocationTargetException && cur.getCause() == null) {
                    // try getTargetException
                    Throwable tgt = ((InvocationTargetException) cur).getTargetException();
                    System.out.println("   target: " + (tgt == null ? "null" : tgt.getClass().getName() + ": " + tgt.getMessage()));
                }
                cur = cur.getCause();
            }
        }
        // also try loading evil class directly from serialized PriorityQueue field _bytecodes
        // walk manually: too complex; instead test defineClass with extracted bytes:
        java.lang.reflect.Method m = ClassLoader.class.getDeclaredMethod("defineClass", String.class, byte[].class, int.class, int.class);
        m.setAccessible(true);
        System.out.println("defineClass accessible: OK (JDK allows with add-opens)");
    }
}
