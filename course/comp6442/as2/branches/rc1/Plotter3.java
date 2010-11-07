/**
 * Dody Suria Wijaya - u4267771
 * Australian National University
 */

import java.awt.*;
import java.awt.event.*;
import java.awt.geom.*;
import java.awt.font.*;
import javax.swing.*;
import java.text.*;
import java.util.*;

/** Produces a graph with a border, labels, ticks, grid, tick-labels and
scientific notation. A mouse-activated Graph Point cursor is implemented.*/
public class Plotter3 {
    public static void plot(double[] xVals, double[] yVals, String xUnits,
            String yUnits, String title) {
        DrawFrame f = new DrawFrame(xVals, yVals, xUnits, yUnits, title);
        f.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        f.setVisible(true);
    }
}

/* A frame that contains a panel with drawings */
class DrawFrame extends JFrame {
    public static final int WIDTH = 700;

    public static final int HEIGHT = 500;

    public DrawFrame(double[] xVals, double[] yVals, String xUnits,
            String yUnits, String title) {
        setTitle(title + "  (" + yUnits + " versus " + xUnits + ")");
        setSize(WIDTH, HEIGHT);
        DrawPanel panel = new DrawPanel(xVals, yVals, xUnits, yUnits, title);
        Container contentPane = getContentPane();
        contentPane.add(panel);
    }
}

/* A panel that displays the line */
class DrawPanel extends JPanel {
    private double[] xVals, yVals;

    private String xUnits, yUnits, title;

    private int borderSize, sigFigs, titleFontHeight, axisFontHeight,
            tickFontHeight, tickPix;

    private int rightBorderSize = 5;

    private double textFac;

    private Font titleFont, axisFont, tickFont;

    private double maxNormalRange = 10.0; // (Do not use sci notation inside

    private double minNormalRange = 1.0; //      of normal range)

    private String sciXLabel = ""; //label for x-axis title if sci notation

    private String sciYLabel = "";//label for y-axis title if sci notati

    /** Nice formater for axis numbers */
    private NumberFormat tickFormatter = NumberFormat.getNumberInstance();

    private GraphMouseHandler mouseHandler = new GraphMouseHandler();
    
    protected Point dragOffsetPoint = new Point();

        static final int DRAGMODE_CROSSHAIR = 1;
        static final int DRAGMODE_WAVEFORM = 2;
        protected int dragMode = DRAGMODE_CROSSHAIR;

    public DrawPanel(double[] x, double[] y, String xu, String yu, String ti) {
        this.xVals = x;
        this.yVals = y;
        this.xUnits = xu;
        this.yUnits = yu;
        this.title = ti;

        sigFigs = 5;
        tickFormatter.setMaximumFractionDigits(4);
        tickFormatter.setMinimumIntegerDigits(1);
        titleFontHeight = 16;
        axisFontHeight = 12;
        tickFontHeight = 10;
        textFac = 0.8; // ratio of ascent to height of text
        tickPix = 4; // number of pixels for tick length

        borderSize = 0; // pixel width of each border to be calculated later

        titleFont = new Font(null, Font.PLAIN, titleFontHeight);
        axisFont = new Font(null, Font.PLAIN, axisFontHeight);
        tickFont = new Font(null, Font.PLAIN, tickFontHeight);

        addMouseListener(mouseHandler);
        addMouseMotionListener(mouseHandler);
    }

