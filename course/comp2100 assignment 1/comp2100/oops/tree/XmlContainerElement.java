package comp2100.oops.tree;

import java.io.IOException;
import java.io.OutputStreamWriter;
import java.util.Vector;

import comp2100.oops.Assert;
import comp2100.oops.StringOps;
import comp2100.oops.scanner.Scanner;
import comp2100.oops.scanner.Tag;
import comp2100.oops.strategies.AnchorStrategy;
import comp2100.oops.strategies.AutomaticStyleStrategy;
import comp2100.oops.strategies.BodyStrategy;
import comp2100.oops.strategies.DocumentStrategy;
import comp2100.oops.strategies.HeadingStrategy;
import comp2100.oops.strategies.LineBreakStrategy;
import comp2100.oops.strategies.ListItemStrategy;
import comp2100.oops.strategies.OrderedListStrategy;
import comp2100.oops.strategies.ParagraphStrategy;
import comp2100.oops.strategies.SpaceStrategy;
import comp2100.oops.strategies.SpanStrategy;
import comp2100.oops.strategies.StylePropertiesStrategy;
import comp2100.oops.strategies.StyleStrategy;
import comp2100.oops.strategies.UnorderedListStrategy;
import comp2100.oops.strategies.XmlContainerStrategy;
import comp2100.oops.visitor.Visitor;

/**
 * Tree nodes representing standard XML elements: those that are
 * represented by a start tag, content and matching end tag (or
 * by the special empty element syntax).
 * 
 * @author Ian Barnes (original Eiffel code)
 * @author Alexei Khorev (Java port)
 * @version $Revision: 569 $, $Date: 2006-04-06 18:48:53 +1000 (Thu, 06 Apr 2006) $
 */
public class XmlContainerElement extends XmlElement {
    
    /** 
     * Parse this element from token stream on scanner.
     *  
     * @param scanner Scanner supplies qualified tokens for
     * building a parse tree with this object being its root
     * element
     */
    public void parse(Scanner scanner) {
	Assert.require(scanner.item().getType().equals("tag"),
		       "Token must be a tag, not data");
	
	Tag tag = (Tag) scanner.item(); 
	children = new Vector();
	name = tag.getName();
	strategy = setStrategy(name);
	attributes = tag.attributes;
	
	if (!tag.isEmptyElement()) {
	    // parse the contents
	    scanner.advance();
	    while(scanner.item().getType().equals("data") ||
		  !(tag = (Tag) scanner.item()).isEndTag()) {
		XmlElement child;
		if (scanner.item().getType().equals("tag")) {
		    child = new XmlContainerElement();
		    child.parse(scanner); // Recursive descent
		} else {
		    child = new XmlDataElement();
		    child.parse(scanner); // Base case (leaf node)
		}
		children.addElement(child);
		child.setParent(this);
	    }
	    
	    // Check if it's the correct end tag, and advance
	    if (!tag.getName().equals(name)) {
		System.err.println("Warning: End tag " + tag.toString()
				   + " does not match start tag <" + 
				   name + ">");
	    }
	}
	scanner.advance();
    }

    /**
     * After reading a tag from the scanner, initialize the
     * strategy of this XmlContainerElement based on the tag
     * name. There is, probably, a more elegant way to do it,
     * based on Java generics, or (prior to Java 1.5) using Java
     * reflection classes. We may opt for one of these two in
     * "later releases".
     *  
     *  @param name A string representing the node name
     */
    private XmlContainerStrategy setStrategy(String name) {
	XmlContainerStrategy s;		
	if (name.equals("office:document-content")) {
	    s = new DocumentStrategy(this);
	} else if (name.equals("office:automatic-styles")) {
	    s = new AutomaticStyleStrategy(this); 
	} else if (name.equals("style:style")) {
	    s = new StyleStrategy(this); 
	} else if (name.equals("style:properties")) {
	    s = new StylePropertiesStrategy(this); 
	} else if (name.equals("office:body")) {
	    s = new BodyStrategy(this); 
	} else if (name.equals("text:h")) {
	    s = new HeadingStrategy(this); 
	} else if (name.equals("text:p")) {
	    s = new ParagraphStrategy(this);
	} else if (name.equals("text:unordered-list")) {
	    s = new UnorderedListStrategy(this);
	} else if (name.equals("text:ordered-list")) {
	    s = new OrderedListStrategy(this);
	} else if (name.equals("text:list-item")) {
	    s = new ListItemStrategy(this);
	} else if (name.equals("text:span")) {
	    s = new SpanStrategy(this);
	} else if (name.equals("text:a")) {
	    s = new AnchorStrategy(this);
	} else if (name.equals("text:s")) {
	    s = new SpaceStrategy(this);
	} else if (name.equals("text:line-break")) {
	    s = new LineBreakStrategy(this);
	} else s = null;
	return s;
    } 
    
    /** The elements inside this element */
    public Vector children;
    
    /** This is not a data element, it is a container */
    public final boolean isData() {
	return false;
    }
		
    /** 
     * The strategy object that tells visitors how to deal with
     * this element
     */
    private XmlContainerStrategy strategy;
    
    /** 
     * The strategy object that tells visitors how to deal with
     * this element
     */
    public XmlContainerStrategy getStrategy() {
	return strategy;
    }
    
    /** The attributes lookup table */
    public AttributeList attributes;
	
    /** Print somewhat pretty, indented xml output */
    public void prettyPrint(final int indent, 
			    OutputStreamWriter output) throws IOException {
	Assert.require(indent >= 0, "Indent must be non-negative");
	
	int i, j;
	output.write(StringOps.spaces(indent));
	output.write("<" + name);
	
	if (attributes != null && !attributes.isEmpty()) {
	    j = name.length() + 2;
	    output.write(attributes.toString(indent + j));
	}
	
	if (children.size() == 0) {
	    output.write("/>\n");
	} else {
	    output.write(">\n");
	    for (i = 0; i < children.size(); i++) 
		((XmlElement)(children.elementAt(i))).prettyPrint(indent + 3, 
								  output);
	    output.write(StringOps.spaces(indent));
	    output.write("</" + name + ">\n");
	}
    }	
    
    /** Tell visitor how to visit this element */
    public void accept(Visitor visitor) {
	if (strategy != null) {
	    strategy.accept(visitor);
	} else {
	    visitor.visitUnknown(this);
	}
    }
    
    /** Make visitor visit all children */
    public void visitChildren(Visitor visitor) {
	if (children != null) {
	    for (int i = 0; i < children.size(); i++) {
		((XmlElement) children.elementAt(i)).accept(visitor);
	    }
	}
    }
}
