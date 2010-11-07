package comp2100.oops.visitor;

import java.util.Hashtable;

import comp2100.oops.Assert;
import comp2100.oops.tree.XmlContainerElement;
import comp2100.oops.tree.XmlDataElement;
import comp2100.oops.tree.XmlElement;

/**
 * Visitor that removes paragraph elements satisfying the
 * following conditions:
 * 1. They have exactly one child.
 * 2. That child is a data element.
 * 3. The content of that data element is either:
 *   a. All hyphens ('-') or
 *   b. All underscores ('_')
 * 
 * Algorithm: Traverse the tree marking nodes for deletion. When
 * visiting a data node, mark it for deletion if it is all
 * hyphens or underscores. When visiting a paragraph, after
 * visiting its children, mark it for deletion if it has only
 * one child, a data node marked for deletion. When visiting
 * anything else, after visiting its children, loop through them
 * in reverse order deleting any children that are paragraphs
 * and that have been marked.
 *
 * @author Ian Barnes
 * @author $Author: u4267771 $
 * @version $Rev: 629 $
 * $Date: 2006-04-07 02:53:32 +1000 (Fri, 07 Apr 2006) $
 */
public class TreeFixer implements Visitor {
    
	private Hashtable styleTable;
	private boolean isInParagraph = false;
	private String pBuffer = "";
	
	/** Are we recording the footnote? */
	private boolean grabbingFootnote = false;

	
	public TreeFixer(Hashtable d) {
		styleTable = d;
	}
	
    /**
     * Delete any immediate child elements that have been marked
     * for deletion. Loop through in reverse order so that
     * deletions don't mess up the iteration.
     * 
     * @param x the node to which the procedure is applied
     */
    private void deleteBadChildren(XmlContainerElement x) {
	for(int i = x.children.size() - 1; i >= 0; i--) {
	    if (((XmlElement) x.children.elementAt(i)).getName().equals("text:p") &&
		((XmlElement) x.children.elementAt(i)).markedForDeletion) {
		x.children.removeElementAt(i);
		//System.out.println("Current element is " + x.getName() + 
		//". Deleting child number " + i);
	    }
	}
    }
	
    /**
     * Does string s consist nothing but hyphens?
     * 
     * @param s string to be examined
     */
    private boolean isAllHyphens(String s) {
	boolean b = true;
	for(int i = 0; i < s.length(); i++) {
	    if (s.charAt(i) != '-') {
		b = false;
		break;
	    }
	}
	return b;
    }

    /**
     * Does string s consist nothing but underscores?
     *  
     * @param s string to be examined
     */
    private boolean isAllUnderscores(String s) {
	boolean b = true;
	for(int i = 0; i < s.length(); i++) {
	    if (s.charAt(i) != '_') {
		b = false;
		break;
	    }
	}
	return b;
    }
    
    public void visitDocument(XmlContainerElement x) {
	Assert.require(x.getName().equals("office:document-content"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
	deleteBadChildren(x);
    }	
	
    public void visitHeading(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:h"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
	deleteBadChildren(x);
    }

    public void visitAutomaticStyles(XmlContainerElement x) {
	Assert.require(x.getName().equals("office:automatic-styles"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
	deleteBadChildren(x);
    }

    public void visitStyle(XmlContainerElement x) {
	Assert.require(x.getName().equals("style:style"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
	deleteBadChildren(x);
    }

    public void visitStyleProperties(XmlContainerElement x) {
	Assert.require(x.getName().equals("style:properties"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
	deleteBadChildren(x);
    }

    public void visitBody(XmlContainerElement x) {
	Assert.require(x.getName().equals("office:body"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
	deleteBadChildren(x);
    }

    /**
     * Visit a paragraph. Mark it for deletion if is has exactly
     * one child, a data element marked for deletion.
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
			if (styleName.equals("Footnote")) {
				grabbingFootnote = true;
			}
		}		
	//mark that we're inside paragraph, and append all data into a buffer
	isInParagraph = true;
	pBuffer = "";
	
	x.visitChildren(this);
	
	isInParagraph = false;	
	//afterward, trim + check of all __ / ---
	//	tag as horizontal line if fulfilled!
	if (pBuffer.length() > 0 && (isAllHyphens(pBuffer.trim()) || isAllUnderscores(pBuffer.trim()))) {
		x.isHorizontalLine = true;
	}
		
	
	
	if (x.children != null && x.children.size() == 1 &&
	    x.children.elementAt(0) instanceof XmlDataElement &&
	    ((XmlElement) x.children.elementAt(0)).markedForDeletion) {
	    x.markedForDeletion = true;
	    //System.out.println("Marked paragraph element for deletion");
	}
		
		// reset all flags
		grabbingFootnote = false;		
    }
    
    public void visitUnorderedList(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:unordered-list"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
	deleteBadChildren(x);
    }
    
    public void visitOrderedList(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:ordered-list"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
	deleteBadChildren(x);
    }
    
    public void visitListItem(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:list-item"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
	deleteBadChildren(x);
    }
    
    public void visitUnorderedListItem(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:unordered-list"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
	deleteBadChildren(x);
    }
    
    public void visitOrderedListItem(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:ordered-list"), 
		       "Visiting wrong node...");		
	x.visitChildren(this);
	deleteBadChildren(x);
    }
    
    public void visitSpan(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:span"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
	deleteBadChildren(x);
    }
    
    public void visitAnchor(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:a"),
		       "Visiting wrong node...");
	x.visitChildren(this);
	deleteBadChildren(x);
    }
    
    public void visitSpace(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:s"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
	deleteBadChildren(x);
    }
    
    public void visitLineBreak(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:line-break"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
	deleteBadChildren(x);
    }
    
    /** Visit an unspecified element */
    public void visitUnknown(XmlContainerElement x) {
	x.visitChildren(this);
	deleteBadChildren(x);
    }
    
    /**
     * Visit a data element. Mark it for deletion if its content
     * is all underscores or all hyphens.
     */ 
    public void visitData(XmlDataElement d) {
    	String trimmed_content = d.content.trim();
    	
    	if (isInParagraph) {
    		pBuffer += trimmed_content; 
    	}
		
		if (grabbingFootnote) {
			System.out.println("Footnote: \"" + d.content + "\"");
			d.isFootnote = true;			
		}		
    }
    
}
