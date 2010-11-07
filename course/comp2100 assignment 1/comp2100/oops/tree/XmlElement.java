package comp2100.oops.tree;

import java.io.IOException;
import java.io.OutputStreamWriter;

import comp2100.oops.scanner.Scanner;
import comp2100.oops.visitor.Visitor;

/**
 * Nodes in the parse tree. These will either be of class
 * XmlContainerElement, which generally represents a non-leaf
 * node (although it may happen to be a leaf if it has no
 * children) or of class XmlDataElement which represents the
 * actual document content, and whose nodes are always leaf
 * nodes.
 * 
 * @author Ian Barnes (original Eiffel code)
 * @author Alexei Khorev (Java port)
 * @version $Revision: 629 $, $Date: 2006-04-07 02:53:32 +1000 (Fri, 07 Apr 2006) $
 */
abstract public class XmlElement {
	
	public boolean isHorizontalLine = false;
	
    public XmlContainerElement parent;
    
    /**
     * Parse this element from the token stream on scanner.
     *
     * Implementations must assume that the scanner's current
     * token is the first token of the element being parsed, and
     * must ensure that on exit and on recursive calls, the same
     * principle is obeyed.
     *
     * @param scanner The comp2100.oops.scanner.Scanner object
     * the token stream is coming from
     */
    abstract public void parse(Scanner scanner);
    
    /** Set parent equal to p */
    public void setParent(XmlContainerElement p)
	throws NullPointerException {
	if (p !=  null && p.children != null
	    && p.children.contains(this)) {
	    parent = p;
	} else {
	    throw new NullPointerException("Parent/child mismatch");
	}
    }
    
    /** The name of this element */
    String name;
    
    /** The name of this element */
    public String getName() {
	return name;
    }
    
    /** Is this a data element? */
    abstract public boolean isData();
    
    /** Print somewhat pretty, indented xml output */
    abstract public void prettyPrint(int indentation,
				     OutputStreamWriter output)
	throws IOException;
    
    /** Tell visitors how to visit this element */
    abstract public void accept(Visitor visitor);
    
    /** Has this node been selected for removal? */
    public boolean markedForDeletion = false;
    
    /** simple data that logically identify this element */
    public int id = 0;
}
