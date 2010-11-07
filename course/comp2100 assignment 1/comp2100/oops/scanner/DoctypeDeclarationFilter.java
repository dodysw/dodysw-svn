package comp2100.oops.scanner;

import java.io.IOException;
import java.io.InputStreamReader;
import java.io.PushbackReader;
import java.io.Reader;

/**
 * A Reader that passes its input straight through except for
 * DOCTYPE declarations, which it removes. These look like:
 * <tt>&lt;!DOCTYPE ...&gt;</tt>.
 *
 * @author Ian Barnes
 * @version $Revision: 569 $, $Date: 2006-04-06 18:48:53 +1000 (Thu, 06 Apr 2006) $
 */
class DoctypeDeclarationFilter extends Reader {

    /** The source we're reading characters from */
    private PushbackReader input;

    /** Initialise to read from the given reader */
    public DoctypeDeclarationFilter(Reader r) {
	input = new PushbackReader(r, 10);
    }

    /** Read one character, skipping DoctypeDeclarations if necessary */
    public int read() throws IOException {
	int c, d, e, f, g, h, i, j, k;
	c = input.read();
	if (c == '<') {
	    d = input.read();
	    if (d == '!') {
		e = input.read();
		f = input.read();
		g = input.read();
		h = input.read();
		i = input.read();
		j = input.read();
		k = input.read();
		if (e == 'D' && f == 'O' && g == 'C' && h == 'T'
		    && i == 'Y' && j == 'P' && k == 'E') {
		    // Find end of DoctypeDeclaration
		    boolean done = false;
		    while (!done) {
			c = input.read();
			done = (c == '>' || c == -1);
		    }
		    c = input.read();
		} else {
		    // It's not a Doctype declaration, go back
		    input.unread(k);
		    input.unread(j);
		    input.unread(i);
		    input.unread(h);
		    input.unread(g);
		    input.unread(f);
		    input.unread(e);
		    input.unread(d);
		}
	    } else {
		// it's not a DoctypeDeclaration, go back
		input.unread(d);
	    }
	}
	return c;
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
	DoctypeDeclarationFilter f = new DoctypeDeclarationFilter(input);
	try {
	    int c = f.read();
	    while (c != -1) {
		System.out.println(">>>> MAIN: " + (char) c + " (" + c + ")");
		c = f.read();
	    }
	    System.out.println();
	} catch (IOException e) {
	    System.out.println("An IO exception occurred!");
	    System.exit(1);
	}
    }
}
