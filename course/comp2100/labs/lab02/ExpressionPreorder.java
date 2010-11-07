/**
 * String representation in pre-order notation
 */
public class ExpressionPreorder implements ExpressionVisitor {
	
    /** The formatted string being built  */
    private String string;
    
    /** Public access to the string */
    public String getString() { return string; }

    /** Initialise with an empty string */
    public ExpressionPreorder() {
    	string = "";
    }

	public void visit(Constant c) {
		string += c.value;
	}
    
	public void visit(Addition a) {
		string +=  "sum(";
		a.left.accept(this);
		string +=  ",";
		a.right.accept(this);
		string += ")";
	}
	
	public void visit(Multiplication a) {
		string +=  "product(";
		a.left.accept(this);
		string +=  ",";
		a.right.accept(this);
		string += ")";
	}
	
	public void visit(Subtraction a) {
		string +=  "difference(";
		a.left.accept(this);
		string +=  ",";
		a.right.accept(this);
		string += ")";
	}
	
	public void visit(Division a) {
		string +=  "quotient(";
		a.left.accept(this);
		string +=  ",";
		a.right.accept(this);
		string += ")";
	}

}