    public void paintComponent(Graphics g) {
        //System.out.println("Paint triggered!");
        // Cast the graphics object to Graph2D
        Graphics2D g2 = (Graphics2D) g;

        // Get plot size
        Dimension size = getSize();
        int xSize = size.width;
        int ySize = size.height;

        // Set background color
        g2.setColor(Color.white);
        g2.fill(new Rectangle2D.Double(0, 0, size.width, size.height));

        // Set the Color and BasicStroke for drawing lines
        g2.setColor(Color.black);
        float strokeWidth = 1.0f;
        float[] solid = { 12.0f, 0.0f }; // Solid line style
        BasicStroke bs = new BasicStroke(strokeWidth, BasicStroke.CAP_SQUARE,
                BasicStroke.JOIN_MITER, 1.0f, solid, 0.0f);
        g2.setStroke(bs);

        borderSize = calcBorderSize(g2); // find border size

        /* Perform scaling from data coordinates to pixels */
        double xMax, yMax, xMin, yMin, deltaX, deltaY;
        xMax = xVals[0];
        xMin = xMax;
        yMax = yVals[0];
        yMin = yMax;
        int i = 0;
        for (i = 1; i < xVals.length; i++) {
            if (xVals[i] > xMax)
                xMax = xVals[i];
            if (yVals[i] > yMax)
                yMax = yVals[i];
            if (xVals[i] < xMin)
                xMin = xVals[i];
            if (yVals[i] < yMin)
                yMin = yVals[i];
        }
        deltaX = xMax - xMin;
        deltaY = yMax - yMin;

        double xScale = (xSize - borderSize - rightBorderSize) / (deltaX);
        double yScale = (ySize - 2 * borderSize) / (deltaY);

        /* Check how many points might be filtered out after transforming to pixels*/
        int npoints = -1;
        int currXVal, currYVal, prevXVal = 0;
        for (i = 0; i < yVals.length; i++) { //Find how many points can be filtered
            currXVal = (int) ((xVals[i] - xMin) * xScale + borderSize);
            if (npoints == -1 || currXVal != prevXVal) {
                npoints++; // increment counter if x pixel value is different
                prevXVal = currXVal;
            }
        }

        // Set up arrays to received scaled points
        int nDim = yVals.length;
        if (npoints <= yVals.length / 2)
            nDim = 2 * (npoints + 1);
        double[] xScaled = new double[nDim];
        double[] yScaled = new double[nDim];
        if (npoints > yVals.length / 2) {//Heuristic condition for "not enough points to filter"
            for (i = 0; i < yVals.length; i++) {
                xScaled[i] = (int) ((xVals[i] - xMin) * xScale + borderSize);
                yScaled[i] = (int) (ySize - borderSize - (yVals[i] - yMin)
                        * yScale);
            }
            npoints = yVals.length;
        } else { //It makes sense to filter. Adjacent filtered points store max/min Y vals
            npoints = -1;
            for (i = 0; i < yVals.length; i++) {
                currXVal = (int) ((xVals[i] - xMin) * xScale + borderSize);
                currYVal = (int) (ySize - borderSize - (yVals[i] - yMin)
                        * yScale);
                if (npoints == -1 || currXVal != prevXVal) { // Points have different xVals; store two copies
                    npoints++;
                    prevXVal = currXVal;
                    xScaled[2 * npoints] = currXVal;
                    yScaled[2 * npoints] = currYVal;
                    xScaled[2 * npoints + 1] = currXVal;
                    yScaled[2 * npoints + 1] = currYVal;
                } else { // Points have same xVals; store max/min yVals
                    if (currYVal < yScaled[2 * npoints])
                        yScaled[2 * npoints] = currYVal;

                    if (currYVal > yScaled[2 * npoints + 1])
                        yScaled[2 * npoints + 1] = currYVal;
                }
            }
            npoints = 2 * npoints + 1;
        }

        /* Plot curve */
        g2.setClip(borderSize, borderSize, xSize-borderSize-rightBorderSize, ySize-2*borderSize);
        for (i = 0; i < npoints; i++) {
            Line2D line = new Line2D.Double(xScaled[i] + dragOffsetPoint.x, yScaled[i] + dragOffsetPoint.y,
                    xScaled[i + 1] + dragOffsetPoint.x, yScaled[i + 1] + dragOffsetPoint.y);
            g2.draw(line);
        }
        g2.setClip(null);

        /* Draw mouse cursor */
        if (dragMode == DRAGMODE_CROSSHAIR) {
	        // First find index of closest x data point
	        int currX = mouseHandler.getCurrX();
	        if ((currX > borderSize) && (currX < (xSize - rightBorderSize))) {
	            // find closest data value from xScaled
	            int minIdx = 0;
	            double minDist = Math.abs(currX - dragOffsetPoint.x - xScaled[0]);
	            double currDist = minDist;
	            for (i = 1; i < npoints; i++) {
	                currDist = Math.abs(currX - dragOffsetPoint.x - xScaled[i]);
	                if (currDist < minDist) {
	                    minDist = currDist;
	                    minIdx = i;
	                }
	            }
	            // draw large cursor marker on graph
	            Line2D xLine = new Line2D.Double(borderSize, yScaled[minIdx] + dragOffsetPoint.y, xSize - rightBorderSize, yScaled[minIdx] + dragOffsetPoint.y);
	            Line2D yLine = new Line2D.Double(xScaled[minIdx] + dragOffsetPoint.x, ySize - borderSize, xScaled[minIdx] + dragOffsetPoint.x, borderSize);
	            g2.draw(xLine);
	            g2.draw(yLine);
	        }
        }

        /* Draw ticks, grid and tick labels */
        // find height of a dummy axis label
        g2.setFont(axisFont);
        FontRenderContext context = g2.getFontRenderContext();
        Rectangle2D bounds = axisFont.getStringBounds("Axis Label", context);
        double axisLabelHeight = bounds.getHeight();

        // change stroke for ticks and grid
        strokeWidth = 0.25f;
        bs = new BasicStroke(strokeWidth, BasicStroke.CAP_SQUARE,
                BasicStroke.JOIN_MITER, 1.0f, solid, 0.0f);
        g2.setStroke(bs);
        // draw ticks, grid and tick labels and return sci labels
        this.drawXAxis(xSize, ySize, xScale, xMin, xMax, (int) axisLabelHeight, g2);
        this.drawYAxis(xSize, ySize, yScale, yMin, yMax, (int) axisLabelHeight, g2);
        // draw graph title and axes labels
        drawLabels(titleFont, axisFont, xSize, ySize, g2);
        
        /* Draw border line box*/
        //drawBorderLine(xSize, ySize, g2);

    }
    
//    private void drawBorderLine(int xSize, int ySize, Graphics2D g2) {
//        //g2.drawRect(borderSize, borderSize, xSize-2*borderSize, ySize-2*borderSize);
//                g2.drawRect(borderSize, borderSize, xSize-borderSize-rightBorderSize, ySize-2*borderSize);
//    }

