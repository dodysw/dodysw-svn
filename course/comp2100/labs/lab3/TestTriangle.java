package comp2100.junit;

import junit.framework.*;

/**
 * JUnit test class for Triangle class.
 *
 * @author $Author: u4267771 $
 * @version $Revision: 15 $
 * $Date: 2006-03-29 15:10:19 +1100 (Wed, 29 Mar 2006) $
 *
 * */

public class TestTriangle extends TestCase {

 private final double tolerance = 0.001;

 private Point p1, p2, p3, q1, q2, q3;
 private Triangle t, t1, t2, t3, t4;

 // don't need it for JUnit3.8 and later? (won't hurt...)
 public TestTriangle(String name) {
  super(name);
 }

 // Set up for Point test
 protected void setUp() {
  p1 = new Point(1, 1);
  p2 = new Point(1, 2);
  p3 = new Point(-2, 7);
  q1 = new Point(-1, 9);
  q2 = new Point(3, 0);
  q3 = new Point(0, 3);
  t  = new Triangle(q3, p2, new Point());
  t1 = new Triangle(p1, p2);
  t2 = new Triangle(q1, q2, q3);
  t3 = new Triangle(q2, q3, q1);
  t4 = new Triangle(q3, q1, q2);
 }

 public void testCreateTriangle() {
  Assert.assertNotNull("What?! no triangles!?", t1);
 }

 public void testArea() {
  Assert.assertEquals(4.5 ,(new Triangle(q2, q3)).area(), tolerance);
 }

 public void testEquals() {
  assertEquals(t1, new Triangle(new Point(0, 0), p1, p2));
  assertEquals("A is NOT A? Amazing!",t2, new Triangle(q3, q1, q2));
 }

 public void testSimilarity() {

  Triangle tr = new Triangle(new Point(-1, -1),
           new Point(1, 1),
           new Point(1, 3));
  assertTrue("Scaling and shifting does not change similarity, does it?",
       t1.isSimilar(tr));
  assertTrue(t2.isSimilar(t3));
  assertTrue(t2.isSimilar(new Triangle(q3, q1, q2)));

 }

 public void testAngles() {
  assertEquals("This can't be!", t4.smallestAngle(),
      t3.smallestAngle(), tolerance);
  assertEquals(t3.largestAngle(), t4.largestAngle(), tolerance);
  assertEquals(t2.smallestAngle(), t3.smallestAngle(), tolerance);
  assertEquals(t2.largestAngle(), t3.largestAngle(), tolerance);
  assertEquals(t.largestAngle(),
      (new Triangle(new Point(0, 9),
           new Point(3, 6))).largestAngle(), tolerance);
  assertEquals((new Triangle(new Point(5, 0), new Point(-5, 0))).
      smallestAngle(), 0.0, tolerance);
 }

 public void testDegeneracy() {
  assertFalse(t.isDegenerate());
  assertTrue(new Triangle(new Point(5, 0), new Point(-5, 0)).isDegenerate());
 }

 public void testSharpestVertex() {
  assertEquals(t1.sharpestVertex(), new Point());

 }

 public void testCongruence() {

 }

 public void testInside() {
	 assertTrue("This point isn't inside", (new Triangle(q2, q3)).isInside(p1));
	 assertFalse((new Triangle(q2, q3)).isInside(p3));
     assertTrue( (new Triangle(new Point(0,0), new Point(1,0), new Point(0,1))).isInside(new Point(0,1)));
     assertTrue( (new Triangle(new Point(0,0), new Point(1,0), new Point(0,1))).isInside(new Point(1,0)));

     assertFalse( (new Triangle(new Point(0,0), new Point(1,0), new Point(0,1))).isInside(new Point(1,1))); //fail
     assertFalse( (new Triangle(new Point(0,0), new Point(1,0), new Point(0,1))).isInside(new Point(2,0)));
     assertFalse( (new Triangle(new Point(0,0), new Point(1,0), new Point(0,1))).isInside(new Point(0,5)));

    //degenereted triangle
     assertTrue( (new Triangle(new Point(0,0), new Point(0,5), new Point(0,10))).isInside(new Point(0,0)));
     assertTrue( (new Triangle(new Point(0,0), new Point(0,5), new Point(0,10))).isInside(new Point(0,1)));
     assertTrue( (new Triangle(new Point(0,0), new Point(0,5), new Point(0,10))).isInside(new Point(0,10))); //fail
     assertFalse( (new Triangle(new Point(0,0), new Point(0,5), new Point(0,10))).isInside(new Point(0,11)));
     assertFalse( (new Triangle(new Point(0,0), new Point(0,5), new Point(0,10))).isInside(new Point(1,5)));

 }

}
