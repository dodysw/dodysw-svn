/**
 * Parsers for simple arithmetic expressions
 * 
 * @author Ian Barnes
 * @version $Revision: 2005.2 $, $Date: 2005/03/13 12:42:03 $
 */
public class Parser {
	
	/**
	 * The result of the last parse command
	 */
	private Expression rootNode;
	
	/**
	 * The result of the last parse command
	 *
	 * NOTE: This method should return a CLONE of rootNode in
	 * order to prevent clients from messing with the private
	 * field.
	 */
	public Expression getRootNode() {
		return rootNode;
	}
	
	/**
	 * Creation and initialization
	 */
	public Parser(Lexer l) {
		// require l != null
		lexer = l;
	}
	
	/**
	 * Parse an expression
	 */
	public void parseExpression() {
		Expression left = null;
		Expression right = null;
		int op;
		
		//System.out.println("parseExpression()");
		
		parseTerm();
		left = rootNode;
		
		switch (lexer.lastToken()) {
		case '+':
			op = Expression.ADDITION;
			break;
		case '*':
			op = Expression.MULTIPLICATION;
			break;
		case '-':
			op = Expression.SUBTRACTION;
			break;
		case '/':
			op = Expression.DIVISION;
			break;
		default:
			// There's no right hand side.
			op = Expression.NONE;
		break;
		}
		
		if (op != Expression.NONE) {
			// Eat the operation symbol
			lexer.getToken();
			parseTerm();
			right = rootNode;
		}
		
		switch (op) {
		case Expression.ADDITION:
			rootNode = new Addition(left, right);
			break;
		case Expression.MULTIPLICATION:
			rootNode = new Multiplication(left, right);
			break;
		case Expression.SUBTRACTION:
			rootNode = new Subtraction(left, right);
			break;
		case Expression.DIVISION:
			rootNode = new Division(left, right);
			break;

		default:
			// do nothing: the expression we want is there already
			break;
		}
	}
	
	/**
	 * Parse a term
	 */
	private void parseTerm() {
		
		//System.out.println("parseTerm()");
		
		if (lexer.lastToken() == '(') {
			// Eat the '('
			lexer.getToken();
			parseExpression();
			// Eat the ')', if it's there
			if (lexer.lastToken() == ')') {
				lexer.getToken();
			} else {
				System.out.println("Error: ')' expected in parseTerm()");
			}
		} else if (Character.isDigit(lexer.lastToken())) {
			// It's a constant
			parseConstant();
		} else {
			System.out.println("Error: digit or '(' expected in parseTerm()");
		}
	}
	
	/**
	 * Parse a constant
	 */
	private void parseConstant() {
		// require Character.isDigit(lexer.lastToken())
		
		int value = 0;
		
		//System.out.println("parseConstant()");
		
		value = Character.getNumericValue(lexer.lastToken());
		lexer.getToken();
		while (Character.isDigit(lexer.lastToken())) {
			value = value * 10 + Character.getNumericValue(lexer.lastToken());
			lexer.getToken();
		}
		
		rootNode = new Constant(value);
	}
	
	/**
	 * The lexical analyser
	 */
	private Lexer lexer; 
	
}
