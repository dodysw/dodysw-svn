/*
 * COMP2100/COMP6442 Homework #1
 * by Dody Suria Wijaya <dodysw@gmail.com>
 * u4267771
 */

import java.io.*;

class CharacterTable {
    int[] ascii_code;
    int all_length;
    void populateSequence() {
        //populate ascii sequence
        all_length = (128-32)+(256-160);
        ascii_code = new int[all_length];
        int n = 0;
        for (int i=32; i < 128; i++) {
            ascii_code[n] = i;
            n++;
        }
        for (int i=160; i < 256; i++) {
            ascii_code[n] = i;
            n++;
        }
    }

    String getTable() throws Exception {
        return getTable(6);
    }

    String getTable(int colnum) throws Exception {
        int row_length = all_length/colnum;
        int[][] matrix = new int[row_length][colnum];

        //split into @colnum number
        for (int i=0; i<row_length; i++) {
            for (int j=0; j<colnum; j++) {
                matrix[i][j] = ascii_code[i+row_length*j];
            }
        }

        //prepare stream to convert Unicode character stream (used internaly by Java) to byte stream for output
        ByteArrayOutputStream baos = new ByteArrayOutputStream();
        OutputStreamWriter osw = new OutputStreamWriter(baos, "ISO-8859-1");
        PrintWriter p = new PrintWriter(osw);       //we need the format/println method

        //write output to our stream
        for (int i=0; i < matrix.length; i++) {
            for (int j=0; j < matrix[i].length; j++) {
                int code = matrix[i][j];
                if (code != 127)
                    p.format("%s %s", code, (char) code);
                else
                	p.write("    ");
                p.write("\t");
            }
            p.println();
        }
        p.flush();

        return baos.toString();
    }
}



public class Homework1 {
    public static void main ( String args[] ) throws Exception {
        CharacterTable ct = new CharacterTable();
        ct.populateSequence();
        byte[] output = ct.getTable(6).getBytes();
        System.out.write( output, 0, output.length );
        System.exit( 0 );
    }
}