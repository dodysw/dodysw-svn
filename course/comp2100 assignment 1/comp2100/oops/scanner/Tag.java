package comp2100.oops.scanner;

import java.io.IOException;

import comp2100.oops.tree.AttributeList;

/**
 * Tokens representing complete XML tags, with attributes lookup
 * tables, name, etc.
 * 
 * @author Ian Barnes (original Eiffel code)
 * @author Alexei Khorev (Java port)
 * @version $Revision: 569 $, $Date: 2006-04-06 18:48:53 +1000 (Thu, 06 Apr 2006) $
 */
public class Tag extends Token {
	
    public Tag(String s) throws NullPointerException {
	if (s != null) {
	    this.name = s;
	    this.attributes = new AttributeList();
	    this.setStart(); // Tag type will be changed later if necessary
	} else throw new NullPointerException(s);
    }
	
    private final String type = "tag";
    
    public String getType() {
	return type;
    }
    
    private String name; 
    
    public String getName() {
	return name;
    }
    
    public AttributeList attributes; //make private, add access method
    
    public void setStart() {
	tagType = START;
    }
    
    public void setEnd() {
	tagType = END;
    }
    
    public void setEmpty() {
	tagType = EMPTY;
    }
    
    public void addAttribute(String aName, String aValue) {
	//String s = null;
	if (aName != null && aValue != null) {
	    try {
		attributes.add(aName, aValue);
	    } catch  (NullPointerException e) {
		System.err.println("Failed to make an entry, " + e.getMessage());
	    }
	} else throw new NullPointerException(aName);
    }
    
    public boolean isStartTag() {
	return tagType.equals(START);
    }
    
    public boolean isEndTag() {
	return tagType.equals(END);
    }
    
    public boolean isEmptyElement() {
	return tagType.equals(EMPTY);
    }
    
    public String toString() {
	String s = new String();
	String pad = comp2100.oops.StringOps.spaces(3);
	//if (isEndTag())  s = "/"+ s;
	s += "TAG" + "\n" + pad + "TYPE = " + tagType + "\n" + pad + 
		"NAME = " + "\"" + name +"\"\n";
	s += attributes.toString();
	//if (isEmptyElement()) s += "/";
	//s = tagType + " TAG: " + s + ">";
	return s;
    }
    
    private String tagType;
    private final static String START = "START", END = "END", EMPTY = "EMPTY";
    
    /**
     * The main method for testing; prints back the first string from
     * the cmdl, or (if the latter is not there) a hard-wired string.
     */
    public final static void main(String[] args) throws IOException {
	String stuff, name, value;
	
	if (args.length == 0) {
	    stuff = "Stendal";
	} else {
	    stuff = new String( args[0] );
	}
	
	Tag t = null;
	try {
	    t = new Tag(stuff);
	} catch (NullPointerException e) {
	    System.err.println(e.getMessage() + 
			       " passed as the Tag constructor param");
	}
	
	t.setEnd();
	System.out.println("the tag name is " + t.getName() + 
			   " and its type is " + t.tagType);
	t.setStart();
	System.out.println("now the tag name is " + t.getName() + 
			   " and its type is " + t.tagType);
	
	System.out.println("setting the first attribute...");
	name = new String("Rouge");
	value = new String("Red");
	t.addAttribute(name, value);
	System.out.println("under the key " + name + 
			   " there was value " + t.attributes.at(name));
	
	System.out.println("setting the second...");
	name = new String("et");	
	t.addAttribute(name, "and");
	System.out.println("under the key " + name + 
			   " there was value " + t.attributes.at(name));
	
	System.out.println("setting the third...");
	name = new String("Noir");
	t.addAttribute(name, "Black");
	System.out.println("under the key " + name + 
			   " there was value " + t.attributes.at(name));
	
	System.out.println("overwriting the third...");
	name = new String("Noir");
	t.addAttribute(name, "Schwarz");
	System.out.println("under the key " + name + 
			   " there was value " +t.attributes.at(name));
	
	System.out.println(t.toString());
	t.setEmpty();
	System.out.println(t.toString());
	
	t = new Tag("Proust");
	t.setEnd();
	t.addAttribute("Temp", "Time");
	t.addAttribute("Perdu", "lost");
	t.addAttribute("recherche", "searching");
	
	System.out.println(t.toString());
    }
}
