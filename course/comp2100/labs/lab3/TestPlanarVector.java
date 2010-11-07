package comp2100.junit;

import junit.framework.*;

/** JUnit test class for PlanarVector class testing
 *
 * @author $Author: u4267771 $
 * @version $Revision: 17 $
 * $Date: 2006-03-29 18:42:17 +1100 (Wed, 29 Mar 2006) $
 * 
 *  */

public class TestPlanarVector extends TestCase {
 
 private Point p1, p2, p3, q1, q2, q3;
 private PlanarVector v, v1, v2, v3;
 
 private final double tolerance = 0.001;
 
 // don't need it for JUnit3.8 and later? (won't hurt...)
 public TestPlanarVector(String name) {
  super(name);
 }

 // Set up for Point test 
 public void setUp() {
  p1 = new Point(1, 1);
  p2 = new Point(1, 2);
  p3 = new Point(-2, 7);
  q1 = new Point(-1, 9);
  q2 = new Point(3, 0);
  q3 = new Point(0, 3);
  v  = new PlanarVector(new Point(0, 0));
  v1 = new PlanarVector(p1, p2);
  v2 = new PlanarVector(q2, q3);
 }
 
 public void testVector() {
  Assert.assertNotNull("What?! no vectors!?", v);
  Assert.assertNotNull("What?! no vectors!?", v1);
 }
 
 public void testLength() {
  Assert.assertEquals(0.0, v.length(), tolerance);
  Assert.assertEquals(Math.sqrt(18.0), v2.length(), tolerance);
 }

 public void testVectorEqual() {
  Assert.assertEquals(v1, new PlanarVector(new Point(1, 1), 
      new Point(1, 2)));
 }
 
 public void testNormal() {
 	assertEquals(v1.scalarProduct(v1.normal()), 0.0, tolerance);
 }

 public void testScalarProduct() {
	assertEquals(v1.scalarProduct(v2), v2.scalarProduct(v1), tolerance);
 }
 
 public void testAdd() {
 	assertEquals(v, v.add(v));
 	assertEquals(v1, v.add(v1));
 }
 
 public void testCentralized() {
 	assertEquals(v, v.centralised());
 	assertEquals(new PlanarVector(new Point(0,1)), v1.centralised());
 }
 
 public void testEquals() {
 	assertEquals(new PlanarVector(new Point(2,2), new Point(2,3)), new PlanarVector(new Point(5,0), new Point (5,1)));
 }

 public void testMovedTo() {
 	assertEquals(new PlanarVector(new Point(5,5), new Point(5,6)), v1.movedTo(new Point(5,5)));
 }

 public void testMovedOn() {
 	assertEquals(v1, v1.movedOn(v));
 }

 public void testInverse() {
 	assertEquals(new PlanarVector(q2, q3), (new PlanarVector(q3,q2)).inverse());
 }


 public void testAngle() {
 	assertEquals(Math.PI/2.0, v1.angle(), tolerance);
 }

 public void testAngleBetween() {
 	assertEquals(0.0, v1.angleBetween(v1), tolerance);
 }

 
}
