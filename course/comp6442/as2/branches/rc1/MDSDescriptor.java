/**
 * Dody Suria Wijaya - u4267771
 * Australian National University
 */


/** Used to store the response from the MDSPlus server */
public class MDSDescriptor
{
    public static final byte MAX_DIM       = 8;

    public static final byte DTYPE_CSTRING = 14;
    public static final byte DTYPE_CHAR    = 6;

    public static final byte DTYPE_BYTE    = 2;
    public static final byte DTYPE_SHORT   = 7; //converted to int
    public static final byte DTYPE_INT     = 8; 

    public static final byte DTYPE_FLOAT   = 10;//converted to double
    public static final byte DTYPE_DOUBLE  = 11;

    public static final byte DTYPE_WORDU   = 3; //"usigned word"; conv to int
    public static final byte DTYPE_EVENT   = 99;

    private byte   descriptorType;
      
    private byte   byteData[];
    private int    intData[]; 

    private double doubleData[];

    private String charData;
    private String cstringData;
    private String eventData;

    // Methods to set and get elemental values
    public void setByteDataElement(byte i_byteData, int index)
    {
        if (index>=0 && index<byteData.length)
            byteData[index] = i_byteData;
    }
    public byte getByteDataElement(int index)
    {
        if (index>=0 && index<byteData.length)
            return byteData[index];
        return byteData[0];  // may want to change this
    }

    public void setIntDataElement(int i_intData, int index)
    {
        if (index>=0 && index<intData.length)
            intData[index] = i_intData;
    }

    public int getIntDataElement(int index)
    {
        if (index>=0 && index<intData.length)
            return intData[index];
        return intData[0];  // may want to change this
    }

    public void setDoubleDataElement(double i_doubleData, int index)
    {
        if (index>=0 && index<doubleData.length)
            doubleData[index] = i_doubleData;
    }

    public double getDoubleDataElement(int index)
    {
        if (index>=0 && index<doubleData.length)
            return doubleData[index];
        return doubleData[0];  // may want to change this
    }

    // More set and get methods.  
    public void setDtype(byte _dtype){ descriptorType = _dtype;}

    public byte getDtype(){ return descriptorType;}

    public void setByteData(byte[] _byteData){ byteData = _byteData;}

    public byte[] getByteData(){ return byteData;}

    public void setIntData(int[] _intData){ intData = _intData;}

    public int[] getIntData(){ return intData;}

    public void setDoubleData(double[] _doubleData){ doubleData = _doubleData;}

    public double[] getDoubleData(){ return doubleData;}

    public void setCharData(String _charData){ charData = _charData;}

    public String getCharData(){ return charData;}

    public void setCstringData(String _cstringData){ cstringData = _cstringData;}

    public String getCstringData(){ return cstringData;}

    public void setEventData(String _eventData){ eventData = _eventData;}

    public String getEventData(){ return eventData;}
}
