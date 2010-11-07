package comp2100.oops.visitor;

import comp2100.oops.Assert;
import comp2100.oops.tree.*;

import java.util.Hashtable;
import java.util.Iterator;
import java.util.Vector;

/**
 * Visitor that do these:
 * 
 * (1)
 * removes paragraph elements satisfying the
 * following conditions:
 * 1. They have exactly one child.
 * 2. That child is a data element.
 * 3. The content of that data element is whitespace
 * 
 * (2)
 * if it's ordered list (OL), and the next element is also OL, and it's on the same level, merge to the first OL
 * do this until there's no more OL on the immediate next element.
 *    
 * 
 * @author $Author: u4267771 $
 * @version $Rev: 629 $
 * $Date: 2006-04-07 02:53:32 +1000 (Fri, 07 Apr 2006) $
 */

public class OrderListMerger extends TreeFixer {

	public OrderListMerger(Hashtable d) {
		super(d);
	}	

	
	/** Are we inside these elements ? */
	private boolean insideHeading = false;
	private boolean insideParagraph = false;
	private boolean insideSpan = false;
	private boolean insideAnchor = false;

	public void visitHeading(XmlContainerElement x) {
		insideHeading = true;
		super.visitHeading(x);
		insideHeading = false;
	}
	
	public void visitParagraph(XmlContainerElement x) {
		insideParagraph = true;
		super.visitParagraph(x);
		insideParagraph = false;
	}
	
	public void visitSpan(XmlContainerElement x) {
		insideSpan = true;
		super.visitSpan(x);
		insideSpan = false;
	}
	
	public void visitAnchor(XmlContainerElement x) {
		insideAnchor = true;
		super.visitAnchor(x);
		insideAnchor = false;
	}

	
	public void visitOrderedList(XmlContainerElement x) {
		
		//check if there's another orderlist immediately after this
		int this_index = x.parent.children.indexOf(x);
		int i = this_index+1;
		Vector<Integer> mark_to_delete = new Vector<Integer>();
		while (i < x.parent.children.size()) {
			//merging can only occur if there's another order-list directly after this
			//a data with whitespace should also trigger merging
			//after that, then delete the old order list
						
			XmlContainerElement nextElement = (XmlContainerElement) x.parent.children.get(i);
			
			//sample13 case, prune text:s tag first!
			if (nextElement.getName().equals("text:p")) {
				boolean is_space = true;
				for (int j=0; j < nextElement.children.size(); j++) {
					String name = ((XmlElement) nextElement.children.get(j)).getName();
					if ( !(name.equals("text:s"))) {
						is_space = false;
						break;
					}
				}
				if (is_space)
					mark_to_delete.add(i);
				else
					break;
			}				
			else if (nextElement.getName().equals("text:ordered-list")) {
				for (int j=0; j < nextElement.children.size(); j++) {
					x.children.add(nextElement.children.get(j));
				}
				mark_to_delete.add(i);
			}
			else if (nextElement.isData() 
					&& ((XmlDataElement)(XmlElement) nextElement).content.trim() == "") {
				((XmlElement) nextElement).markedForDeletion = true;
			}
			else {
				break;
			}
			i++;
		}
		
		//remove the previously copied order list. delete from behind!
		for (int k=mark_to_delete.size()-1; k >= 0; k--) {
			x.parent.children.remove(x.parent.children.get(mark_to_delete.get(k)));
		}
		
		insideSpan = true;
		super.visitOrderedList(x);
		insideSpan = false;
	}
	
	/**
	 * Visit a data element. Mark it for deletion if its content
	 * is all underscores or all hyphens.
	 */ 
	public void visitData(XmlDataElement d) {
		if (!insideHeading && !insideParagraph && !insideSpan && !insideAnchor && d.content == " ") {
			d.markedForDeletion = true;
		}
	}
	
}
