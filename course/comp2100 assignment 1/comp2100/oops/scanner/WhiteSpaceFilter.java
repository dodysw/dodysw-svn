package comp2100.oops.scanner;

import java.io.IOException;
import java.io.InputStreamReader;
import java.io.Reader;

/**
 * A Reader that passes its input straight through except for
 * white space, which it normalises. More specifically it
 * transforms whitespace by replacing each sequence of
 * whitespace characters (as determined by the
 * java.lang.Character.isWhitespace() function) with a single
 * space character.
 * @author Ian Barnes
 * @version $Revision: 569 $, $Date: 2006-04-06 18:48:53 +1000 (Thu, 06 Apr 2006) $
 */
class WhiteSpaceFilter extends Reader {

    /** The source we're reading characters from */
    private Reader input;

    /**
     * False if the last character we encountered was
     * whitespace, true otherwise
     */
    private boolean inWord = true;

    /** Initialise to read from the given reader */
    public WhiteSpaceFilter(Reader r) {
	input = r;
    }

    /** Read one character, skipping white space if necessary */
    public int read() throws IOException {
	int c = input.read();
	if (!inWord) { // Skip ahead to first non-whitespace character
	    while (Character.isWhitespace((char) c)) {
		c = input.read();
	    }
	}
	if (Character.isWhitespace((char) c)) {
	    inWord = false;
	    return ' ';
	} else {
	    inWord = true;
	    return c;
	}
    }

    /** Read characters into a buffer */
    public int read(char[] cbuf, int off, int len) throws IOException {
	for (int i = off; i < off + len; i++) {
	    int c = read();
	    if (c == -1) {
		return -1;
	    } else {
		cbuf[i] = (char) c;
	    }
	}
	return len;
    }

    /**
     * Close the input. There's probably more things I should do
     * here to ensure that this class really conforms properly,
     * but since I never plan to have anyone call
     * <tt>close()</tt> on one of these, I can't be bothered.
     */
    public void close() throws IOException {
	input.close();
    }

    /**
     * Main method for testing. Read from <tt>System.in</tt> and
     * write to <tt>System.out</tt>. A bit baffling unless you
     * redirect input.
     */
    public static void main(String[] args) {
	Reader input = new InputStreamReader(System.in);
	WhiteSpaceFilter f = 
	    new WhiteSpaceFilter(
	    new DoctypeDeclarationFilter(
            new CommentFilter(
            new ProcessingInstructionFilter(input))));
	try {
	    int c = f.read();
	    while (c != -1) {
		System.out.println("MAIN: '" + (char) c + "'");
		c = f.read();
	    }
	    System.out.println();
	} catch (IOException e) {
	    System.out.println("An IO exception occurred!");
	    System.exit(1);
	}
    }
}
