import java.nio.file.*;
public class TestLoad extends ClassLoader {
    public static void main(String[] a) throws Exception {
        byte[] b = Files.readAllBytes(Paths.get(a[0]));
        Class<?> c = new TestLoad().defineClass("TplSleep", b, 0, b.length);
        System.out.println("loaded: " + c + " superclass: " + c.getSuperclass().getName());
        c.newInstance();
        System.out.println("instance ok");
    }
}
