package comp2100.oops.scanner;

import java.io.IOException;

/**
 * Character data tokens -- the stuff between the tags
 * 
 * @author Ian Barnes (original Eiffel code)
 * @author Alexei Khorev (Java port)
 * @version $Revision: 569 $, $Date: 2006-04-06 18:48:53 +1000 (Thu, 06 Apr 2006) $
 */
public class Data extends Token {
	
    public Data(String s) {
	this.content = s;
    }
    
    private final String type = "data";
    
    public String getType() {
	return type;
    }
    
    public String content;
    
    public String toString() {
	String pad = comp2100.oops.StringOps.spaces(3);
	return "CHARACTER DATA" + "\n" + pad + "LENGTH = " + content.length()
		+ "\n" + pad + "CONTENT = " + "\"" + content + "\"";
    }
	
    /**
     * The main method for testing; prints back the first string from
     * the cmdl, or (if the latter is not there) a hard-wired string.
     */
    public final static void main(String[] args) throws IOException {
	
	String stuff;
	
	if (args.length == 0) {
	    stuff = "This is a sample data, allright?";
	} else {
	    stuff = new String(args[0]);
	}
	
	Data d = new Data(stuff);
	System.out.println("The type of this token is " + d.getType());
	System.out.println(d.toString());
    }
}
