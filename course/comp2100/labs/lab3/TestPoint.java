package comp2100.junit;

import junit.framework.*;

/** JUnit test clas for testing Poit class
 *
 * @author $Author: u4267771 $
 * @version $Revision: 16 $
 * $Date: 2006-03-29 15:57:43 +1100 (Wed, 29 Mar 2006) $
 *
 *  */

public class TestPoint extends TestCase {
	
	private Point p, p1, p2, p3;
	
	// don't need it for JUnit3.8 and later? (won't hurt...)
	public TestPoint(String name) {
		super(name);
	}
	
	// Set up for Point test
	public void setUp() {
		p  = new Point();
		p1 = new Point(1, 1);
		p2 = new Point(1, 2);
		p3 = new Point(-2, 7);
	}
	
	public void testPointEqual() {
		Assert.assertNotNull("What?! no points!?", p1);
		Assert.assertNotNull("What?! no points!?", p);
		Assert.assertEquals(p, new Point());
	}
	
	public void testRelativeTo() {
		assertEquals(new Point(0,-1), p1.relativeTo(p2));
		assertEquals(p2.relativeTo(p1), new Point(0,1));
	}
	
	public void testMovedOn() {		
		assertEquals(new Point(5,5), p.movedOn(new PlanarVector(new Point(5,5))));
		assertEquals(new Point(6,6), p1.movedOn(new PlanarVector(new Point(5,5))));
		assertEquals(new Point(1,1), p1.movedOn(new PlanarVector(new Point(0,0))));
		assertEquals(new Point(), p2.movedOn(new PlanarVector(new Point(-1,-2))));
		assertEquals(new Point(), p3.movedOn(new PlanarVector(new Point(2,-7))));
	}
	
	public void testEquals() {
		assertTrue(p.equals(new Point(0,0)));
		assertTrue(p1.equals(new Point(1,1)));
		assertTrue(p2.equals(new Point(1,2)));
		assertTrue(p3.equals(new Point(-2, 7)));
	}
	
	public void testDistance() {
		assertEquals(0.0, p.distance(p));
		assertEquals(1.0, p.distance(new Point(0,1)));
		assertEquals(1.0, p.distance(new Point(1,0)));
		assertEquals(1.0, p.distance(new Point(-1,0)));
	}
}
