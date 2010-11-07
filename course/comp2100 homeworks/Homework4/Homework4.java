/**
 * COMP2100/COMP6442 Homework #3
 * @author Dody Suria Wijaya <dodysw@gmail.com>
 * u4267771
 */

import java.lang.Math;

class Root {
	double a, b, c, x1, x2, j1, j2;
	boolean imaginary = false;
	Root (double p1, double p2, double p3) {
		assert p1 != 0;
		a = p1;
		b = p2;
		c = p3;
	}
		
	void calcRootComplex() {
		imaginary = true;
		double discriminant = b*b - 4*a*c;
		x1 = -b/(2*a);
		j1 = -Math.sqrt(Math.abs(discriminant))/(2*a);
		x2 = x1;
		j2 = -j1;		
	}
	
	void calcRoot() {
		double discriminant = b*b - 4*a*c;
		if (discriminant >= 0) {
			imaginary = false;
			x1 = -(b + Math.sqrt(discriminant))/(2*a);
			x2 = -(b - Math.sqrt(discriminant))/(2*a);			
		}
		else {
			//complex number result
			calcRootComplex();
		}
	}
	
	void calcRootAlternative() {
		assert c != 0;
		double discriminant = b*b - 4*a*c;
		if (discriminant >= 0) {
			imaginary = false;
			double q = (-b - Math.sqrt(discriminant));
			x1 = q/(2*a);
			x2 = (2*c)/q;
		}
		else {
			//complex number result, use normal complex calculation
			calcRootComplex();
		}
	}
	
	String getResult() {
		if (imaginary)
			return String.format("%s %s %s i, %s %s %s i", x2==0?"0.0":x2, j2<0? "-": "+", Math.abs(j2), x1==0?"0.0":x1, j1<0? "-": "+", Math.abs(j1));
		else
			return String.format("x = %s, x = %s", x2==0?"0.0":x2, x1==0?"0.0":x1);
	}
}

public class Homework4 {

	public static void main(String[] args) {
		Root r = new Root(Double.parseDouble(args[0]), Double.parseDouble(args[1]), Double.parseDouble(args[2]));		
		System.out.println(String.format("%sx^2 + %sx + %s = 0\n", args[0], args[1], args[2]));
		r.calcRoot();
		System.out.println("Normal formula:");
		System.out.println(r.getResult());
		r.calcRoot();
		System.out.println("Alternative formula:");
		System.out.println(r.getResult());		
	}

}