    public int calcBorderSize(Graphics2D g2) {
        /* borderSize calculation using test string "Test" */
        double border1, border2, border3, spaceFac = 1.5;

        // height of top adornment: graph title
        g2.setFont(titleFont); // title font
        FontRenderContext context = g2.getFontRenderContext();
        Rectangle2D bounds = titleFont.getStringBounds("Test", context);
        double stringHeight = bounds.getHeight();
        border1 = spaceFac * stringHeight;

        // height of bottom adornments: x-axis label plus x-tick-labels
        g2.setFont(axisFont); // axis font
        context = g2.getFontRenderContext();
        bounds = axisFont.getStringBounds("Test", context);
        stringHeight = bounds.getHeight();
        border2 = spaceFac * stringHeight;
        g2.setFont(tickFont); // tick font
        context = g2.getFontRenderContext();
        bounds = tickFont.getStringBounds("Test", context);
        stringHeight = bounds.getHeight();
        border3 = spaceFac * stringHeight;
        return (int) Math.max(border1, border2 + border3);
    }

    public void drawLabels(Font titleFont, Font axisFont, int xSize, int ySize,
            Graphics2D g2) {
        // Graph Title
        g2.setFont(titleFont); // set font and find size of label
        FontRenderContext context = g2.getFontRenderContext();
        Rectangle2D bounds = titleFont.getStringBounds(title, context);
        double stringWidth = bounds.getWidth();
        double stringHeight = bounds.getHeight();

        int xp = (int) ((xSize - stringWidth) / 2.0);
        int yp = (int) ((borderSize - stringHeight) / 2.0 + textFac
                * stringHeight);
        g2.drawString(title, xp, yp);
        

        /* Draw X-axis label */
        g2.setFont(axisFont); // set font and find size of label
        context = g2.getFontRenderContext();
        bounds = axisFont.getStringBounds(xUnits + sciXLabel, context);
        stringWidth = bounds.getWidth();
        stringHeight = bounds.getHeight();

        xp = (int) ((xSize - stringWidth) / 2.0);
        yp = (int) (ySize - 1.25 * stringHeight + textFac * stringHeight);
        g2.drawString(xUnits + sciXLabel, xp, yp);

        /* Draw Y-axis label */
        bounds = axisFont.getStringBounds(yUnits + sciYLabel, context); //font unchanged
        stringWidth = bounds.getWidth();
        stringHeight = bounds.getHeight();

        xp = (int) (stringHeight * 0.5 + stringHeight * textFac);
        yp = (int) (ySize - (ySize - stringWidth) / 2.0);
        // rotate graphics context; draw the message; rotate back
        g2.rotate(-90.0 * Math.PI / 180., xp, yp);
        g2.drawString(yUnits + sciYLabel, xp, yp);
        g2.rotate(+90.0 * Math.PI / 180., xp, yp);
    }

