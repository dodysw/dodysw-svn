package comp2100.oops.tree;

import java.io.IOException;
import java.io.OutputStreamWriter;

import comp2100.oops.StringOps;
import comp2100.oops.scanner.Data;
import comp2100.oops.scanner.Scanner;
import comp2100.oops.visitor.Visitor;

/** 
 * Character data elements, document's actual content. The stuff
 * between the tags.
 * 
 * @author Ian Barnes (original Eiffel code)
 * @author Alexei Khorev (Java port)
 * @author $Author: u4267771 $
 * @version $Rev: 629 $
 * $Date: 2006-04-07 02:53:32 +1000 (Fri, 07 Apr 2006) $
 */
public class XmlDataElement extends XmlElement {
    
    /**
     * Parse a data element from scanner. In fact since the
     * scanner has already done this, there's not much to do:
     * just copy the relevant information across from the DATA
     * token into this XML_DATA_ELEMENT.
     *  
     * @param scanner Scanner supplies qualified tokens for
     * building a parse tree with this object being its leaf
     */
		
	public boolean isFootnote = false;

    public void parse(Scanner scanner) throws NullPointerException {
	Data data;
	if (scanner.item().getType().equals("data")) {
	    // use instanceof to mock initialization attempt
	    if (scanner.item().getType().equals("data")) {
		data = (Data) scanner.item();
		content = data.content;
		name = new String("DATA");
		scanner.advance();
	    }
	} else {
	    throw new NullPointerException("Current token is not data");
	}
    }
    
    /** This is a data element, not a container */
    public final boolean isData() {
	return true;
    }
	
    /** The actual content */
    public String content;
	
    /**
     * Print somewhat pretty, indented xml output
     *  
     * @param indentation number of empty spaces on the text margins
     * @param output where we are writing to
     */
    public void prettyPrint(final int indentation, 
			    OutputStreamWriter output) throws IOException {
	String result = StringOps.spaces(indentation) +
	    "\"" + content + "\"\n";
	output.write(result, 0, result.length());
    }
    
    /** Tell visitor how to visit this element */
    public void accept(Visitor visitor) {
	visitor.visitData(this);
    }
}
