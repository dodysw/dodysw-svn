
public class EmptyStack<A> implements Stack<A> {
    public EmptyStack() {}
    public boolean isEmpty() { return true; }
    public Stack<A> pop() { return null; }
    public A top() { return null; }
    public int depth() { return 0; }
    public String toString() {return "";  }
}