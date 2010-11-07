/**
 * String representation in in-order notation
 */
public class ExpressionFormatter implements ExpressionVisitor {
	
    /** The formatted string being built  */
    private String string;
    
    /** Public access to the string */
    public String getString() { return string; }

    /** Initialise with an empty string */
    public ExpressionFormatter() {
    	string = "";
    }

	public void visit(Constant c) {
		string +=  c.value;
	}
    
	public void visit(Addition a) {
		string +=  "(";
		a.left.accept(this);
		string +=  " + ";
		a.right.accept(this);
		string += ")";
	}
	
	public void visit(Multiplication a) {
		string +=  "(";
		a.left.accept(this);
		string +=  " * ";
		a.right.accept(this);
		string += ")";
	}
	
	public void visit(Subtraction a) {
		string +=  "(";
		a.left.accept(this);
		string +=  " - ";
		a.right.accept(this);
		string += ")";
	}
	
	public void visit(Division a) {
		string +=  "(";
		a.left.accept(this);
		string +=  " / ";
		a.right.accept(this);
		string += ")";
	}

}