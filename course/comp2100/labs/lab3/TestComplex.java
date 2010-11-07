package comp2100.junit;

import junit.framework.*;

/** JUnit test class for Complex class
 * 
 * @author $Author: u4267771 $
 * @version $Revision: 15 $
 * $Date: 2006-03-29 15:10:19 +1100 (Wed, 29 Mar 2006) $
 * 
 * */

public class TestComplex extends TestCase {
 
 // complex numbers for testing
 private Complex a, c, c1, c2, r, i;
 
 // don't need it for JUnit3.8 and later? (won't hurt...)
 public TestComplex(String name) {
  super(name);
 }

 // Set up for complex test 
 public void setUp() {
  a  = new Complex(1, 1);
  c1 = new Complex(1, 2);
  c2 = new Complex(-2, 7);
  c  = new Complex(-1, 9);
  r  = new Complex(3, 0);
  i  = new Complex(0, 3);
 }
 public void testComplex() {
  Assert.assertNotNull("No complex number exists.", c1);
 }

 public void testAddition() {
  Assert.assertEquals(c, c1.add(c2));
  Assert.assertEquals("Commutativity broken.",c1.add(c2), c2.add(c1));
 }

 public void testSubtraction() {
  Assert.assertEquals(new Complex(-3, 5), c2.subtract(c1));
  Assert.assertEquals(new Complex(0, 0), c.subtract(c));
 }

 public void testConjugate() {
  Assert.assertEquals(c, (c.conjugate()).conjugate());
  Assert.assertTrue(c1.arg() ==  -(c1.conjugate()).arg());
  Assert.assertTrue(c.arg() == (new Complex(c.getReal() * 10, 
      c.getImag() * 10)).arg());
 }

 public void testAbs() {
  Assert.assertTrue(c.abs() == (c.conjugate()).abs());
  Assert.assertTrue(i.abs() == r.abs());
  Assert.assertTrue(c.subtract(c).abs() == 0);
 }

 public void testArg() {
  Assert.assertTrue(a.arg() == 0.25 * Math.PI);
  Assert.assertTrue(r.arg() == 0.0);
  Assert.assertTrue(i.arg() == 0.5 * Math.PI);
 }
 
 public void testMultiplication() {
  Assert.assertEquals(c1.multiply(c2), c2.multiply(c1));
  Assert.assertTrue(r.multiply(i).getReal() != 0 ||
        i.multiply(r).getImag() != 0);
 }
}
