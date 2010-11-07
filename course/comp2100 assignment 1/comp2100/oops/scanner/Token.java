package comp2100.oops.scanner;

/** 
 * Items produced by the XML scanner: they can be either tags or
 * character data.
 * 
 * @author Ian Barnes (original Eiffel code)
 * @author Alexei Khorev (Java port)
 * @version $Revision: 569 $, $Date: 2006-04-06 18:48:53 +1000 (Thu, 06 Apr 2006) $
 */
public abstract class Token {
    
    private String type; 
    
    abstract public String getType();
    
    abstract public String toString();
    
}
