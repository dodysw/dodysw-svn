package comp2100.oops.scanner;

import java.io.FileReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.Reader;

import comp2100.oops.Assert;

/** 
 * Lexical analyser for the Open Office XML processor. Pretty
 * basic: only knows about tags and data (everything else?)
 * 
 * @author Ian Barnes (original Eiffel code)
 * @author Alexei B Khorev (Java port)
 * @author Rewritten by Ian Barnes
 * @version $Revision: 569 $, $Date: 2006-04-06 18:48:53 +1000 (Thu, 06 Apr 2006) $
 */
public class Scanner {
	
    /** Where we're reading from */
    private Reader input;

    /** Initialise to feed on the given Reader */
    public Scanner(Reader r) throws IOException {
	input = new WhiteSpaceFilter(
                new CommentFilter(
                new ProcessingInstructionFilter(
                new DoctypeDeclarationFilter(r))));
	// skip leading blanks
	eatOne();
	while (c == ' ') {
	    eatOne();
	}
	// parse the first token
	advance();
    }
    
    /** the last token read */
    private Token currentToken;
    
    /** the last token read */
    public Token item() {
	return currentToken; 
    }
        
    /** The last character read */
    private char c;

    /** Read one character from input into c */
    private void eatOne() {
	try {
	    int n = input.read();
	    if (n == -1) {
		eof = true;
	    }
	    c = (char) n;
	} catch (IOException e) {
	    System.out.println("IOException in Scanner.eatOne()");
	    System.exit(1);
	}
    }
	
    /** move forward one token */
    public void advance() {
	if  (c == '<') {
	    addTag();
	} else {
	    addData();
	}
    }
    
    private boolean eof = false;
    
    /** Have we reached end of file? */
    public boolean endOfInput() {
	return eof;
    }
    
    /**
     * Parse a tag: extract name, attributes, create tag object,
     * assign it to currentToken.
     */
    void addTag() {
	// Move past '<' to next character
	Assert.check(c == '<', "Expected '<', saw '" + c + "' instead");
	eatOne();
	
	// If first character is '/', it's an end tag
	boolean endTag = false;
	if (c == '/') {
	    endTag = true;
	    eatOne();
	}
	
	// Get the name (everything up to the next space, '/' or '>')
	String name = new String();
	while (!eof && c != ' ' && c != '/' && c != '>') {
	    name += c;
	    eatOne();
	}
	Tag tag = new Tag(name);
	if (endTag) {
	    tag.setEnd();
	}
	
	// Parse attributes
	while (!eof && c != '/' && c != '>') {
	    // Skip over the space
	    Assert.check(c == ' ', "Expected ' ', saw '" + c + "' instead");
	    eatOne();
	    
	    // Get the attribute name
	    String attributeName = new String();
	    while (!eof && c != '=') {
		attributeName += c;
		eatOne();
	    }
	    // Skip the '='
	    Assert.check(c == '=', "Expected '=', saw '" + c + "' instead");
	    eatOne();
	    // Skip the '"'
	    Assert.check(c == '\"', "Expected '\"', saw '" + c + "' instead");
	    eatOne();
	    
	    // Get the attribute value
	    String attributeValue = new String();
	    while (!eof && c != '\"') {
		attributeValue += c;
		eatOne();
	    }
	    // Skip the '"'
	    Assert.check(c == '\"', "Expected '\"', saw '" + c + "' instead");
	    eatOne();
	    
	    tag.addAttribute(attributeName, attributeValue); 
	}
	if (c == '/') {
	    tag.setEmpty();
	    eatOne();
	}
	// Skip the closing '>'
	Assert.check(c == '>', "Expected '>', saw '" + c + "' instead");
	eatOne();
	currentToken = tag;
    }
    
    /**
     * Read content from input into a new Data token. Stop when
     * you hit the start of the next tag.
     */
    void addData() {
	String s = new String();
	while (!eof && c != '<') {
	    s += c;
	    eatOne();
	}
	currentToken = new Data(s);
    }
    
    /**
     * The main method for testing. Reads from a sample file (if
     * the file name is provided on the command-line), or from
     * from stdin (if no command-line argument is given).
     */	
    public final static void main(String[] args) throws IOException {
	Reader input;
	if (args.length == 0) {
	    input = new InputStreamReader(System.in);
	} else {
	    input = new FileReader(args[0]);
	}
	Scanner scanner = new Scanner(input);
	while (!scanner.endOfInput()) {
	    scanner.advance();
	    System.out.println(scanner.item().toString());
	}
    }
}
