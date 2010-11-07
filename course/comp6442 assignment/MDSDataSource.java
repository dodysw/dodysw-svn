import java.io.*;

/** This interface defines a Java communications interface to the MDSPlus server.*/
public interface MDSDataSource
{
    /** Connect to the remote MDS server.
        - Check that the source is not already connected<br>
        - Parse "source" to ensure that a port number is specified<br>
        - Send JAVA_USER message<br>
        @param serverAddrCPort The server address and port separated by a colon
        @throws IOException */
    public void connect(String serverAddrCPort ) throws IOException;

    /** Close the socket, set stream handles to null and connected flags to false.
        @throws IOException */
    public void disconnect() throws IOException;

    /** Open the socket by sending message "JAVAOPEN(experiment,shot)"<br>
        - If open is not successful then the Descriptor returned by the 
          server is null. Otherwise the open is successful.
        - Check that we are connected but not already open.
        - Set flags
    @throws IOException */
    public void open(String experiment, int shot) throws IOException;

    /** Send message "JAVACLOSE(experiment,shot)"<br>
        - Set flags
        @throws IOException */
    public void close() throws IOException;

    /** Evaluate an expression by sending a string to the server.  
        If not null, the result will be contained in the data field of the 
        MDSDescriptor returned by MDSPlus.<br>
        - Check that we are connected and open
        @throws IOException
        @return MDSDescriptor returned by MDSPlus
    */
    public MDSDescriptor evaluate(String expression) throws IOException;

    // get and set and get-status methods
    public boolean isConnected();

    public boolean isOpen();

    /** Returns server address and port number separated by a colon*/  
    public String getSource();

    public String getExperiment();

    public int getShot();


}
