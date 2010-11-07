package comp2100.junit;

/** The Triangle class to represent a planar triangle and
  * some useful things you can do with it. 
  * 
  * @author $Author: u4267771 $
  * @version $Revision: 16 $
  * $Date: 2006-03-29 15:57:43 +1100 (Wed, 29 Mar 2006) $
  * 
  * */

public class Triangle {
 
 /** tolerance for comparing and checking equality */
 private final double tolerance = 0.001; 
 
 /** triangle vertices set by constructor */
 private Point vertex1;
 private Point vertex2;
 private Point vertex3;
 
 /** triangle sides determined in constructor */
 private double side1, side2, side3;
 
 /** constructor with two points; (0,0) is the first vertex */
 public Triangle(Point  p1, Point p2) {
 
  vertex1 = new Point();
  vertex2 = p1;
  vertex3 = p2;
  
  side1 = vertex2.distance(vertex3);
  side2 = vertex3.distance(vertex1);
  side3 = vertex1.distance(vertex2);
 }
 
 /** regular constructor with three points */
 public Triangle(Point p1, Point p2, Point p3) {
  
  vertex1 = p1;
  vertex2 = p2;
  vertex3 = p3;
  
  side1 = vertex2.distance(vertex3);
  side2 = vertex3.distance(vertex1);
  side3 = vertex1.distance(vertex2);
  }
 
 /** degeneracy test: are all sides non zero */
 public boolean isDegenerate() {
  
  return (Math.abs(smallestAngle()) < tolerance);
 }
 
 /** getter methods to extract vertexes */
 public Point getVertex1() {
  
  return this.vertex1;
 }
 
 public Point getVertex2() {
  
  return this.vertex1;
 }
 public Point getVertex3() {
  
  return this.vertex1;
 }

 /** minimum of three doubles */
 private double minOfThree(double d1, double d2, double d3) {
	return Math.min(d1, Math.min(d2, d3));
 }

 /** maximum of three doubles */
  private double maxOfThree(double d1, double d2, double d3) {
 	return Math.max(d1, Math.max(d2, d3));
 }
	 
 
 /** length of the side opposite to vertex1 */
 public double side1() {
  
  return side1;
 } 
 
 /** length of the side opposite to vertex2 */
 public double side2() {
  
  return side2;
 }
 
 /** length of the side opposite to vertex3 */
 public double side3() {
  
  return side3;
 }
 
 /** angle1 (in degrees) at the vertex1 
   * using arccos (it's a monotone function) */
 public double angle1() {  
  double q1 = side1 * side1;
  double q2 = side2 * side2;
  double q3 = side3 * side3;
  double cosine = (q2 + q3 - q1) / (2 * side2 * side3);
  
  return Math.acos(cosine) * 180 / Math.PI;
 }
 /** angle2 (in degrees) at the vertex2 */
 public double angle2() {
  double q1 = side1 * side1;
  double q2 = side2 * side2;
  double q3 = side3 * side3;
  double cosine = (q1 + q3 - q2) / (2 * side1 * side3);
  
  return Math.acos(cosine) * 180 / Math.PI;  
 }
 /* angle3 (in degrees): we believe in Euclid! */
 public double angle3() {
  
  return (180 - angle1() - angle2());
 }

 /** smallest angle in the triangle */
 public double smallestAngle() {
  double angle = angle1();
  if (angle2() < angle) angle = angle2();
  if (angle3() < angle) angle = angle3();
  
  return angle;
 }
 
 /** largest angle in the triangle */
 public double largestAngle() {
  double angle = angle1();
  if (angle2() > angle) angle = angle2();
  if (angle3() > angle) angle = angle3();
  
  return angle;
 }  
 
 /** vertex at the smallest angle */
 public Point sharpestVertex() {
  if (angle1() < angle2()) {
   return (angle1() < angle3()) ? vertex1 : vertex3;
  } 
  else {
   return (angle2() < angle3()) ? vertex2 : vertex3;
  }
 }
 
 /** length of the side opposite to the smallest angle */
 public double shortestSide() {
  if (angle1() < angle2()) {
   return (angle1() < angle3()) ? side1 : side3;
  } 
  else {
   return (angle2() < angle3()) ? side2 : side3;
  }
 }
  
 /** similarity check for this and Triangle t */
 public boolean isSimilar(Triangle t) {
  boolean s = false;
  s = (Math.abs(smallestAngle() - t.smallestAngle()) +
   Math.abs(largestAngle() - t.largestAngle()) < tolerance);
  return s;
 }
   
 /** equality (congruentness) test for this and Triangle represented 
   * by Object o (overriding the equals method appropriately) */
 public boolean equals(Object o) {
  if (o instanceof Triangle) {
    Triangle p = (Triangle) o;
    if (this.isSimilar(p) && 
     Math.abs(shortestSide() - p.shortestSide()) < tolerance)
     return true;
   }
   return false;
 } 
 
 /** overriding hashCode() since equals() was overriddeen */
 public int hashCode() {
  
  return (int) (side1 * side2 * side3);
 }
 
 /** the triangle area calculated via Heron's formular */
 public double area() {
  double sp = 0.5 * (side1 + side2 + side3);
  double s1 = 0.5 * (side2 + side3 - side1);
  double s2 = 0.5 * (side3 + side1 - side2);
  double s3 = 0.5 * (side1 + side2 - side3);
  
  return  Math.sqrt(sp * s1 * s2 * s3);
 }
 
 /** test whether a point p lies inside this triangle */
 public boolean isInside(Point p) {
	 double xmin, xmax, ymin, ymax;
	 int sign1, sign2, sign3;
	 PlanarVector v = new PlanarVector(p);

	 PlanarVector v1 = (new PlanarVector(vertex1));
	 PlanarVector v2 = (new PlanarVector(vertex2));
	 PlanarVector v3 = (new PlanarVector(vertex3));
	 
	 PlanarVector n3 = (new PlanarVector(vertex1, vertex2)).normal();
	 PlanarVector n1 = (new PlanarVector(vertex2, vertex3)).normal();
	 PlanarVector n2 = (new PlanarVector(vertex3, vertex1)).normal();

	 xmin = minOfThree(vertex1.getX(), vertex2.getX(), vertex3.getX());
	 xmax = maxOfThree(vertex1.getX(), vertex2.getX(), vertex3.getX());
	 ymin = minOfThree(vertex1.getY(), vertex2.getY(), vertex3.getY());
	 ymax = maxOfThree(vertex1.getY(), vertex2.getY(), vertex3.getY());
	
	 if ((p.getX() > xmax) || (p.getX() < xmin)) return false;
	 
	 if ((p.getY() > ymax) || (p.getY() < ymin)) return false;
	 
	 sign1 = (v1.scalarProduct(n3) > v.scalarProduct(n3)) ? -1 : 1;
	 sign2 = (v2.scalarProduct(n1) > v.scalarProduct(n1)) ? -1 : 1;
	 sign3 = (v3.scalarProduct(n2) > v.scalarProduct(n2)) ? -1 : 1;
	 
	 if  ( sign1 * sign2 * sign3 == -1) return  false;
	 
	 return true;
 }
 
}