    /** Draw x-axis grid, ticks and tick labels. Return sciXLabel */
    public void drawXAxis(int xSize, int ySize, double xScale, double xMin,
            double xMax, int axisLabelHeight, Graphics2D g2D) {
        int tickOffset = 3;
        // set font and find FontRenderContext for label dimensions
        g2D.setFont(tickFont);
        FontRenderContext context = g2D.getFontRenderContext();

        String dummyTickL = "."; // compute width of dummy tick label
        for (int i = 0; i < sigFigs; i++) {
            dummyTickL = dummyTickL + "W";
        }
        Rectangle2D bounds = tickFont.getStringBounds(dummyTickL, context);
        int textWidth = (int) bounds.getWidth();
		

        //find number of ticks
        int numTicks = (int) ((double) (xSize - borderSize - rightBorderSize) / (double) textWidth);
        if (numTicks < 2)
            numTicks = 2;
        int nBin = numTicks - 1; // number of bins

        double dataRange = nicenum(xMax - xMin, true);
        double d = nicenum(dataRange / (nBin), false);

		//convert drag offset (in screen unit) to data offset
        double xDataOffset = dragOffsetPoint.x / xScale;
        
        // tick range should be smaller than the data range
        double tickMin = d * Math.ceil((xMin - xDataOffset) / d);
        double tickMax = d * Math.floor((xMax - xDataOffset) / d);

        // Fill in an arraylist of x tick values
        ArrayList<Double> xTickPoints = new ArrayList<Double>();
        for (double x = tickMin; x <= tickMax + 0.5 * d; x = x + d) {
            xTickPoints.add(new Double(x));
        }

        Double dummyMinTick = xTickPoints.get(0); //first
        double minTick = dummyMinTick.doubleValue();
        Double dummyMaxTick = xTickPoints.get(xTickPoints.size() - 1); //last
        double maxTick = dummyMaxTick.doubleValue();

        // Determine whether to use scientific notation; find multiplier and sciLabel
        double maxAbsTick = Math.max(Math.abs(maxTick), Math.abs(minTick));
        int powerOfTen = 0;
        double multiplier = 1;
        boolean scientific = false;
        sciXLabel = "";
        if (maxAbsTick >= maxNormalRange || maxAbsTick < minNormalRange) {
            if (maxAbsTick == 0)
                powerOfTen = 1;
            else
                powerOfTen = (int) Math.floor(Math.log10(maxAbsTick));
            scientific = true;
            sciXLabel = "  (x10^" + powerOfTen + ")";
            multiplier = Math.pow(10, powerOfTen);
        }

		for (int i = 0; i < xTickPoints.size(); i++) {
			Double dummy = xTickPoints.get(i);
			double dp = dummy.doubleValue();
			// Draw little tick mark
			int gp = (int) ((dp - xMin) * xScale + borderSize);
			int yu = (int) ySize - borderSize;
			int yl = yu + tickPix; // Number of pixels below the axis for tick
			g2D.drawLine(gp + dragOffsetPoint.x, yu, gp+ dragOffsetPoint.x, yl); // Draw little tick

			// Draw grid line
			g2D.drawLine(gp + dragOffsetPoint.x, yu, gp + dragOffsetPoint.x, borderSize);

            // Find tick label
            String tickLabel = "";
            double tickNumber = 0.;
            if (scientific) {
                tickNumber = dp / multiplier;
            } // Divide by exponent to get number
            else {
                tickNumber = dp;
            }
            tickLabel = tickFormatter.format(tickNumber);

            // Calc tick label width and height
            bounds = tickFont.getStringBounds(tickLabel, context);
            double stringWidth = bounds.getWidth();
            double stringHeight = bounds.getHeight();
            // Draw label
            int xp = gp - (int) (stringWidth / 2.0);
            int yp = (int) (ySize - borderSize + tickOffset + textFac
                    * stringHeight);
            g2D.drawString(tickLabel, xp + dragOffsetPoint.x, yp);
        }
    }

