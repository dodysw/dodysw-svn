/*
 * Created on 21/03/2006
 *
 * Dody Suria Wijaya - u4267771 - dodysw@gmail.com
 * Homework #5
 */

import java.io.File;
import java.io.FileNotFoundException;
import java.util.*;

public class LineCount {
	private String filename = "";
	private Scanner scanner;
	public int loc = 0;
	
	LineCount (String s) throws FileNotFoundException{
		filename = s;
		scanner = new Scanner(new File(s));
		processFile();
	}
	
	void processFile() {
		while (scanner.hasNextLine()) {
			String line = scanner.nextLine();			
			String newline = line.replaceAll("/\\*\\*|/\\*|\\*/|\\*|//|[ \t\\{\\}]+", "");
			if  (newline.trim().length() != 0) {
				loc++;
			}
		}
	}

	public static void main(String[] args) {
		if (args.length == 0) {
			System.out.println("ERROR: You need to specify at least one file.");
			System.exit(0);
		}
		int total_loc = 0;
		int total_files = 0;
		for (int i=0; i < args.length; i++) {
			try {
				LineCount lc = new LineCount(args[i]);
				total_files++;
				total_loc += lc.loc;
				System.out.printf("%1$4d %2$s\n", new Integer(lc.loc), args[i]);
				
			}
			catch (FileNotFoundException e) {
				System.out.println("ERROR: counld't open " + args[i]);
				continue;
			}						
		}
		if (total_files > 1) {
			System.out.println("\n" + total_files + " classes, " + total_loc + " lines of code");
		}
		
		
	}
}
