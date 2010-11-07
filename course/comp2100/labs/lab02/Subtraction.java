/**
 * Sums of two expressions
 */
public class Subtraction extends Expression {
	
	/**
	 * The expressions to be added.
	 */
	Expression left, right;
	
	/**
	 * Initialise left and right sides
	 */
	public Subtraction (Expression l, Expression r) {
		left = l;
		right = r;
	}
	
    public void accept(ExpressionVisitor visitor) {
    	visitor.visit(this);
    }
	
}
