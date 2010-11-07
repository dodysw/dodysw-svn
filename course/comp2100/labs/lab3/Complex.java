package comp2100.junit;

/** 
 * The class Complex to do simple JUnit tests.
 *
 * @author $Author: u4267771 $
 * @version $Revision: 15 $
 * $Date: 2006-03-29 15:10:19 +1100 (Wed, 29 Mar 2006) $
 */

public class Complex {
 private double real;
 private double imag;
 
 public Complex(double r, double i) {
  this.real = r;
  this.imag = i;
 }

 public double getReal() {
  return this.real;
 }

 public double getImag() {
  return this.imag;
 }
 
 public String toString() {
  return this.real + " + i*" + this.imag;
 }

 public boolean equals(Object o) {
  if (o instanceof Complex) 
   return ((Complex)o).getReal() == this.real && 
    ((Complex)o).getImag() == this.imag;
  return false;
 }

 public int hashCode() {
  return (int)(this.real*this.imag - this.real/this.imag);
 }
 
 public Complex conjugate() {
  return new Complex(this.real, - this.imag);
 }

 public double abs() {
  return Math.sqrt(this.real * this.real + this.imag * this.imag);
 }

 public double arg() {
  return Math.atan2(this.imag, this.real);
 }

 public Complex add(Complex c) {
  return new Complex(this.real + c.real,
       this.imag + c.imag);
 }
 
 public Complex subtract(Complex c) {
  return new Complex(this.real - c.real,
       this.imag - c.imag);
 }
 
 public Complex multiply(Complex c) {
  return new Complex(this.real * c.real - this.imag * c.imag,
       this.imag * c.real + this.real * c.imag);
 }
 
 /*
 public Complex divide(Complex c) throws ArithmeticException {
  double d =  c.abs();
  Complex n = this.multiply(c.conjugate());
  return new Complex(n.real / d,
       n.imag / d);
 } */

}
