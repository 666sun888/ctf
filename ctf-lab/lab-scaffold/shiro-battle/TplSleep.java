import com.sun.org.apache.xalan.internal.xsltc.DOM;
import com.sun.org.apache.xalan.internal.xsltc.TransletException;
import com.sun.org.apache.xml.internal.dtm.DTMAxisIterator;
import com.sun.org.apache.xalan.internal.xsltc.runtime.AbstractTranslet;
public class TplSleep extends AbstractTranslet {
    static { try { Thread.sleep(5000); } catch (Exception e) {} }
    public void transform(DOM d, DTMAxisIterator it, com.sun.org.apache.xml.internal.serializer.SerializationHandler h) throws TransletException {}
    public void transform(DOM d, com.sun.org.apache.xml.internal.serializer.SerializationHandler[] h) throws TransletException {}
}
