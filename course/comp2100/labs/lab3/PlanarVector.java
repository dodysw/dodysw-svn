package comp2100.junit;

/** 
 * The PlanarVector class to represent a vector on
 * the plane fixed to a point 
 *  
 * @author $Author: u4267771 $
 * @version $Revision: 15 $
 * $Date: 2006-03-29 15:10:19 +1100 (Wed, 29 Mar 2006) $
 */  

public class PlanarVector {
 
 private Point origin;
 private Point end;
 
 /** end point constructor */
 public PlanarVector(Point p) {
  
  this.origin = new Point();
  this.end = p;
 } 
 /** regular constructor */
 public PlanarVector(Point p1, Point p2) {
  
  this.origin = p1;
  this.end = p2;
 }
 
 /** a vector sum of this and a vector v */
 public PlanarVector add(PlanarVector v) {
  
  return new PlanarVector(origin, end.movedOn(v));
 } 
 /** getter methods for origin and end */

 public Point getOrigin() {
  return origin;
 }

 public Point getEnd() {
  return end;
 }
 
 /** vector's origin moved to the centre */
 public PlanarVector centralised() {
  
  Point ne = new Point(end.getX() - origin.getX(), 
        end.getY() - origin.getY());
  return new PlanarVector(ne);
 }
 
 /** the equals method overridden appropriately */
 public boolean equals(Object o) {
  if ( o instanceof PlanarVector) {
   return this.centralised().getEnd().equals(
    ((PlanarVector) o).centralised().getEnd());
  }
  return false;
 }
 
 /** vector's origin moved to the point p */
 public PlanarVector movedTo(Point p) {
  
  Point ne = p.movedOn(this.centralised());
  return new PlanarVector(p, ne);
 } 
 /** vector is parallel transported on vector v */
 public PlanarVector movedOn(PlanarVector v) {
  
  PlanarVector vc = v.centralised();
  Point no = new Point(origin.getX() - v.getEnd().getX(), 
        origin.getY() - v.getEnd().getY());
  Point ne = new Point(end.getX() - v.getEnd().getX(), 
        end.getY() - v.getEnd().getY());
  return new PlanarVector(no, ne);
 } 
 /** vector with origin and end points swapped */
 public PlanarVector inverse() {
  
  return new PlanarVector(this.end, this.origin);
 }
 
 /** length of this vector */
 public double length() {
  
  return this.origin.distance(this.end);
 }
 
 /** angle between this vector and the absciss axis */
 public double angle() {
  
  return Math.acos((end.getX() - origin.getX()) / length());
 }
 
 /** scalar product of this and vector v */
 public double scalarProduct(PlanarVector v) {
  
  return (centralised().end.getX() * v.centralised().end.getX()) +
          centralised().end.getY() * v.centralised().end.getY();
 }
 
 /** angle between the vector and the vector v */
 public double angleBetween(PlanarVector v) {
  
  double a = scalarProduct(v) / (length() * v.length());
  return Math.acos(a); 
 }
  
 /** vector normal to this one (right hand rotation) */
 public PlanarVector normal() {
 	
	 return new PlanarVector(origin, new Point(- end.getY(), end.getX()));
 }
 
 /** printable form of a PlanarVector object */
 public String toString() {
  
  return "Vector form " + origin.toString() + 
             " to " + end.toString();
 }
}
