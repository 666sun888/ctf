import java.io.*;
import java.lang.reflect.*;
import java.util.*;

public class Repro4 {
    public static void main(String[] a) throws Exception {
        byte[] ser = java.util.Base64.getDecoder().decode(
            java.nio.file.Files.readAllLines(java.nio.file.Paths.get(a[0])).get(0).trim());
        try {
            new ObjectInputStream(new ByteArrayInputStream(ser)).readObject();
        } catch (Throwable t) {
            // BFS the suppressed + causes
            Deque<Throwable> q = new ArrayDeque<>();
            q.add(t);
            while (!q.isEmpty()) {
                Throwable cur = q.poll();
                System.out.println("== " + cur.getClass().getName() + ": " + cur.getMessage());
                if (cur.getCause() != null) q.add(cur.getCause());
                if (cur instanceof InvocationTargetException) {
                    Throwable tgt = ((InvocationTargetException) cur).getTargetException();
                    if (tgt != null && tgt != cur.getCause()) q.add(tgt);
                }
                for (Throwable s : cur.getSuppressed()) q.add(s);
            }
        }
    }
}
