
/**
 * Expression processors that hide the details of
 * scanning, parsing, trees etc. from the client, and
 * present a nice interface to it all.  (This is an
 * example of the Facade pattern.)
 */

public class Compiler {
	
	/**
	 * Get everything set up
	 */
	public Compiler() {
		lexer = new Lexer();
		parser = new Parser(lexer);
	}
	
	/**
	 * Has the input stream been correctly parsed?
	 */
	public boolean hasValidTree() {
		return (tree() != null);
	}
	
	/**
	 * Do we have an input stream ready to read?
	 */
	public boolean readyToGo() {
		return lexer.isConnected() && !lexer.endOfInput();
	}
	
	/**
	 * Set it to read from `s'.
	 */   
	public void setInput(String s) {
		// require s != null
		lexer.setInput(s);
	}
	/**
	 * Build an Abstract Parse Tree from the tokens on lexer
	 */
	public void buildTree() {
		// require readyToGo
		parser.parseExpression();
	}
	
	/*
	 * Print the expression in "in_order".
	 */
	public void printInOrder() {
		// require hasValidTree
		System.out.print("In-order: ");
		ExpressionFormatter f = new ExpressionFormatter();
		tree().accept(f);
		System.out.println(f.getString());
	}
	
	/**
	 * Print the value of the expression.
	 */
	public void printValue() {
		// require hasValidTree
		try {
			System.out.print("Value = ");
			ExpressionEvaluator v = new ExpressionEvaluator();
			tree().accept(v);
			System.out.println(v.getValue());
		}
		catch (ArithmeticException e) {
			System.out.println("Error: arithmetic problem = " + e);
		}
	}
	
	/**
	 * Print the value of the expression pre-orderly.
	 */
	public void printPreorder() {
		System.out.print("Pre-order: ");
		ExpressionPreorder f = new ExpressionPreorder();
		tree().accept(f);
		System.out.println(f.getString());
	}
	
	/**
	 * Print the value of the expression postorderly.
	 */
	public void printPostorder() {
		System.out.print("Post-order: ");
		ExpressionPostorder f = new ExpressionPostorder();
		tree().accept(f);
		System.out.println(f.getString());
	}
	
	/**
	 * Lexical scanner
	 */
	private Lexer lexer;
	
	/**
	 * Top-down parser
	 */
	private Parser parser;
	
	/**
	 * Root of the expression tree.
	 */
	public Expression tree() {
		if (parser != null) {
			return parser.getRootNode();
		} else {
			return null;
		}
	}
}
