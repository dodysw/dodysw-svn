/**
 * Stack Version 1 from Lecture 7
 *   
 * @author Ian Barnes and Richard Walker
 * @version $Revision: 2005.3 $, $Date: 2005/03/13 23:34:09 $
 */

public interface Stack<A> {

    /** Is this stack empty? */
    public abstract boolean isEmpty();
    
    /** The top element */
    public abstract A top();
    //require !isEmpty()

    /** The result of removing the top element */
    public abstract Stack<A> pop();
    // require !isEmpty()

    /** The number of elements stored */
    public abstract int depth();
}
