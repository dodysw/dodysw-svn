/**
 * String representation in post-order notation
 */
public class ExpressionPostorder implements ExpressionVisitor {
	
    /** The formatted string being built  */
    private String string;
    
    /** Public access to the string */
    public String getString() { return string; }

    /** Initialise with an empty string */
    public ExpressionPostorder() {
    	string = "";
    }

	public void visit(Constant c) {
		string += c.value;
	}
    
	public void visit(Addition a) {		
		a.left.accept(this);
		string += " ";
		a.right.accept(this);
		string += " +";
	}
	
	public void visit(Multiplication a) {
		a.left.accept(this);
		string += " ";
		a.right.accept(this);
		string += " *";
	}
	
	public void visit(Subtraction a) {
		a.left.accept(this);
		string += " ";
		a.right.accept(this);
		string += " -";
	}
	
	public void visit(Division a) {
		a.left.accept(this);
		string += " ";
		a.right.accept(this);
		string += " /";
	}

}