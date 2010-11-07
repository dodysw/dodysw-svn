/**
 * A simple reverse Polish calculator.
 *
 * Modified to use Stack Version 1 from Lecture 7.
 *
 * @author Jim Grundy, Ian Barnes and Richard Walker
 * @version $Revision: 2005.4 $, $Date: 2005/03/13 23:33:32 $
 */

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.io.IOException;

public class Calculator {

    /** Run the calculator */
    public static void main(String[] args) throws IOException {
    	StackFactory<Integer> sf = new StackFactory<Integer>();
	    Stack<Integer> stack = sf.empty();

	    // Make it suitable for Java 1.4 or 1.5 - no Scanner
    	BufferedReader in =
	    new BufferedReader(new InputStreamReader(System.in));

        String line = in.readLine().trim();
        while (line != null && !line.equals("q")) {
            if (line.equals("t")) {
            	// Print the number on top of the stack.
            	System.out.println(((Integer) stack.top()).toString());
            } else if (line.equals("+")) {
                // Add the top two elements.
                if (!stack.isEmpty()) {
                    int x = ((Integer) stack.top()).intValue();
                    stack = stack.pop();
                    if (!stack.isEmpty()) {
                        int y = ((Integer) stack.top()).intValue();
                        stack = stack.pop();
                        stack = sf.push(new Integer(x + y), stack);
                    } else {
                        System.out.println("ERROR: Insufficient arguments");
                        stack = sf.push(new Integer(x), stack);
                    }
                } else {
                    System.out.println("ERROR: Insufficient arguments");
                }
            } else if (line.equals("*")) {
            // Multiply the top two elements.
                if (!stack.isEmpty()) {
                    int x = ((Integer) stack.top()).intValue();
                    stack = stack.pop();
                    if (!stack.isEmpty()) {
                        int y = ((Integer) stack.top()).intValue();
                        stack = stack.pop();
                        stack = sf.push(new Integer(x * y), stack);
                    } else {
                        System.out.println("ERROR: Insufficient arguments");
                        stack = sf.push(new Integer(x), stack);
                    }
                } else {
                    System.out.println("ERROR: Insufficient arguments");
                }
            } else if (line.equals("p")) {
            	//print stack
            	System.out.println(stack.toString());
            } else if (line.equals("r")) {
            	//reverse stack order
            	Stack<Integer> s2 = sf.empty();
            	while (!stack.isEmpty()) {
            		s2 = sf.push(stack.top(), s2);
            		stack = stack.pop();
            	}
            	stack = s2;
            } else { // It's a number to push?
                try {
                    stack = sf.push(new Integer(line), stack);
                }
                catch (NumberFormatException e) {
                    // It's not a number or a known command
                    System.out.println("Error: Unknown Request: " + line);
                }
            }
            line = in.readLine().trim();
        }
    }
}
