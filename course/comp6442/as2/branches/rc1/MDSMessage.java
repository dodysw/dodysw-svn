/**
 * Dody Suria Wijaya - u4267771
 * Australian National University
 */

import java.io.*;
import java.util.zip.*;

/** Provides low-level communication with the MDSPlus server.
    Note for students: Because this class is low-level it is "ugly". 
    You do not need to understand the details.*/
public class MDSMessage
{
    private static int msgid = 0;
    /* Constants for bit-masking. */
    public static final int SUPPORTS_COMPRESSION = 0x8000;
    public static final byte SENDCAPABILITIES = 0x0F;
    public static final byte COMPRESSED = 0x20;
    public static final byte BIG_ENDIAN_MASK = (byte)0x80;
    public static final byte SWAP_ENDIAN_ON_SERVER_MASK = 0x40;
    public static final byte JAVA_CLIENT = 3 | BIG_ENDIAN_MASK | SWAP_ENDIAN_ON_SERVER_MASK;
    /* Other constants */
    public static final String EVENTASTREQUEST = "---EVENTAST---REQUEST---";
    public static final String EVENTCANREQUEST = "---EVENTCAN---REQUEST---";

    private int msglen;
    private int status;
    private short length;
    private byte nargs;
    private byte descr_idx;
    private byte message_id;
    private byte dtype;

    private byte client_type;
    private byte ndims;
    private int dims[];
    private byte body[]; // body is a byte array

    private boolean compressed = false;//Is the file compressed?

    /* Constructs an MDSMessage object containing
       a "message" string which will get sent to the MDSplus server.
       The protocol is to send this message and then to receive the output
       data from MDSplus.
    */
    public MDSMessage( String expr )
    {
        int i;
        status = 0;
        length = (short)expr.length();
        nargs = 1;
        descr_idx = 0;
        ndims = 0;
        dims = new int[MDSDescriptor.MAX_DIM];
        for ( i = 0; i < MDSDescriptor.MAX_DIM; i ++ )
        {
            dims[i] = 0;
        }
        dtype = MDSDescriptor.DTYPE_CSTRING;
        client_type = JAVA_CLIENT; //Java client is "big-endian"
        body = expr.getBytes();
    }

    /* Send the message contained by this MDSMessage object */ 
    public void send( DataOutputStream s ) throws IOException
    {
        int i;

        msglen = 48 + body.length;
        s.writeInt( msglen );
        s.writeInt( status );
        s.writeShort( (int)length );
        s.writeByte( nargs );
        s.writeByte( descr_idx );
        s.writeByte( msgid++ );
        s.writeByte( dtype );
        s.writeByte( client_type );
        s.writeByte( ndims );
        for ( i = 0; i < MDSDescriptor.MAX_DIM; i ++ )
        {
            s.writeInt( dims[i] );
        }
        s.write( body, 0, length );
        s.flush();

        message_id ++;
    }

    /* Receive the response from the MDSplus server. Decode and store
       the header of this message */
    public void receive( DataInputStream s ) throws IOException
    {
        byte header[] = new byte[16 + 4 * MDSDescriptor.MAX_DIM];
        int i;

        readBytes( header, s );

        client_type = header[14];
        compressed = ((client_type & COMPRESSED) == COMPRESSED );

        msglen = byteArrayToInt( header, 0);
        status = byteArrayToInt( header, 4);
        length = byteArrayToShort( header, 8);

        nargs     = header[10];
        descr_idx = header[11];
        message_id = header[12];
        dtype = header[13];
        client_type = header[14];
        ndims = header[15];

        for ( i = 0; i < MDSDescriptor.MAX_DIM; i ++ )
        {
            dims[i] = byteArrayToInt( header, 16 + 4*i);
        }

        if ( msglen > 48 )
        {
            if ( compressed )
            {
                body = readCompressedBytes( msglen - 52, s );

            }
            else
            {
                body = new byte[msglen - 48];
                readBytes( body, s );
            }

        }
        else
        {
            body = new byte[0];
        }
    }

    /* Convert the message to an MDSDescriptor object.*/
    public MDSDescriptor toDescriptor()
    {
        MDSDescriptor desc;

        desc = new MDSDescriptor();

        desc.setDtype(dtype);
        switch ( dtype )
        {
            case MDSDescriptor.DTYPE_CSTRING:
                desc.setCstringData(new String( body ));
            break;

            case MDSDescriptor.DTYPE_CHAR:
                desc.setCharData(new String( body ));
            break;

	    case MDSDescriptor.DTYPE_WORDU: //unsigned word 
                desc.setIntData(convertBytesToShortToInt());
                desc.setDtype(MDSDescriptor.DTYPE_INT); 
            break;

            case MDSDescriptor.DTYPE_BYTE:
                desc.setByteData(body);
            break;

	    case MDSDescriptor.DTYPE_SHORT: // will be in error; need conversion
                desc.setIntData(convertBytesToShortToInt());
                desc.setDtype(MDSDescriptor.DTYPE_INT);
            break;

            case MDSDescriptor.DTYPE_INT:
                desc.setIntData(convertBytesToInt());
            break;

            case MDSDescriptor.DTYPE_FLOAT:
                desc.setDoubleData(convertBytesToFloatToDouble());
                desc.setDtype(MDSDescriptor.DTYPE_DOUBLE);
            break;

            case MDSDescriptor.DTYPE_DOUBLE:
		desc.setDoubleData(convertBytesToDouble());
            break;

            case MDSDescriptor.DTYPE_EVENT:
                desc.setEventData(new String( body ));
            break;

            default:
            // We'll let the caller handler invalid messages
                desc.setByteData(body);
            break;
        }

        return desc;
    }

