package comp2100.junit;

import junit.framework.Test;
import junit.framework.TestSuite;

/** 
 * TestSuite class for JUnit test.
 *
 * @author $Author: u4267771 $
 * @version $Revision: 15 $
 * $Date: 2006-03-29 15:10:19 +1100 (Wed, 29 Mar 2006) $
 */

public class Comp2100JunitTests {
 public static void main(String[] args) {
  junit.textui.TestRunner.run(suite());
 }

 public static Test suite() {
  TestSuite suite = new TestSuite("All Comp2100 JUnit Tests");
  TestSuite suite1 = new TestSuite("Triangle Tests");
  suite.addTest(new TestSuite(comp2100.junit.TestComplex.class));
  suite1.addTest(new TestSuite(comp2100.junit.TestTriangle.class));
  suite1.addTest(new TestSuite(comp2100.junit.TestPoint.class));
  suite1.addTest(new TestSuite(comp2100.junit.TestPlanarVector.class));
  suite.addTest(suite1);
  
  return suite;
 }
}