    /**
    Calculates and draws the y axis interval lines and interval numbers
    @param min The minimum y point on the graph
    @param max The maximum y point on the graph
    */
    public void drawYAxis(int xSize, int ySize, double yScale, double yMin,
            double yMax, int axisLabelHeight, Graphics2D g2D) {
        // set font and find FontRenderContext for label dimensions
        g2D.setFont(tickFont);
        FontRenderContext context = g2D.getFontRenderContext();

        String dummyTickL = "."; // compute width of a dummy tick label
        for (int i = 0; i < sigFigs; i++) {
            dummyTickL = dummyTickL + "W";
        }
        Rectangle2D bounds = tickFont.getStringBounds(dummyTickL, context);
        int textWidth = (int) bounds.getWidth();


        //find number of ticks
        int numTicks = (int) ((double) (ySize - 2 * borderSize) / (double) textWidth);
        if (numTicks < 2)
            numTicks = 2;
        int nBin = numTicks - 1; // number of bins

        double dataRange = nicenum(yMax - yMin, true);
        double d = nicenum(dataRange / (nBin), false);
        
		//convert drag offset (in screen unit) to data offset
        double yDataOffset = dragOffsetPoint.y / yScale;


        // tick range should be smaller than the data range
        double tickMin = d * Math.ceil((yMin + yDataOffset) / d);
        double tickMax = d * Math.floor((yMax + yDataOffset) / d);

        // Fill in an arraylist of y tick values
        ArrayList<Double> yTickPoints = new ArrayList<Double>();
        for (double y = tickMin; y <= tickMax + 0.5 * d; y = y + d) {
            yTickPoints.add(new Double(y));
        }

        Double dummyMinTick = yTickPoints.get(0); //first
        double minTick = dummyMinTick.doubleValue();
        Double dummyMaxTick = yTickPoints.get(yTickPoints.size() - 1); //last
        double maxTick = dummyMaxTick.doubleValue();

		// Determine whether to use scientific notation; find multiplier and sciLabel
		double maxAbsTick = Math.max(Math.abs(maxTick), Math.abs(minTick));
		int powerOfTen = 0;
		double multiplier = 0;
		boolean scientific = false;
		sciYLabel = "";
		if (maxAbsTick >= maxNormalRange || maxAbsTick < minNormalRange) {
			if (maxAbsTick == 0)
				powerOfTen = 1;
			else
				powerOfTen = (int) Math.floor(Math.log10(maxAbsTick));
			scientific = true;
			sciYLabel = "  (x10^" + powerOfTen + ")";
			multiplier = Math.pow(10, powerOfTen);
		}
		for (int i = 0; i < yTickPoints.size(); i++) {
			Double dummy = yTickPoints.get(i);
			double dp = dummy.doubleValue();
			// Draw little tick mark
			int gp = (int) (ySize - borderSize - (dp - yMin) * yScale);
			int xr = (int) borderSize;
			int xl = xr - tickPix; // Number of pixels left of the axis for tick
			g2D.drawLine(xl, gp + dragOffsetPoint.y, xr, gp+ dragOffsetPoint.y); // Draw little tick

			// Draw grid line
			g2D.drawLine(borderSize, gp + dragOffsetPoint.y, (int) xSize - rightBorderSize, gp + dragOffsetPoint.y);

            // Find tick label
            String tickLabel = "";
            double tickNumber = 0.;
            if (scientific) {
                tickNumber = dp / multiplier; 
            } // Divide by exponent to get number
            else {
                tickNumber = dp;
            }
            tickLabel = tickFormatter.format(tickNumber);

            // Calc tick label width and height
            bounds = tickFont.getStringBounds(tickLabel, context);
            double stringWidth = bounds.getWidth();
            double stringHeight = bounds.getHeight();
            // Draw label
            int xp = (int) (1.5 * axisLabelHeight + textFac * stringHeight);
            int yp = gp + ((int) (stringWidth / 2.0));
            // Rotate and draw string; rotate back
            g2D.rotate(-90.0 * Math.PI / 180., xp, yp);
            g2D.drawString(tickLabel, xp - dragOffsetPoint.y, yp);
            g2D.rotate(90.0 * Math.PI / 180., xp, yp);
        }
    }

