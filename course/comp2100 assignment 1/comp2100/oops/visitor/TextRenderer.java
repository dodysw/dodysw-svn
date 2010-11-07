package comp2100.oops.visitor;

import comp2100.oops.Assert;
import comp2100.oops.tree.*;
import java.io.*;
import java.util.Enumeration;
import java.util.Hashtable;
import java.util.StringTokenizer;
import comp2100.oops.StringOps;
import java.util.Vector;

/**
 * Visitors that render a tree as formatted plain text on a
 * given output stream.
 * 
 * @author Ian Barnes (Original Eiffel version)
 * @author Alexei B Khorev (Java port)
 * @author $Author: u4267771 $
 * @version $Rev: 569 $
 * $Date: 2006-04-06 18:48:53 +1000 (Thu, 06 Apr 2006) $
 */
public class TextRenderer implements Visitor {
	
    /**
     * Initialise to write to the given OutputStreamWriter.
     *  
     * @param s The stream we are writing to
     */ 
    public TextRenderer(OutputStreamWriter s) {
	output = s;
	line = new String();
	doIndentation();
    }
	
	/** incremental number for footnote id*/
	private int lastFootnoteId = 1;
	
	/** array which stores footnotes */
	private Vector<Footnote> footnoteData = new Vector<Footnote>();
	
    /** Stream we are writing  to */
    private OutputStreamWriter output;
	
    /** The current line being prepared for output */
    private String line;
	
    /** The maximum number of characters on a line */
    private final int MaxLength = 64;
	
    /** Number of spaces to indent */
    private final int IndentationIncrement = 4;
    
    /** Current number of indents */
    private int depth;
    
    /**
     * The current right margin. Not always the same as
     * MaxLength since we indent at the right as well as at the
     * left.
     */
    private int rightMargin() {
    	return (MaxLength - depth * IndentationIncrement);
    }
    
    /** 
     * The number of characters in lines now. This does not
     * include leading or trailing blanks, numbers or
     * bullets. So it's the number of characters between the
     * left and right margins.
     */
    private int lineLength() {
	return (MaxLength - 2 * depth * IndentationIncrement);
    }		  
    
    /** Is the line is just white space? */
    private boolean lineIsEmpty;
    
    /** Are we in all upper-case  mode? */
    private boolean upperCase;
    
    /**
     * Send the rest of the current line to output, unless it's
     * empty, in which case do nothing.  We do not use newLine()
     * methods since the output is not presumed to be a
     * BufferedWriter
     */
    private void flushLine() {
	if (!lineIsEmpty) {
	    try {
		output.write(line);
		output.write("\n");
		doIndentation();
		lineIsEmpty = true;
	    } catch (IOException e) {
		e.printStackTrace(System.err);
		System.exit(1);
	    }
	}
    }
    
    /**
     * Put a blank line on the output, making sure to flush
     * anything pending
     */
    private void blankLine() {
	try {
	    flushLine();
	    output.write("\n");
	} catch  (IOException e) {
	    System.err.println("Exception: " + e.getMessage());
	}
    }
    
    /** Increase indentation depth */
    private void indent() {
	depth++;
    }
    
    /** Decrease indentation depthif it's not already zero */
    private void outdent() {
	if (depth > 0) depth--;
    }
    
    /** Stick the right number of blanks at the start of line */
    private void doIndentation() {
	line = StringOps.spaces(depth * IndentationIncrement);
    }
    
    /**
     * The visitNameSake methods: do the actual job of rendering
     * the parse tree (which represents the original XML file)
     * to the required format, here it's an indented plain text
     * with some frills like numbering items in an ordered list,
     * and bulleting items in an unordered list.
     * 
     * Block  elements insert a blank line after. Other elements
     * then know thatthey can start immediately. Inline elements 
     * do not insert anything before or after.
     * 
     * @param x The visited node of the parse tree whose
     * strategy determines the type of visit method.
     */    
    public void visitDocument(XmlContainerElement x) {
	Assert.require(x.getName().equals("office:document-content"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
    }	
    
    public void visitHeading(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:h"), 
		       "Visiting wrong node...");
	int level;
	if (x.attributes.has("text:level")) {
	    try {
		level = Integer.parseInt(x.attributes.at("text:level"));
	    } catch (NumberFormatException e) {
		System.err.print("level must be an integer, ");
		System.err.println("not " + e.toString());
		level = 2;
		System.err.println("Error: Heading without level!!");
	    }
	} else {
	    level = 2;
	    System.err.println("Error: Heading without level!!");
	}
	if (x.children.size() > 0) {
	    if  (level == 1)  upperCase = true;
	    x.visitChildren(this);
	    upperCase = false;
	    blankLine();
	}
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
		
		//iterate all footnotes
		if (!footnoteData.isEmpty()) {
			blankLine();
			while (!footnoteData.isEmpty()) {
				Footnote f = footnoteData.firstElement();
				footnoteData.remove(0);				
				addWord("[" + f.id + "] " + f.text);
				flushLine();
			}
		}
    }
    
    public void visitParagraph(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:p"), 
		       "Visiting wrong node...");
	if (x.children.size() > 0)
	    x.visitChildren(this);
	blankLine();
    }
    
