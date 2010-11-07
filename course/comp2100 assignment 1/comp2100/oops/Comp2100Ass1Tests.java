package comp2100.oops;

import java.io.IOException;
import java.util.Enumeration;

import junit.framework.TestResult;
import junit.framework.TestSuite;
import junit.framework.TestFailure;

import comp2100.oops.scanner.TestScanner;

/**
 * TestSuite runner
 * 
 * @author Modified by $Author: u4267771 $
 * @version $Rev: 569 $
 * $Date: 2006-04-06 18:48:53 +1000 (Thu, 06 Apr 2006) $
 */


class Comp2100Ass1Tests {

	public static void main(String[] args) throws IOException {
		Comp2100Ass1Tests main = new Comp2100Ass1Tests();
		main.suite();
	}
	
	void suite() {
		TestSuite testSuite = new TestSuite(TestScanner.class);
		TestResult result = new TestResult();
		testSuite.run(result);
		if (result.wasSuccessful()) {
			System.out.println("Success.");
		}
		else {
			System.out.println("Error:" + result.errorCount() + " | Fail:" + result.failureCount());
			for (Enumeration<TestFailure> e = result.errors(); e.hasMoreElements();) {
				System.out.println(e.nextElement().toString());
			}
			for (Enumeration<TestFailure> e = result.failures(); e.hasMoreElements();) {
				System.out.println(e.nextElement().toString());
			}				
		}
	}
}