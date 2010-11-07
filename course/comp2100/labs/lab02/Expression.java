/**
 * Arithmetic integer expressions
 */
public abstract class Expression {
	
	public abstract void accept(ExpressionVisitor visitor);
	
    public static final int NONE = 0;
    public static final int ADDITION = 1;
    public static final int MULTIPLICATION = 2;
    public static final int SUBTRACTION = 3;
    public static final int DIVISION = 4;

}
