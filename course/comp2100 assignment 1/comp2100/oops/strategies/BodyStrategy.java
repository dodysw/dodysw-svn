package comp2100.oops.strategies;

import comp2100.oops.tree.XmlContainerElement;
import comp2100.oops.visitor.Visitor;

/** A strategy class appropriate for office:body elements
 * 
 * @author Ian Barnes (original Eiffel code)
 * @author ABX (Java port)
 * @version $Revision
 * $Date:
 */
public class BodyStrategy extends XmlContainerStrategy {
	
	public BodyStrategy(XmlContainerElement x) {
		
		super(x);
	}
	
	// tells visitor how to visit this owner
	public void accept(Visitor visitor) {
		
		visitor.visitBody(owner);
		
	}
	
}


		
										  
		