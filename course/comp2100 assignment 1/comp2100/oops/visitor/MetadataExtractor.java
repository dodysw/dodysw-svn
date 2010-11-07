package comp2100.oops.visitor;

import java.util.Hashtable;
import java.util.Vector;

import comp2100.oops.Assert;
import comp2100.oops.tree.XmlContainerElement;
import comp2100.oops.tree.XmlDataElement;


/**
 * Visitor that extracts document metadata, using a lookup table
 * provided by a previous traversal by a StyleDecoder.
 *  
 * @author Ian Barnes (Original Eiffel version)
 * @author Alexei B Khorev (Java port)
 * @version $Revision: 569 $, $Date: 2006-04-06 18:48:53 +1000 (Thu, 06 Apr 2006) $
 */
public class MetadataExtractor implements Visitor {

    /**
     * Paragraph style parent information. This is obtained from
     * the StyleDecoder, which must be run before this is.
     */
    private Hashtable styleTable;
    
    /**
     * Metadata extracted. A list of strings (since there might
     * be multiple authors etc
     */
    private Vector title, author, affiliation;
    
    /** The extracted title of this document */
    public String getTitle() {
	String result = "";
	for(int i = 0; i < title.size(); i++) {
	    result += (String) title.elementAt(i);
	    if (i < title.size() - 1) {
		result += " ";
	    }
	}
	return result;
    }
    
    /** The extracted authors name (or authors' names) */
    public String getAuthor() {
	String result = "";
	for(int i = 0; i < author.size(); i++) {
	    result += (String) author.elementAt(i);
	    if (i < author.size() - 1) {
		result += " ";
	    }
	}
	return result;
    }

    /** The extracted author affiliation(s) */
    public String getAffiliation() {
	String result = "";
	for(int i = 0; i < affiliation.size(); i++) {
	    result += (String) affiliation.elementAt(i);
	    if (i < affiliation.size() - 1) {
		result += " ";
	    }
	}
	return result;
    }
    
    /** Are we recording the document title? */
    private boolean grabbingTitle = false;

    /** Are we recording the names(s) of the author(s)? */
    private boolean grabbingAuthor = false;

    /** Are we recording the author affiliation(s)? */
    private boolean grabbingAffiliation = false;
    
    /**
     * Initialise arrays of titles, authors and affiliations.
     *   
     * @param d Look up table for style inheritance information.
     */ 
    public MetadataExtractor(Hashtable d) {
	styleTable = d;
	title = new Vector();
	author = new Vector();
	affiliation = new Vector();
    }
	
    /** Print the accumulated information to standard output */
    public void printMetadata() {
	System.out.print("Title         = ");
	printArray(title);
	System.out.print("Author's Name = ");
	printArray(author);
	System.out.print("Affiliation   = ");
	printArray(affiliation);
    }
	
    /**
     * Print the elements of a Vector to output, with each
     * element in double quotes, elements separated by commas,
     * followed by a newline.
     * 
     * @param a The vector of values to be printed
     */
    public void printArray(Vector a) {
	int i;
	for(i = 0; i < a.size(); i++) {
	    System.out.print("\"" + ((String) a.elementAt(i)) + "\"");
	    if (i < a.size() - 1) {
		System.out.print(", ");
	    }
	}
	System.out.println();
    }
    
    public void visitDocument(XmlContainerElement x) {
	Assert.require(x.getName().equals("office:document-content"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
    }	
	
    public void visitHeading(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:h"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
    }

    public void visitAutomaticStyles(XmlContainerElement x) {
	Assert.require(x.getName().equals("office:automatic-styles"), 
		       "Visiting wrong node...");
	//x.visitChildren(this);
    }

    public void visitStyle(XmlContainerElement x) {
	Assert.require(x.getName().equals("style:style"), 
		       "Visiting wrong node...");
	//x.visitChildren(this);
    }

    public void visitStyleProperties(XmlContainerElement x) {
	Assert.require(x.getName().equals("style:properties"), 
		       "Visiting wrong node...");
	//x.visitChildren(this);
    }

    public void visitBody(XmlContainerElement x) {
	Assert.require(x.getName().equals("office:body"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
    }

    /**
     * Check if this paragraph has an attribute
     * <tt>text:style-name</tt> whose value (or its parent in
     * the lookup table) is one of <tt>"Title"</tt>,
     * <tt>"Author&apos;s Name"</tt> or
     * <tt>"Affiliation"</tt>. If so, set the appropriate flag
     * for use later when visiting descendant data node.
     */
    public void visitParagraph(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:p"), 
		       "Visiting wrong node...");
	String styleName;
	if (x.attributes.has("text:style-name")) {
	    // get style name and decode if necessary
	    styleName = x.attributes.at("text:style-name");
	    if (styleTable.containsKey(styleName)) {
		styleName = (String) styleTable.get(styleName);
	    }
	    
	    // set the appropriate flag
	    if (styleName.equals("Title")) {
		grabbingTitle = true;
	    } else if (styleName.equals("Author&apos;s Name")) {
		grabbingAuthor = true;
	    } else if (styleName.equals("Affiliation")) {
		grabbingAffiliation = true;
	    }

	    // recurse through the child nodes
	    x.visitChildren(this);

	    // reset all flags
	    grabbingTitle = false;
	    grabbingAuthor = false;
	    grabbingAffiliation = false;
	}
    }

    public void visitUnorderedList(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:unordered-list"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
    }

    public void visitOrderedList(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:ordered-list"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
    }

    public void visitListItem(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:list-item"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
    }
	
    public void visitUnorderedListItem(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:unordered-list"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
    }
		
    public void visitOrderedListItem(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:ordered-list"), 
		       "Visiting wrong node...");		
	x.visitChildren(this);
    }

    public void visitSpan(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:span"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
    }

    public void visitAnchor(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:a"),
		       "Visiting wrong node...");
	x.visitChildren(this);
    }
	
    public void visitSpace(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:s"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
    }
	
    public void visitLineBreak(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:line-break"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
    }

    /** Visit an unspecified element */
    public void visitUnknown(XmlContainerElement x) {
	x.visitChildren(this);
    }
	
    /**
     * Visit a data node and if a flag is set pick up metadata
     * content for Title, Author or Affiliation.
     */ 
    public void visitData(XmlDataElement d) {
	if (grabbingTitle) {
	    title.addElement(new String(d.content));
	} else if (grabbingAuthor) {
	    author.addElement(new String(d.content));
	} else if (grabbingAffiliation) {
	    affiliation.addElement(new String(d.content));
	}
    }
}