    /**
    Converts a given number into a 'nice number'
    @param x The number to convert
    @param round True for rounded to closest; false for rounded up
    @return The nice number near x
    */
    private double nicenum(double x, boolean round) {
        int exp;
        double f, nf;

        if (x <= 0.) {
            System.out.println("Illegal value passed to nicenum: " + x);
            System.exit(0);
        }

        exp = (int) Math.floor(Math.log10(x));
        f = x / Math.pow(10.0, exp);
        if (round) // round to closest nice number
        {
            if (f < 1.5) {
                nf = 1.0;
            } else {
                if (f < 3.0) {
                    nf = 2.0;
                } else {
                    if (f < 7.0) {
                        nf = 5.0;
                    } else {
                        nf = 10.0;
                    }
                }
            }
        } else // round up to nice number
        {
            if (f <= 1.0) {
                nf = 1.0;
            } else {
                if (f <= 2.0) {
                    nf = 2.0;
                } else {
                    if (f <= 5.0) {
                        nf = 5.0;
                    } else {
                        nf = 10.0;
                    }
                }
            }
        }
        return nf * Math.pow(10.0, exp);
    }

    /** inner class to handle mouse events */
    private class GraphMouseHandler implements MouseListener, MouseMotionListener {
        private int currX = 0, currY = 0;
        private Point lastPressedPoint = new Point();
        private Point lastDragOffset = new Point();
        private Point currentDragOffset = new Point();

        public GraphMouseHandler() {
        }

        public void mousePressed(MouseEvent e) {
            if (dragMode == DRAGMODE_WAVEFORM) {
                lastPressedPoint = e.getPoint();
                lastDragOffset.x = dragOffsetPoint.x;
                lastDragOffset.y = dragOffsetPoint.y;
            }
//            System.out.println("Pressed at " + e.getX() + "," + e.getY());
        }

