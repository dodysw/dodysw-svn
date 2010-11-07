package comp2100.oops.strategies;

import comp2100.oops.tree.XmlContainerElement;
import comp2100.oops.visitor.Visitor;

/** The Strategy pattern, allowing XmlContainerElements
 * to behave as if they belong to different classes.  A
 * bit like subclassing, but it happens after creation.
 * 
 * Interface to classes containing specific information 
 * about different types of xml_element tree nodes.
 * 
 * Firstly, pseudo-polymorphism for the tree, as part of 
 * the Visitor pattern.  The `owner' object doesn't know 
 * what to do, but this one does.  Saves checking the 
 * name against a list more than once.
 * 
 * Second use might well be to implement tree-cleaning 
 * strategies: different types of elements have different 
 * content models and will authorise (or not) deletion of 
 * whitespace-only child elements.
 * 
 * @author Ian Barnes (original Eiffel code)
 * @author Alexei B Khorev (Java port)
 * @version $Revision: 569 $
 * $Date:
 */
abstract public class XmlContainerStrategy {
	
	public XmlContainerStrategy(XmlContainerElement x) {
		
		owner = x;
		
	}
	
	public XmlContainerElement owner;
	
	// tells visitor how to visit this owner
	abstract public void accept(Visitor visitor);
	
}
