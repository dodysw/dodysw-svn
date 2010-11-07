package comp2100.junit;

/** The Point class to represent a planar point and
  * some useful things you can do with is. 
  * 
  *  @author $Author: u4267771 $
  *  @version $Revision: 15 $
  *  $Date: 2006-03-29 15:10:19 +1100 (Wed, 29 Mar 2006) $
  *  
  *  */

public class Point {
 
 private double x;
 private double y;
 
 /** constructor for the (0,0) */
 public Point() {
 
  this.x = 0.0;
  this.y = 0.0;
 }
 
 /** regular constructor */
 public Point(double x, double y) {
  
  this.x = x;
  this.y = y;
 }
 
 /** point in coordinates relative to the point p
   * (the centre of coordinate frame moved to p) */
 public Point relativeTo(Point p) {
  
  return new Point(this.x - p.getX(), this.y - p.getY());
 }
 
 /** point moved on the vector v */
 public Point movedOn(PlanarVector v) {
  
  return new Point(this.x + v.centralised().getEnd().x, 
     this.y + v.centralised().getEnd().y);
 }
 
 /** standard getter methods for point coordinates */
 public double getX() {
  return this.x;
 }
 
 public double getY() {
  return this.y;
 }
 
 /** equals test for this and Point r */
 public boolean equals(Object o) {
  //return true;
  if (o instanceof Point) {
   Point p = (Point)o;
   return (this.x == p.getX()) &&
    (this.y == p.getY());
  }
  return false;
 }
 
 /** overriding hashCode() since equals() was overriddeen */
 
 public int hashCode() {

  return (int)(this.x * this.y);
 } 
 
 /** distance from this Point to another Point p */
 public double distance(Point p) {
  
  double xdist = this.x - p.getX();
  double ydist = this.y - p.getY();
  
  return Math.sqrt((xdist * xdist) + (ydist * ydist));
 }
 
 public String toString() {
  
  return "Point (" + x + "," + y + ")";
 }
 
 
}