        public void mouseMoved(MouseEvent e) {
        }

        public void mouseClicked(MouseEvent e) {
//            System.out.println("Click at " + e.getX() + "," + e.getY() + " count " + e.getClickCount());
            int clickCount = e.getClickCount();
            if (clickCount == 1) {
//                System.out.println("Click count 1. button:" + e.getButton());
                if (e.getButton() == MouseEvent.BUTTON3) {
//                    System.out.println("Showing context menu");
                    //show context menu
                    JPopupMenu menu = new JPopupMenu("Drag mode selection");
                    JCheckBoxMenuItem item;
                    item = new JCheckBoxMenuItem("Cross-hair diagnostic mode");
                    item.setState(dragMode == DRAGMODE_CROSSHAIR);
//                    item.setEnabled(dragMode != DRAGMODE_CROSSHAIR);
                    menu.add(item);
                    item.addActionListener(new CrosshairModeAction());
                    item = new JCheckBoxMenuItem("Grab waveform mode");
                    item.setState(dragMode == DRAGMODE_WAVEFORM);
//                    item.setEnabled(dragMode != DRAGMODE_WAVEFORM);
                    menu.add(item);
                    item.addActionListener(new WaveformModeAction());
                    menu.show(e.getComponent(), e.getX(), e.getY());
                }
            }
            else if (clickCount == 2) {
                System.out.println("Click count 2!");
                if (dragMode == DRAGMODE_WAVEFORM) {
                    if (!(dragOffsetPoint.x == 0 && dragOffsetPoint.y == 0)) {
                        dragOffsetPoint.x = 0;
                        dragOffsetPoint.y = 0;
                        System.out.println("Trying to repaint");
                        DrawPanel.this.repaint();
                    }
                }
            }
        }

        public void mouseEntered(MouseEvent e) {        	
        	if (dragMode == DRAGMODE_WAVEFORM) {
        		e.getComponent().setCursor(new Cursor(Cursor.HAND_CURSOR));
        	}
        	else {
        		e.getComponent().setCursor(null);
        	}
        }

        public void mouseExited(MouseEvent e) {
        }

        public void mouseDragged(MouseEvent e) {
//			System.out.println("Drag at " + e.getX() + "," + e.getY());
            if (dragMode == DRAGMODE_WAVEFORM) {
                currentDragOffset.x = e.getX() - lastPressedPoint.x;
                currentDragOffset.y = e.getY() - lastPressedPoint.y;
                System.out.println("1Last drag offset is" + lastDragOffset.x + "," + lastDragOffset.y);
                dragOffsetPoint.x = lastDragOffset.x + currentDragOffset.x;
                dragOffsetPoint.y = lastDragOffset.y + currentDragOffset.y;
                System.out.println("2Last drag offset is" + lastDragOffset.x + "," + lastDragOffset.y);
                System.out.println("Current drag offset is" + currentDragOffset.x + "," + currentDragOffset.y);
                System.out.println("Drag offset is" + dragOffsetPoint.x + "," + dragOffsetPoint.y);
            }
            else {
                currX = e.getX();
                currY = e.getY();            	
            }
            DrawPanel.this.repaint();
        }

        public void mouseReleased(MouseEvent e) {
//            System.out.println("Mouse released at " + e.getX() + "," + e.getY());
        }

        public int getCurrX() {
            return currX;
        }

        private class CrosshairModeAction implements ActionListener {
            public void actionPerformed(ActionEvent event) {
                dragMode = DRAGMODE_CROSSHAIR;
                DrawPanel.this.repaint();
            }
        }

        private class WaveformModeAction implements ActionListener {
            public void actionPerformed(ActionEvent event) {
                dragMode = DRAGMODE_WAVEFORM;
                DrawPanel.this.repaint();
            }
        }
        
    }
}