    /* Read bytes from an input stream */
    private void readBytes( byte buffer[], DataInputStream s ) throws IOException
    {
        int bytes_to_read;
        int read_bytes;
        int offset;

        bytes_to_read = buffer.length;
        read_bytes = 0;
        offset = 0;

        while ( bytes_to_read > 0 )
        {

            read_bytes = s.read( buffer, offset, bytes_to_read );
            offset += read_bytes;
            bytes_to_read -= read_bytes;

        }
    }

    /* Read bytes from a compressed input stream */
    private byte[] readCompressedBytes ( int bytes, DataInputStream s ) throws IOException
    {
        int bytes_to_read;
        int read_bytes;
        int offset;

        byte out[];
        byte b4[];

        InflaterInputStream zs;

        b4 = new byte[4];
        readBytes( b4, s );
        bytes_to_read = byteArrayToInt( b4, 0);

        out = new byte[bytes_to_read];
        zs = new InflaterInputStream( s );
        offset = 0;

        while ( bytes_to_read > 0 )
        {
            read_bytes = zs.read( out, offset, bytes_to_read );
            offset += read_bytes;
            bytes_to_read -= read_bytes;
        }

        return out;
    }

    /* Convert message header bytes to ints*/
    private int byteArrayToInt( byte buffer[], int i)
    {
        return
            ( ( buffer[i + 0] & 0xff ) << 24 ) +
            ( ( buffer[i + 1] & 0xff ) << 16 ) +
            ( ( buffer[i + 2] & 0xff ) << 8 ) +
            ( ( buffer[i + 3] & 0xff ) << 0 );

    }

    /* Convert message header bytes to short ints*/
    private short byteArrayToShort( byte buffer[], int i)
    {
        return (short)(( ( buffer[i + 0] & 0xff ) << 8 ) +
            ( ( buffer[i + 1] & 0xff ) << 0 ));
    }

    /* Convert message "body" byte array to an int array */
    private int[] convertBytesToInt() 
    {
        int i;
        int j;
        int out[];

        out = new int[body.length / 4];

        for ( i = 0, j = 0; j < body.length; i ++, j += 4 )
        {
            out[i] =
	        ( ( body[j + 0] & 0xff ) << 24 ) + //& 0xff converts to int
                ( ( body[j + 1] & 0xff ) << 16 ) +
                ( ( body[j + 2] & 0xff ) << 8 ) +
                ( ( body[j + 3] & 0xff ) << 0 );
        }
        return out;
    }

    /* Convert message "body" byte array to shorts and then to an int array */
    private int[] convertBytesToShortToInt() 
    {
        int i;
        int j;
        int out[];

        out = new int[body.length / 2];

        for ( i = 0, j = 0; j < body.length; i ++, j += 2 )
        {
                out[i] =
                ( ( body[j + 0] & 0xff ) << 8) +
                ( ( body[j + 1] & 0xff ) << 0);
        }
	return out;
    }

    /* Convert message "body" byte array to floats and then to a double array */
    private double[] convertBytesToFloatToDouble()
    {
        int i;
        int j;
        int tmp;
        double outDouble[];

	outDouble = new double[body.length / 4];

        for ( i = 0, j = 0; j < body.length; i ++, j += 4 )
        {
            tmp =
                ( ( body[j + 0] & 0xff ) << 24) +
                ( ( body[j + 1] & 0xff ) << 16) +
                ( ( body[j + 2] & 0xff ) << 8) +
                ( ( body[j + 3] & 0xff ) << 0);
            outDouble[i] = Float.intBitsToFloat( tmp );
        }
        return outDouble;
    }

    /* Convert message "body" byte array to a double array */
    private double[] convertBytesToDouble()
    {
        long ch;
        double out[] = new double[body.length / 8];
        for(int i = 0, j = 0; i < body.length / 8; i++, j+=8)
        {
            ch  = (body[j+0] & 0xffL) << 56;
            ch += (body[j+1] & 0xffL) << 48;
            ch += (body[j+2] & 0xffL) << 40;
            ch += (body[j+3] & 0xffL) << 32;
            ch += (body[j+4] & 0xffL) << 24;
            ch += (body[j+5] & 0xffL) << 16;
            ch += (body[j+6] & 0xffL) << 8;
            ch += (body[j+7] & 0xffL) << 0;
            out[i] = Double.longBitsToDouble(ch);
        }
        return out;
    }

    public void setNargs(byte _nargs)
    {
        nargs = _nargs;
    }

}
