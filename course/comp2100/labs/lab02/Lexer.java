/**
 * Lexical scanners for simple integer expressions
 *
 * Tokens in this case are just non-white-space characters, so
 * it's much simpler than the HTML scanner.
 */
public class Lexer {
	
	/** Last token read */
	public char lastToken() {
		return token;
	}
	
	/** Do we have something to read from? */
	public boolean isConnected() {
		return input != null;
	}
	
	/** Have we reached the end of the input? */
	public boolean endOfInput() {
		// require isConnected();
		return input.length() == 0;
	}
	
	/**
	 * Initialise to read from given string
	 * @param s The string to read from
	 */
	public void setInput(String s) {
		// require s != null;
		input = s;
		getToken();
	}
	
	/** Read the next token from the input */
	public void getToken() {
		// require isConnected()
		skipBlanks();
		if (input.length() == 0) {
			token = '\u0000';
		} else {
			token = input.charAt(0);
			input = input.substring(1);
		}
	}
	
	/**
	 * Skip over any whitespace, so that we're left looking at
	 * the next non-whitespace character.
	 */
	public void skipBlanks() {
		// require isConnected()
		input = input.trim();
		// ensure endOfInput() || !input.first.isWhitespace()
	}
	
	/* Fields */
	
	/** Last token read */
	private char token;
	
	/** Where we're reading from */
	private String input;
}
