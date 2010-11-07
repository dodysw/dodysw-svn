/**
 * Products of two expressions
 */
public class Multiplication extends Expression {
	
	/**
	 * The expressions to be added.
	 */
	Expression left, right;
	
	/**
	 * Initialise left and right sides
	 */
	public Multiplication(Expression l, Expression r) {
		left = l;
		right = r;
	}
	
    public void accept(ExpressionVisitor visitor) {
    	visitor.visit(this);
    }
	
}