    public void visitUnorderedList(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:unordered-list"), 
		       "Visiting wrong node...");
	x.visitChildren(this);
    }
    
    /** The item number of the last ordered list item */
    private int listItemNumber = 0;

    public void visitOrderedList(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:ordered-list"), 
		       "Visiting wrong node...");
	if (x.attributes.has("text:continue-numbering") &&
	    x.attributes.at("text:continue-numbering").equals("true")) {
	    System.out.println("Found list continuation!!");
	} else {
	    listItemNumber = 0;
	}
	x.visitChildren(this);
    }
    
    public void visitListItem(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:list-item"), 
		       "Visiting wrong node...");
	if (x.parent.getName().equals("text:unordered-list")) 
	    visitUnorderedListItem(x);
	else if (x.parent.getName().equals("text:ordered-list")) 
	    visitOrderedListItem(x);
	else x.visitChildren(this);
    }
    
    /**
     * Format an item in an unordered (bullet point) list:
     * indent one step but with "o " added in front of the first
     * line of the contents
     */
    public void visitUnorderedListItem(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:list-item"), 
		       "Visiting wrong node...");
	line += StringOps.spaces(IndentationIncrement - 2);
	line += "o ";
	lineIsEmpty = false;
	indent();
	x.visitChildren(this);
	outdent();
	doIndentation();
    }
    
    /**
     * Format an item in an ordered (numbered) list: indent one
     * step but with the number, a dot and a space in front of
     * the first line of the contents
     */
    public void visitOrderedListItem(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:list-item"), 
		       "Visiting wrong node...");
	String str;
	listItemNumber++;
	str = Integer.toString(listItemNumber);
	line += StringOps.spaces(IndentationIncrement - 2 - str.length()) + 
	    str + ". ";
	lineIsEmpty = false;
	indent();
	x.visitChildren(this);
	outdent();
	doIndentation();
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
	addWord(new String(" "));
    }
    
    public void visitLineBreak(XmlContainerElement x) {
	Assert.require(x.getName().equals("text:line-break"), 
		       "Visiting wrong node...");
	flushLine();
    }
    
    /** Visit an unspecified element */
    public void visitUnknown(XmlContainerElement x) {
	x.visitChildren(this);
    }
    
    /**
     * Format a data item
     * @param data The actual content, essentially a string
     */
    public void visitData(XmlDataElement data) {
	String content = new String(replaceEntities(data.content));
	String word = new String();
		
		if (data.isFootnote) {
			addWord("[" + lastFootnoteId + "]");
			Footnote f = new Footnote(lastFootnoteId, data.content);
			footnoteData.add(f);
			lastFootnoteId++;
		}
		else {
	for(int i = 0; i < content.length(); i++) {
	    if (content.charAt(i) == ' ' && word.length() > 0) {
		addWord(word);
		word = new String();
	    }
	    word += content.charAt(i);
	}
	if (word.length() > 0) {
	    addWord(word);
	}
    }
	}
    
    /**
     * Add a word to the end of the line. Eject the line and
     * start a new line if it doesn't fit. Word may have a
     * leading blank: keep it unless this word is the first in a
     * new line. But if it doesn't have a leading blank, don't
     * insert one.
     * 
     * @param w the word to be added
     */	
    private void addWord(String w) {
	Assert.require(w != null && w.length() != 0, 
		       "The word must exist and be nonempty");

	if (upperCase) {
	    w = w.toUpperCase();
	}
	
	// Trim leading white space off the first word in a line
	if (lineIsEmpty) {
	    //w = w.trim();
	    while (w.charAt(0) == ' ') {
		w = w.substring(1);
		if (w.length() == 0) break;
	    }
	}
	if (w.length() != 0) {
	    line += new String(w);
	    lineIsEmpty = false;
	}
	if (line.length() > rightMargin()) {
	    // go back to the last space and break the line there
			// dsw: wrong assumption that there might be a space. if there's no space, it should just break it on the right    
	    int i;
	    for (i = line.length()-1; i >= 0 && line.charAt(i) != ' '; i--) {
		// do nothing
	    }
			// dsw: i = -1 if no space in the long line
			if (i == -1) {
				i = rightMargin();
			}
	    String excess = line.substring(i+1);
	    line = line.substring(0, i);
	    flushLine();
	    line += excess;
	    if (excess.length() > 0) {
		lineIsEmpty = false;
	    }
	}
    }
    
    /* Auxiliary methods to modify the content */
    
    /** 
     * A lookup table (dictionary) of entities and multi-byte
     * characters and their replacement strings. Entities is
     * initialized to the required value (fixed set of key-value
     * pairs) in a static block to guarantee that it will be
     * hard-wired in the TextRenderer (a static block is
     * executed when the class is loaded in JVM, even before
     * constructor is executed) 
     */
    private static Hashtable entities = new Hashtable();
    
    static {
	entities.put("&lt;", "<");
	entities.put("&gt;", ">");
	entities.put("&amp;", "&");
	entities.put("&apos;", "'");
	entities.put("&quot;", "\"");
	entities.put("\u0194\u0169", "\u0169"); // Copyright symbol
    }
    
    /** 
     * Replace all entities. Look up keys in the hashtable and
     * replace with the corresponding value.
     *
     * @param str The string whose matching substrings will be replaced
     */
    private String replaceEntities(String str) {
	String n, s = new String(str);
	Enumeration e = entities.keys();
	if (e != null ) {
	    while (e.hasMoreElements()) {
		n = (String) e.nextElement();
		s = StringOps.replaceAll(s, n, (String) entities.get(n));
	    }
	}
	return s;
    }
    
}
