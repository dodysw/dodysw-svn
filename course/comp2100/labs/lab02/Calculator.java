//import java.util.Scanner;
import java.io.*;

/**
 * A calculator that parses simple arithmetic expressions into a
 * binary tree structure and then traverses the tree to print it
 * in various forms or evaluate it.
 */
public class Calculator {
	
	/** Create an instance and let it run. */
	public static void main(String[] args) {
		Calculator calculator = new Calculator();
	}
	
	/* Constructor */
	
	/** Run the calculator. */
	public Calculator() {
		compiler = new Compiler();
		
		// Scanner input = new Scanner(System.in);
		BufferedReader input = 
			new BufferedReader(new InputStreamReader(System.in));
		
		instruct();
		System.out.print(prompt);
		
		// String line = input.nextLine().toLowerCase().trim();
		String line = "";
		try {
			line = input.readLine().toLowerCase().trim();
		} catch (IOException e) {
			System.exit(1);
		}
		
		while (!line.equals("q")) {
			
			if (line.length() > 0) {
				switch (line.charAt(0)) {
				case 'n':
					compiler.setInput(line.replaceFirst("n", "").trim());
					compiler.buildTree();
					break;
				case 'i':
					compiler.printInOrder();
					break;
				case 'v':
					compiler.printValue();
					break;
				case 'r':
					compiler.printPreorder();
					break;
				case 'o':
					compiler.printPostorder();
					break;

				case 'h':
					instruct();
					break;
				default:
					warn();
				break;
				}
			}
			System.out.print(prompt);
			// line = input.nextLine().toLowerCase().trim();
			try {
				line = input.readLine().toLowerCase().trim();
			} catch (IOException e) {
				System.exit(1);
			}
		}
	}
	
	/* Methods */
	
	/** Give instructions on how to use this thing. */
	void instruct() {
		System.out.println("Enter one of the following commands:");
		System.out.println("  n e - Enter a New expression");
		System.out.println("  i   - Print out the Inorder form");
		System.out.println("  o   - Print out the pOstorder form.");
		System.out.println("  r   - Print out the pReorder from.");
		System.out.println("  v   - Print out the Value.");
		System.out.println("  h   - View these instructions again.");
		System.out.println("  q   - Quit");
		System.out.println("Here e stands for an arithmetic expression");
		System.out.println("involving integers, parentheses and the four");
		System.out.println("arithmetic operations: +, -, * and /.  All");
		System.out.println("operations are applied to the last");
		System.out.println("expression entered.  You must enter an");
		System.out.println("expression before you can apply any operation.\n");
	}
	
	/** Warning on incorrect use. */
	void warn() {
		System.out.println("Your command was not in the correct format.");
		System.out.println("Use the h command for details.");
	}
	
	/* Fields */
	
	/** The prompt we will give the user. */
	static final String prompt = "> ";
	
	/** The expression processor. */
	private Compiler compiler;
}
