public class StackFactory<A> {

    /** A brand new empty stack */
    public Stack<A> empty() {
    	return new EmptyStack<A>();
    }
    
    /** The result of a push */
    public Stack<A> push(A x, Stack<A> s) {
    	return new NonemptyStack<A>(x, s);
    }
}