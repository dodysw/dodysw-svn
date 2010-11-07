/**
 * Dody Suria Wijaya - u4267771
 * Australian National University
 */

import java.io.*;
import java.net.*;

public class MDSNetworkSource implements MDSDataSource
{
    private String serverAddrCPort;
    private String experiment;
    private int shot;

    private boolean isConnected;
    private boolean isOpen;

    private Socket socket; // socket for connecting to MDSPlus server
    private DataOutputStream output; // output stream for socket
    private DataInputStream input; // input stream for socket

    public MDSNetworkSource()
    {
        serverAddrCPort = null;
        experiment = null;
        shot = -1;

        isConnected = false;
        isOpen = false;

        socket = null;
        input = null;
        output = null;
    }

    /** Attempts to open a new connection to the MDSPlus server.
        If no port given the default is port 8000. Sets flags
        @param _source The address of the MDSPlus server and port 
        @throws IOException If already connected */
    public void connect(String _source) throws IOException
    {
        serverAddrCPort = _source;

        if ( isConnected )
        {
            throw new IOException( "Already connected" );
        }

        /* First check if a port is specified in the string */
        int i = serverAddrCPort.indexOf(":");
        String addr;
        int port;
        if ( i == -1 )
        {
            addr = serverAddrCPort;
            port = 8000;
        }
        else
        {
            addr = serverAddrCPort.substring( 0, i );
            port = Integer.parseInt( 
            serverAddrCPort.substring(i+1, serverAddrCPort.length()));
        }

        /* Connect to remote server */
        socket = new Socket( addr, port );
        output = new DataOutputStream( new
                     BufferedOutputStream( socket.getOutputStream() ) );
        input = new DataInputStream( new
                    BufferedInputStream( socket.getInputStream() ) );

        /* Send login name */
        MDSMessage message = new MDSMessage( "JAVA_USER" );

        message.send( output );
        message.receive( input );

        /* Flag that we are now connected */
        isConnected = true;
    }

    /** Disconnects from the MDSPlus server, and resets flags.
        @throws IOException If not already connected */
    public void disconnect() throws IOException
    {
        if ( !isConnected )
        {
            throw new IOException( "Not connected" );
        }

        socket.close();
        socket = null;
        output = null;
        input = null;
        isConnected = false;
        isOpen = false;
    }

    /**  @throws IOException If not connected, already open, or if the server 
         is down or not responding */
    public void open(String _experiment, int _shot) throws IOException
    {
        MDSDescriptor status;

        if ( !isConnected )
        {
            throw new IOException( "Not connected" );
        }

        if ( isOpen )
        {
            throw new IOException( "Already open" );
        }

        status = sendMessage( "JavaOpen(\"" + _experiment +
                              "\"," + _shot + ")" );

        if ( status == null )
        {
            throw new IOException( "Null response from server" );
        }
        else
        {
            if ( status.getIntData() != null && status.getIntData().length > 0 )
            {   // diagnostic write of return data from server
                System.out.println( "MDSNetworkSource::Open:" +
                        "result = " + status.getIntData()[0] );
            }
            experiment = _experiment;
            shot = _shot;
            isOpen = true;
        }
    }

    /** @throws IOException if not connected to a server or no experiment open. */
    public void close() throws IOException
    {
        MDSDescriptor status;

        if ( !isConnected )
        {
            throw new IOException( "Not connected" );
        }
        if ( !isOpen )
        {
            throw new IOException( "Not open" );
        }
        status = sendMessage( "JavaClose(\"" + experiment + "\"," + shot + ")" );

        isOpen = false;
    }

    /**
    Sends a message to the MDSPlus server and returns the response.
    This is a high level method and calls the more complicated sendMessage()
    method which hides the more complex implementation necessary to communicate
    with an MDSPlus server.
    @param expression The message to be sent to the server
    @return The response from the server
    @throws IOException If not connected to a server or no experiment open.
    */
    public MDSDescriptor evaluate(String expression) throws IOException
    {
        if ( !isConnected )
        {
            throw new IOException( "Not connected" );
        }
        if ( !isOpen )
        {
            throw new IOException( "Not open" );
        }
        return sendMessage( expression );
    }

    /** Sends a message to the MDSPlus server and returns the response.
    This method works at a lower level than evaluate() and requires 
        the use of a MDSMessage object.
    @param expression The message to be sent to the server
    @return The response from the server
    @throws IOException passed up from MDSMessage send or receive methods
    */
    private MDSDescriptor sendMessage( String msg ) throws IOException
    {
        MDSMessage message = new MDSMessage( msg );
        message.send( output );
        message.receive( input );
        return message.toDescriptor();
    }

    // get and set and status methods
    public boolean isConnected(){ return isConnected;}

    public boolean isOpen(){ return isOpen;}

    public String getSource(){ return serverAddrCPort;}

    public String getExperiment(){ return experiment;}

    public int getShot(){ return shot;}
}
