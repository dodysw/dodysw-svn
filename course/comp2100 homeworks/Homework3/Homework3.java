/**
 * COMP2100/COMP6442 Homework #3
 * by Dody Suria Wijaya <dodysw@gmail.com>
 * u4267771
 */

import java.util.*;

class Average {
	ArrayList<Integer> al = new ArrayList<Integer>();
	
	
	void Add(Integer n) {
		al.add(n);
	}
	
	void Reset() {
		al.clear();
	}
	
	boolean isEmpty() {
		return al.isEmpty();
	}
	
	double GetAverage() {
		int v = 0;
		Iterator<Integer> iter = al.iterator();
		while (iter.hasNext())
			v += iter.next();
		return v / (double) al.size();
	}
	
	ArrayList<Integer> GetArray() {
		return al;
	}
	
	int GetSize() {
		return al.size();
	}
	
}

public class Homework3 {
	public static void main(String[] args) {
		Scanner sc = new Scanner(System.in);
		Scanner sc2;
		String line;
		Average avg = new Average();
		System.out.println("Please enter data values, one per line.");
		System.out.print("> ");
		while(sc.hasNextLine()) {
			line = sc.nextLine();
			sc2 = new Scanner(line);
			if (sc2.hasNextInt()) {
				int i = sc2.nextInt();
				avg.Add(i);
			}
			else {
				if (line.trim().length() == 0) {
					if (avg.isEmpty()) {
					}
					else {
						//calculate average, display, and reset
						System.out.println("Here is that data again:");
						Iterator iter = avg.GetArray().iterator();
						while (iter.hasNext())
							System.out.println(iter.next());
						System.out.println();
						System.out.println("N = " + avg.GetSize());
						System.out.println("Mean = " + avg.GetAverage());
						avg.Reset();
					}
				}
				else {
					System.out.println("Invalid input, try again.");
				}
			}
			System.out.print("> ");
		}
		
	}

}
