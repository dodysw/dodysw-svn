package comp2100.oops.visitor;

import comp2100.oops.Assert;
import comp2100.oops.tree.*;

import java.util.Hashtable;
import java.util.Vector;

/**
 * Visitor that traverses XML parse tree extracting header information
 * about style inheritance and storing it in a lookup table.
 *  
 * @author Ian Barnes
 * @author Alexei B Khorev (Java port)
 * @version $Revision: 629 $, $Date: 2006-04-07 02:53:32 +1000 (Fri, 07 Apr 2006) $
 * @author $Author: u4267771 $
 * @version $Rev: 629 $
 * $Date: 2006-04-07 02:53:32 +1000 (Fri, 07 Apr 2006) $
 */

public class HeadingsDecoder implements Visitor {
	
	private String sContent;
	public Vector<Headings> HeadingList;
	private boolean isHeading = false;	
	/**
	 * Constructor creates a lookupTable and turns off the
	 * recording.
	 */ 
	
	private Hashtable styleTable;
	
	public HeadingsDecoder(Hashtable d) {
		styleTable = d;
		HeadingList = new Vector<Headings>();
	}
	
	public void visitDocument(XmlContainerElement x) {
		Assert.require(x.getName().equals("office:document-content"), 
		"Visiting wrong node...");
		x.visitChildren(this);
	}	
	
	/**
	 * Mark all the headings and save them into a vector
	 */
	public void visitHeading(XmlContainerElement x) {
		Assert.require(x.getName().equals("text:h"), 
		"Visiting wrong node...");
		int ilevel = 1;
		if (x.attributes.has("text:level")) {
			ilevel = Integer.parseInt(x.attributes.at("text:level"));
			isHeading = true;
		} else {
			System.err.println("Error: Heading without level!!");
		}
		
		x.visitChildren(this);		
		isHeading = false;
		HeadingList.add(new Headings( sContent, ilevel ));
	}
	
	/** visit a data node and need do nothing */ 
	public void visitData(XmlDataElement d) {
		if (isHeading)
			sContent = d.content;
	}
	
	public void visitAutomaticStyles(XmlContainerElement x) {
		Assert.require(x.getName().equals("office:automatic-styles"), 
		"Visiting wrong node...");
		x.visitChildren(this);
	}
	
	public void visitStyle(XmlContainerElement x) {
		Assert.require(x.getName().equals("style:style"), 
		"Visiting wrong node...");
		x.visitChildren(this);
	}
	
	public void visitStyleProperties(XmlContainerElement x) {
		Assert.require(x.getName().equals("style:properties"), 
		"Visiting wrong node...");
		x.visitChildren(this);
	}
	
	public void visitBody(XmlContainerElement x) {
		Assert.require(x.getName().equals("office:body"), 
		"Visiting wrong node...");
		x.visitChildren(this);
	}
	
	public void visitParagraph(XmlContainerElement x) {
		Assert.require(x.getName().equals("text:p"), 
		"Visiting wrong node...");	
		
		//note, Lecturer change requirements!
		//1st level heading now: <text:p text:style-name="Primary Head">1. INTRODUCTION</text:p>
		//2nd level heading now: <text:p text:style-name="Secondary Head">1. INTRODUCTION</text:p>
		int ilevel = 1;
		if (x.attributes.has("text:style-name")) {
			String styleName = x.attributes.at("text:style-name");
		    if (styleTable.containsKey(styleName)) {
				styleName = (String) styleTable.get(styleName);
			}
			if (styleName.equals("Primary Head")) {
				isHeading = true;
				ilevel = 1;
			}
			else if (styleName.equals("Secondary Head")) {
				isHeading = true;
				ilevel = 2;
			}			
		}		
		x.visitChildren(this);
		
		if (isHeading) {
			isHeading = false;
			HeadingList.add(new Headings( sContent, ilevel ));
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
	}
	
	/** Visit an unspecified element */
	public void visitUnknown(XmlContainerElement x) {
		x.visitChildren(this);
	}
	
	
}

class Headings {
	private String sTitle;
	private int iLevel;
	
	public Headings( String sHeadingTitle, int iHeadingLevel ) {
		sTitle = sHeadingTitle;
		iLevel = iHeadingLevel;
	}
	
	public String getTitle() {
		return sTitle;
	}
	
	public int getLevel() {
		return iLevel;
	}
}
