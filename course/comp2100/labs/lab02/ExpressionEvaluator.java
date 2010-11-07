
public class ExpressionEvaluator implements ExpressionVisitor {

	/**
	 * The value
	 */
	private int value;
    
	/**
	 * The value
	 */
	public int getValue() {
		return value;
	}

    /** Get the value of a constant expression */
    public void visit(Constant c) {
    	value = c.value;
    }
    
    /** Get the value of a sum */
    public void visit(Addition a) {
    	ExpressionEvaluator leftEvaluator = new ExpressionEvaluator();
    	ExpressionEvaluator rightEvaluator = new ExpressionEvaluator();
    	a.left.accept(leftEvaluator);
    	a.right.accept(rightEvaluator);
    	value = leftEvaluator.value + rightEvaluator.value;
    }

    /** Get the value of a product */
    public void visit(Multiplication a) {
		ExpressionEvaluator leftEvaluator = new ExpressionEvaluator();
		ExpressionEvaluator rightEvaluator = new ExpressionEvaluator();
		a.left.accept(leftEvaluator);
		a.right.accept(rightEvaluator);
		value = leftEvaluator.value * rightEvaluator.value;
    }

    /** Get the value of a difference */
    public void visit(Subtraction a) {
    	ExpressionEvaluator leftEvaluator = new ExpressionEvaluator();
    	ExpressionEvaluator rightEvaluator = new ExpressionEvaluator();
    	a.left.accept(leftEvaluator);
    	a.right.accept(rightEvaluator);
    	value = leftEvaluator.value - rightEvaluator.value;
    }

    /** Get the value of a quotient */
    public void visit(Division a) {
		ExpressionEvaluator leftEvaluator = new ExpressionEvaluator();
		ExpressionEvaluator rightEvaluator = new ExpressionEvaluator();
		a.left.accept(leftEvaluator);
		a.right.accept(rightEvaluator);
		value = leftEvaluator.value / rightEvaluator.value;
    }
    
}
