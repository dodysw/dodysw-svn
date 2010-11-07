package comp2100.oops;

/**
 * A simple assertion checking mechanism.
 *
 * @author Jim Grundy (original version, around 1999)
 * @author Ian Barnes (added require and ensure)
 * @version $Revision: 569 $, $Date: 2006-04-06 18:48:53 +1000 (Thu, 06 Apr 2006) $
 */
public class Assert {
    
    /**
     * Check an assertion.
     *
     * If the assertion holds, execution continues, otherwise
     * execution halts and the message is printed followed by a
     * stack trace.
     * 
     * @param assertion The assertion to check (a boolean
     * expression)
     * @param message The message to print if the assertion
     * fails
     */
    public static void check(boolean assertion, String message) {
	if (!assertion) {
	    System.err.println("ASSERTION VIOLATED: " + message);
	    Thread.currentThread().dumpStack();
	    System.exit(1);
	}
    }
    
    /**
     * Check a precondition. Details as for
     * @see #check(boolean, String)
     */
    public static void require(boolean assertion, String message) {
	if (!assertion) {
	    System.err.println("Precondition violated: " + message);
	    Thread.currentThread().dumpStack();
	    System.exit(1);
	}
    }
    
    /**
     * Check a postcondition. Details as for
     * @see #check(boolean, String)
     */
    public static void ensure(boolean assertion, String message) {
	if (!assertion) {
	    System.err.println("Postcondition violated: " + message);
	    Thread.currentThread().dumpStack();
	    System.exit(1);
	}
    }
}
