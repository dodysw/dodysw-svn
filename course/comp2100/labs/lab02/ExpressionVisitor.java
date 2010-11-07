
public interface ExpressionVisitor {
    public abstract void visit(Constant c);

    public abstract void visit(Addition a);

    public abstract void visit(Multiplication m);
    
    public abstract void visit(Division m);
    
    public abstract void visit(Subtraction m);

}
