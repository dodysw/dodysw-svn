/**
 * Dody Suria Wijaya - u4267771
 * Australian National University
 */

import javax.swing.*;
import java.awt.event.*;
import java.io.*;
import java.net.*;
import java.util.*;

public class EScopeFrame extends JFrame
{

    private JPanel componentPanel,drawPanel;
    private JMenuItem connectServer;
    private JMenuItem openExperiment;
    private JMenuItem requestData;
    private JMenuItem plotData;
    private JMenuItem doAll;
    private JMenuItem quit;
    private PrintWriter out=null;
    private DataInputStream in=null;
    private double[] xVals=null,yVals=null;
    private String xUnits=null,yUnits=null;
    private int xLen=0,yLen=0;
    private Socket sock=null;
    private MDSNetworkSource dataSource=null;
    private String address;

    public EScopeFrame()
    {
    //  Components for the GUI
        JMenuBar menuBar=new JMenuBar();
        JMenu menu = new JMenu("File");
        menu.setMnemonic(KeyEvent.VK_F);
        connectServer = new JMenuItem("Connect to server");
        openExperiment = new JMenuItem("Open experiment");
        requestData = new JMenuItem("Request data");
        plotData = new JMenuItem("Plot data");
        doAll = new JMenuItem("Do All");
        doAll.setMnemonic(KeyEvent.VK_A);
        quit = new JMenuItem("Quit");
        quit.setMnemonic(KeyEvent.VK_Q);

        connectServer.addActionListener(new ConnectServerAction());
        openExperiment.addActionListener(new OpenExperimentAction());
        requestData.addActionListener(new RequestDataAction());
        plotData.addActionListener(new PlotDataAction());
        doAll.addActionListener(new DoAllAction());
        quit.addActionListener(new QuitAction());

        menuBar.add(menu);
        menu.add(connectServer);
        menu.add(openExperiment);
        menu.add(requestData);
        menu.add(plotData);
        menu.add(doAll);
        menu.add(quit);
        setJMenuBar(menuBar);

        connectServer.setEnabled(true);
        openExperiment.setEnabled(true);
        requestData.setEnabled(true);
        plotData.setEnabled(true);
        doAll.setEnabled(true);
        quit.setEnabled(true);

        componentPanel = new JPanel();
    }

    public void initialise()
    {
        getContentPane().add(componentPanel);

        setSize(400,200);
        setLocation(400,400);
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setTitle( "EScope GUI" );
        setVisible(true);
    }

    private class ConnectServerAction implements ActionListener
    {
        public ConnectServerAction() {}

        public void actionPerformed(ActionEvent event)
        {
            String serverString = JOptionPane.showInputDialog
                (EScopeFrame.this,"Specify server and port", "ephebe.anu.edu.au:8000");
            try
            {
                dataSource = new MDSNetworkSource();
                System.out.println( "\nConnecting to server ..." );
                dataSource.connect( serverString );
            }
            catch ( IOException e )
            {
                System.out.println( "Failed to communicate with server: " +
                e.getMessage() );
            }
        }
    }
    private class OpenExperimentAction implements ActionListener
    {
        public OpenExperimentAction() {}

        public void actionPerformed(ActionEvent event)
        {
            int ntokens = 0;
            StringTokenizer strTok=null;
            while (ntokens != 2)
            {
                String experimentString = JOptionPane.showInputDialog
                (EScopeFrame.this,"Specify experiment and shot","h1data 37025");
                strTok = new StringTokenizer(experimentString);
                ntokens = strTok.countTokens();
            }
            String experiment = strTok.nextToken();
            String portString = strTok.nextToken();
            try
            {
                int shot = Integer.parseInt(portString);
                System.out.println( "Opening Experiment and Shot ..." +
                   experiment + " " + shot);
                dataSource.open( experiment, shot ); // string, int

            }
            catch (NumberFormatException e) {System.out.println(e);}
            catch (IOException e) {System.out.println(e);}
        }
    }

