import com.sun.org.apache.xalan.internal.xsltc.trax.TemplatesImpl;
import com.sun.org.apache.xalan.internal.xsltc.runtime.AbstractTranslet;
import java.io.*;
import java.lang.reflect.*;
import java.util.*;

public class TestTemplate {
    public static void main(String[] a) throws Exception {
        // Rebuild the CB1 chain manually to see the real inner exception
        // 1. Get evil class bytes from the payload: extract from PriorityQueue -> BeanComparator -> TemplatesImpl
        byte[] ser = java.util.Base64.getDecoder().decode(
            java.nio.file.Files.readAllLines(java.nio.file.Paths.get(a[0])).get(0).trim());

        // Deserialize with all-white-list OIS to grab the TemplatesImpl
        ObjectInputStream ois = new ObjectInputStream(new ByteArrayInputStream(ser));
        Object pq = ois.readObject(); // will throw, so instead:
    }
}
