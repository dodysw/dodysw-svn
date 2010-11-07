/**
  Computer Labs 1
  Dody Suria Wijaya
  u4267771
*/

public class Year {
	private int value;

	Year (int y) {
		value = y;
	}

	int getValue() {
		return value;
	}

	boolean isLeapYear() {
		if (value % 4 == 0) {
			if (value % 100 == 0) {
				return value % 400 == 0;
			}
			return true;
		}
		return false;
	}

	public static void main (String args[]) {
		Year y = new Year(Integer.parseInt(args[0]));
		if (y.isLeapYear())
			System.out.println("Yes, " + y.getValue() + " is a leap year!"); 
		else
			System.out.println("No, " + y.getValue() + " is not a leap year!"); 
		System.exit(0);
	}
}
