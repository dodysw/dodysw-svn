/**
 * Homework 6
 * @author Dody Suria Wijaya
 *
 */
public class Words {
	private static String[] digits = {"zero", "one", "two", "three", "four", "five", "six", "seven", "eight", "nine"};
	private static String[] teens = {"ten", "eleven", "twelve", "thirteen", "fourteen", "fifteen", "sixteen", "seventeen", "eighteen", "nineteen"};
	private static String[] tens = {"", "", "twenty", "thirty", "forty", "fifty", "sixty", "seventy", "eighty", "ninety"};
	
	public static String threeDigitsAsWords(int n) {
		String s = "";
		if ((n / 100) > 0) {
			s += digits[n/100] + " hundred ";
			if (n%100 != 0) {
				s += "and ";
			}
			n -= (n/100)*100;
		}
		
		if (n > 0 && n < 10) {
			s += digits[n];
		}
		else if (n < 20) {
			s += teens[n-10];			
		}
		else {
			s += tens[n/10];
			if (n%10 != 0) {
				s += "-" + digits[n - (n/10)*10];
			}
		}		
		return s;
	}

	public static void main(String[] args) {
		int n;
		if (args.length == 1 && (n = Integer.parseInt(args[0])) > 0 && n < 1000) {
			System.out.println(threeDigitsAsWords(n));
		}
		else {
			System.out.println("Usage: words n (where 0 < n < 1000)");			
		}
		System.exit(0);
	}
}
