/*
 * COMP2100/COMP6442 Homework #2b
 * by Dody Suria Wijaya <dodysw@gmail.com>
 * u4267771
 */

import java.util.*;

class Factor {
    long n;
    String factors;
    
    Factor(long n) {
        this.n = n;
    }
    
    void calcFactors() {
        double factor = 2.0;
        long m = this.n;
        factors = "";
        int iter_num = 0;
        long t = 1;
        while (factor < m) {
        	iter_num += 1;
        	double d = m/factor;
            if (d == (long) d) {
                factors += (long) factor;
                t *= (long) factor;
                factors += " * ";
                m = (long) (m/factor);
                continue;
            }
           	factor += 1;            
        }
        factors += m;
        t *= m;
        if (t != this.n)
        	System.out.println("(normal) Error at n=" + this.n);
        	
        
        System.out.println("Iteration: " + iter_num);
    }

    void calcFactorsOptimized() {
        double factor = 2.0;
        long m = this.n;
        factors = "";
        int iter_num = 0;
        long t = 1;
        while (factor < m) {
        	iter_num += 1;
        	double d = m/factor;
            if (d == (long) d) {
                factors += (long) factor;
                t *= (long) factor;
                factors += " * ";
                m = (long) (m/factor);
                continue;
            }
            //searching factor in increasing order always find prime factor, and prime after 2 is always odd number  
            if (factor == 2)
            	factor += 1;            
            else
            	factor += 2;
            
            //consider n as prime if testing factor is >= n/3
            if (factor > m/factor)
            	break;
        }
        factors += m;
        t *= m;
        if (t != this.n)
        	System.out.println("(opt) Error at n=" + this.n);
        System.out.println("Optimized Iteration: " + iter_num);
    }
    
    String getFactors() {
        return factors;
    }    
}

public class HomeWork2 {
	public static void main(String[] args) {
		long n;
		Scanner sc = new Scanner(System.in);        
        System.out.print("> ");
        while ((n = sc.nextLong()) > 1) {        	
        	sc = new Scanner(System.in);
            Factor f = new Factor(n);
            f.calcFactors();
            f.calcFactorsOptimized();
            System.out.println(n + " = " + f.getFactors());
            System.out.print("> ");
        }
        System.out.println("Goodbye");
        System.exit(0);
	}
	
	public static void test() {
		boolean error = false;
        for (int n=0; n < 5000000; n++) {
            Factor f = new Factor(n);
//            f.calcFactors();
            f.calcFactorsOptimized();
        }
        if (!error)
        	System.out.println("success!");
        	

	}
}
