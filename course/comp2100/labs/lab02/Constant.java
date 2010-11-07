/**
 * Constant expressions
 */
public class Constant extends Expression {
	
	/**
	 * The value
	 */
	int value;	
	
	/**
	 * Initialise value
	 */
	public Constant(int v) {
		value = v;
	}

    public void accept(ExpressionVisitor visitor) {
    	visitor.visit(this);
    }
	
}
