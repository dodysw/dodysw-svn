package comp2100.oops.visitor;

import java.util.Enumeration;
import java.util.Hashtable;

import comp2100.oops.Assert;
import comp2100.oops.tree.XmlContainerElement;
import comp2100.oops.tree.XmlDataElement;

/**
 * Visitor that traverses XML parse tree extracting information
 * about style inheritance and storing it in a lookup table.
 *  
 * @author Ian Barnes
 * @author Alexei B Khorev (Java port)
 * @version $Revision: 569 $, $Date: 2006-04-06 18:48:53 +1000 (Thu, 06 Apr 2006) $
 */
public class StyleDecoder implements Visitor {
    
    /** Are we currently recording style inheritance? */
    private boolean recordingIsOn;
    
    /**
     * Where the information is stored. The key is the child
     * style name, the value is the parent style name.
     */
    public Hashtable lookupTable;

    /** The list of child-parent pairs */
    public String toString() {
        String result = new String();
        Enumeration e = lookupTable.keys();
        if (e != null) {
            while (e.hasMoreElements()) {
                String key = (String) e.nextElement();
                String value = (String) lookupTable.get(key);
                result += " " + key + " --> " + value + "\n";
            }
        }
        return result;
    }
    
    /**
     * Constructor creates a lookupTable and turns off the
     * recording.
     */ 
    public StyleDecoder() {
	lookupTable = new Hashtable();
	recordingIsOn = false;
    }
    
    public void visitDocument(XmlContainerElement x) {
	Assert.require(x.getName().equals("office:document-content"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
    }
    
    public void visitHeading(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:h"), 
		       "Visiting wrong node...");
    }
    
    /**
     * Style information in Style nodes is determined by their
     * position INSIDE the automatic style node
     */
    public void visitAutomaticStyles(XmlContainerElement x) {
	Assert.require(x.getName().equals("office:automatic-styles"), 
		       "Visiting wrong node...");
	recordingIsOn = true;
	x.visitChildren(this);
	recordingIsOn = false;
    }

    public void visitStyle(XmlContainerElement x) {
	Assert.require(x.getName().equals("style:style"), 
		       "Visiting wrong node...");
	String parent, child;
	if (recordingIsOn) {
	    if (x.attributes.has("style:parent-style-name") &&
		x.attributes.has("style:name")) {
		parent = x.attributes.at("style:parent-style-name");
		child = x.attributes.at("style:name");
		lookupTable.put(child, parent);
	    }
	}
    }
    
    public void visitStyleProperties(XmlContainerElement x) {
	Assert.require(x.getName().equals("style:properties"), 
		       "Visiting wrong node...");
    }
    
    public void visitBody(XmlContainerElement x) {
	Assert.require(x.getName().equals("office:body"), 
		       "Visiting wrong node...");
    }
    
    public void visitParagraph(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:p"), 
		       "Visiting wrong node...");
    }
    
    public void visitUnorderedList(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:unordered-list"), 
		       "Visiting wrong node...");
    }
    
    public void visitOrderedList(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:ordered-list"), 
		       "Visiting wrong node...");
    }
    
    public void visitListItem(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:list-item"), 
		       "Visiting wrong node...");
    }
    
    public void visitSpan(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:span"), 
		       "Visiting wrong node...");
    }
    
    public void visitAnchor(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:a"), 
		       "Visiting wrong node...");
    }
    
    public void visitSpace(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:s"), 
		       "Visiting wrong node...");
    }
    
    public void visitLineBreak(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:line-break"), 
		       "Visiting wrong node...");
    }
    
    /** visit an unspecified element */
    public void visitUnknown(XmlContainerElement x) {
	x.visitChildren(this);
    }
    
    /** visit a data node and need do nothing */ 
    public void visitData(XmlDataElement d) {}
    
}
