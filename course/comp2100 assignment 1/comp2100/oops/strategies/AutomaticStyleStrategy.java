package comp2100.oops.strategies;

import comp2100.oops.tree.XmlContainerElement;
import comp2100.oops.visitor.Visitor;

/** A strategy class appropriate for office:automatic-styles elements
 * 
 * @author Ian Barnes (original Eiffel code)
 * @author Alexei B Khorev (Java port)
 * @version $Revision: 569 $
 * $Date:
 */
public class AutomaticStyleStrategy extends XmlContainerStrategy {
	
	public AutomaticStyleStrategy(XmlContainerElement x) {
		
		super(x);
	}
	
	// tells visitor how to visit this owner
	public void accept(Visitor visitor) {
		
		visitor.visitAutomaticStyles(owner);
		
	}
	
}