    private class RequestDataAction implements ActionListener
    {
        public RequestDataAction() {}
        public void actionPerformed(ActionEvent event)
        {
            address = JOptionPane.showInputDialog
                  (EScopeFrame.this,"Enter legal file address",".operations:i_fault");
            MDSDescriptor result1=null,result2=null,result3=null,result4=null;
            String expression = null;
            try 
            {
                expression="units_of(" + address + ")";
                System.out.println( "Evaluating Expression  " + expression );
                result1 = dataSource.evaluate( expression );

                expression= address;
                System.out.println( "Evaluating Expression  " + expression );
                result2 = dataSource.evaluate( expression );

                expression="units_of(dim_of(" + address +"))";
                System.out.println( "Evaluating Expression  " + expression );
                result3 = dataSource.evaluate( expression );

                expression="dim_of(" + address + ")";
                System.out.println( "Evaluating Expression  " + expression );
                result4 = dataSource.evaluate( expression );
            }
            catch ( IOException e )
            {
                System.out.println( "Failed to communicate with server: " 
                + e.getMessage() );
                System.exit(-1);
            }
            yUnits = result1.getCstringData(); 
            xUnits = result3.getCstringData(); 
            yLen = result2.getDoubleData().length;
            xLen = result4.getDoubleData().length; 
            yVals = new double[yLen];
            xVals = new double[xLen];

	    yVals = result2.getDoubleData();
	    xVals = result4.getDoubleData();
        }
    }

    private class PlotDataAction implements ActionListener
    {
        public PlotDataAction() {}
        public void actionPerformed(ActionEvent event)
        {
            if (xVals !=null && yVals != null && xUnits != null &&
                yUnits != null&& yLen !=0 && xLen !=0 ) 
            {   Plotter3.plot(xVals,yVals,xUnits,
                 yUnits,address);}
            else {System.out.println("Not enough information for plot");}
        }
    }

    private class QuitAction implements ActionListener
    {
        public QuitAction() {}

        public void actionPerformed(ActionEvent event)
        {
            try
            {
                if (dataSource!=null)
                {
                    System.out.println( "\nClosing Experiment ..." );
                    dataSource.close();

                    System.out.println( "Disconnecting from server ..." );
                    dataSource.disconnect();

                    dataSource = null;
                }
                System.exit(0);
            }
            catch (IOException e) {System.out.println(e);}
        
        }
    }

    private class DoAllAction implements ActionListener
    {
        public DoAllAction() {}

        public void actionPerformed(ActionEvent event)
        {

            try
            {
                dataSource = new MDSNetworkSource();
                System.out.println( "\nConnecting to server ..." );
                dataSource.connect( "ephebe.anu.edu.au:8000" );
                System.out.println("...connection successful");
            }
            catch ( IOException e )
            {
                System.out.println( "Failed to communicate with server: " +
                        e.getMessage() );
                return;
            }

            StringTokenizer strTok=null;
            strTok = new StringTokenizer("h1data 37025");
            String experiment = strTok.nextToken();
            String portString = strTok.nextToken();
            try
            {
                int shot = Integer.parseInt(portString);
                System.out.println( "Opening Experiment and Shot ..." +
                        experiment + " " + shot);
                dataSource.open( experiment, shot ); // string, int

            }
            catch (NumberFormatException e) {System.out.println(e);return;}
            catch (IOException e) {System.out.println(e);return;}

            address = ".operations:i_fault";
            MDSDescriptor result1=null,result2=null,result3=null,result4=null;
            String expression = null;
            try 
            {
                expression="units_of(" + address + ")";
                System.out.println( "Evaluating Expression  " + expression );
                result1 = dataSource.evaluate( expression );

                expression= address;
                System.out.println( "Evaluating Expression  " + expression );
                result2 = dataSource.evaluate( expression );

                expression="units_of(dim_of(" + address +"))";
                System.out.println( "Evaluating Expression  " + expression );
                result3 = dataSource.evaluate( expression );

                expression="dim_of(" + address + ")";
                System.out.println( "Evaluating Expression  " + expression );
                result4 = dataSource.evaluate( expression );
            }
            catch ( IOException e )
            {
                System.out.println( "Failed to communicate with server: " 
                + e.getMessage() );
                System.exit(-1);
            }
            yUnits = result1.getCstringData(); 
            xUnits = result3.getCstringData(); 
            yLen = result2.getDoubleData().length;
            xLen = result4.getDoubleData().length; 
            yVals = new double[yLen];
            xVals = new double[xLen];

	    yVals = result2.getDoubleData();
	    xVals = result4.getDoubleData();

            if (xVals !=null && yVals != null && xUnits != null &&
                yUnits != null&& yLen !=0 && xLen !=0 )
            {   Plotter3.plot(xVals,yVals,xUnits,
                              yUnits,address);}
                              else {System.out.println("Not enough information for plot");}
            }

    }
    
}
