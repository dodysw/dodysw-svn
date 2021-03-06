package comp2100.oops.scanner;

import java.io.IOException;
import java.io.InputStreamReader;
import java.io.PushbackReader;
import java.io.Reader;

/**
 * A Reader that passes its input straight through except for
 * XML processing instructions, which it removes. These look
 * like <tt>&lt;?...?&gt;</tt>.
 *
 * @author Ian Barnes
 * @version $Revision: 569 $, $Date: 2006-04-06 18:48:53 +1000 (Thu, 06 Apr 2006) $
 */
class ProcessingInstructionFilter extends Reader {

    /** The source we're reading characters from */
    private PushbackReader input;

    /** Initialise to read from the given reader */
    public ProcessingInstructionFilter(Reader r) {
	input = new PushbackReader(r, 3);
    }

    /** Read one character, skipping processing instructions if necessary */
    public int read() throws IOException {
	int c, d;
	c = input.read();
	if (c == '<') {
	    d = input.read();
	    if (d == '?') {
		// 3-state DFSA to find end of PI
		// State 1 = start
		// State 2 = seen "?"
		// State 3 = seen "?>" (end of PI)
		int state = 1;
		while (state != 3) {
		    c = input.read();
		    if (c == -1) {
			break;
		    }
		    switch (state) {
		    case 1:
			if (c == '?') {
			    state = 2;
			}
			break;
		    case 2:
			if (c == '>') {
			    state = 3;
			}
			else if (c == '?') {
			    state = 2; // no state change
			} else {
			    state = 1;
			}
			break;
		    }
		}
		c = input.read();
	    } else {
		// it's not a PI, go back
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
	Reader f = new ProcessingInstructionFilter(new InputStreamReader(System.in));
	try {
	    int c = f.read();
	    while (c != -1) {
		System.out.println(">>>> MAIN: '" + (char) c + "' ("
				   + c + ")");
		c = f.read();
	    }
	    System.out.println();
	} catch (IOException e) {
	    System.out.println("An IO exception occurred!");
	    System.exit(1);
	}
    }
}
