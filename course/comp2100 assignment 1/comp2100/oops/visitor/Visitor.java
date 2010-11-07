package comp2100.oops.visitor;

import comp2100.oops.tree.XmlContainerElement;
import comp2100.oops.tree.XmlDataElement;

/**
 * Objects that traverse the XML parse tree performing various
 * operations or extracting information.
 * 
 * Part of the Visitor pattern.
 * 
 * @author Ian Barnes
 * @author Alexei Khorev (Java port)
 * @version $Revision: 629 $, $Date: 2006-04-07 02:53:32 +1000 (Fri, 07 Apr 2006) $
 */
public interface Visitor {
    
    public void visitDocument(XmlContainerElement x);
    public void visitAutomaticStyles(XmlContainerElement x);
    public void visitStyle(XmlContainerElement x);
    public void visitStyleProperties(XmlContainerElement x);
    public void visitBody(XmlContainerElement x);
    public void visitHeading(XmlContainerElement x);
    public void visitParagraph(XmlContainerElement x);
    public void visitUnorderedList(XmlContainerElement x);
    public void visitOrderedList(XmlContainerElement x);
    public void visitListItem(XmlContainerElement x);
    public void visitSpan(XmlContainerElement x);
    public void visitAnchor(XmlContainerElement x);	
    public void visitSpace(XmlContainerElement x);	
    public void visitLineBreak(XmlContainerElement x);

    /** Visit an unspecified element */
    public void visitUnknown(XmlContainerElement x);
	
    /** Visit a data item (the actual content) */
    public void visitData(XmlDataElement data);
	
}

class Footnote {
	public String text;
	public int id;
	public boolean printed;
	
	public Footnote(int id, String text) {
		this.text = text;
		this.id = id;
		this.printed = false;
	}
}