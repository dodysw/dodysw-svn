package comp2100.oops;

import java.io.*;
import java.util.zip.ZipEntry;
import java.util.zip.ZipInputStream;

import comp2100.oops.scanner.Scanner;
import comp2100.oops.tree.XmlContainerElement;
import comp2100.oops.visitor.*;

/** 
 * Root class for the Oops system.
 *
 * Open an Open Office (zipped xml file) document, then scan,
 * parse, build a tree and render it in a number of formats.
 *
 * @author Ian Barnes (original Eiffel code)
 * @author Alexei B Khorev (Java port)
 * @author Modified by $Author: u4267771 $
 * @version $Rev: 629 $
 * $Date: 2006-04-07 02:53:32 +1000 (Fri, 07 Apr 2006) $
 */
public class Converter {
    
    final static String fileName = "content.xml";
    
    public static void main(String[] args) throws IOException {
        
        System.out.println("This is Oops - the Open Office Publishing System");
        
        Scanner scanner; // The scanner object the parser talks to
        XmlContainerElement rootElement; // The root of the XML parse tree
        TextRenderer textRenderer; // plain text renderer
        HTMLRenderer htmlRenderer; // HTML renderer
        
        /* input and output streams */
        ZipInputStream zis = null;
        ZipEntry ze = null;
        BufferedReader br = null;
        OutputStreamWriter output = null;
        
        int i, c = 0;
        String docName, baseName = new String();
        
        if (args.length != 1) {
            System.err.println("Usage: java Converter file.sxw");
            System.exit(1);
        }
        
        docName = args[0];
        try {
            zis = new ZipInputStream(new BufferedInputStream(new FileInputStream(args[0])));
        } catch (FileNotFoundException f) {
            System.err.println(f.getMessage() + " File not found, exiting...");
            System.exit(1);
        }       
        
        if (docName.endsWith(".sxw")) {
            i = docName.lastIndexOf(".sxw");
            baseName = docName.substring(0,i);
            docName = baseName + ".xml";
            try {
                ze = zis.getNextEntry();
                while (!ze.getName().equals("content.xml")) {
                    ze = zis.getNextEntry();
                }
            } catch (IOException e) {
                System.err.println("Exception: " + e.getMessage());
            }
        }
        
        System.out.println("Creating the scanner which feeds on the input stream");
        br  = new BufferedReader(new InputStreamReader(zis));
        scanner = new Scanner(br);
        
        System.out.println("Parsing the input and creating the root element");
        rootElement = new XmlContainerElement();

        try {
            rootElement.parse(scanner);
        } catch (Exception e) {
            e.printStackTrace();
	    System.exit(1);
        }
        
        if (rootElement == null) {
            System.out.println("Document tree is empty! Quitting...");
            System.exit(1);
        }
        
	
        System.out.println("Gathering style information");
        StyleDecoder styleDecoder = new StyleDecoder();
        rootElement.accept(styleDecoder);
        System.out.println("Style information gathered:");
        System.out.println(styleDecoder.toString());
        
		System.out.println("Removing bad paragraphs");
		TreeFixer treeFixer = new TreeFixer(styleDecoder.lookupTable);
		rootElement.accept(treeFixer);
		
		System.out.println("Removing empty tags and merging ordered list");
		OrderListMerger orderListMerger = new OrderListMerger(styleDecoder.lookupTable);
		rootElement.accept(orderListMerger);		
		
        System.out.println("Extracting metadata");
		MetadataExtractor metadataExtractor = new MetadataExtractor(styleDecoder.lookupTable);
        rootElement.accept(metadataExtractor);
        System.out.println("Metadata information collected:");
        metadataExtractor.printMetadata();
        
		System.out.println("Creating TOC");
		HeadingsDecoder headingsDecoder = new HeadingsDecoder(styleDecoder.lookupTable);
		rootElement.accept(headingsDecoder);

		
        docName = baseName + ".txt";
        System.out.println("Opening " + docName + " for plain text output");
        try { 
            output = new OutputStreamWriter(new BufferedOutputStream(new FileOutputStream(docName)));
        } catch (FileNotFoundException e) {
            System.err.println("Cannot open " + docName + " for output");
        } 
        System.out.println("Creating the text renderer.");
        textRenderer = new TextRenderer(output); 
        System.out.println("Writing the plain text file");
        rootElement.accept(textRenderer);
        System.out.println("Finished writing the plain text file");
        output.close();
        
        docName = baseName + ".xml";
        System.out.println("Opening " + docName + " for XML output");
        try { 
            output = new OutputStreamWriter(new BufferedOutputStream(new FileOutputStream(docName)));
        } catch (FileNotFoundException e) {
            System.err.println("Cannot open " + docName + " for output");
        } 
        System.out.println("Writing the XML file");
        rootElement.prettyPrint(0, output);
        System.out.println("Finished writing the XML file");
        output.close();
        
        //only do TOC and HTML split thing if there is at least 1 level-1 heading.
        int iID;	//default condition
        if ( headingsDecoder.HeadingList.size() >= 1 ) {
            //writing TOC             
            docName = baseName + ".html";
            System.out.println("Heading in document! Opening TOC " + docName + " for HTML output");
            try { 
                output = new OutputStreamWriter(new BufferedOutputStream(new FileOutputStream(docName)));
            } catch (FileNotFoundException e) {
                System.err.println("Cannot open " + docName + " for output");
            }
            iID = -1;
        }
        else {
        	//writing normal file
            docName = baseName + ".html";
            System.out.println("Heading not in document! Opening normal HTML file " + docName + " for HTML output");
            try { 
                output = new OutputStreamWriter(new BufferedOutputStream(new FileOutputStream(docName)));
            } catch (FileNotFoundException e) {
                System.err.println("Cannot open " + docName + " for output");
            }
            iID = -2;	//signify that no heading found thus fallback to normal non-TOC/split thing
        }
        
        System.out.println("Creating the HTML renderer.");
        
        String baseRelativeName = baseName.substring(baseName.lastIndexOf('/')+1);        
        
        htmlRenderer = new HTMLRenderer(styleDecoder.lookupTable, 
        		metadataExtractor, output, headingsDecoder.HeadingList, baseRelativeName, iID);
        System.out.println("Writing the HTML file");
        rootElement.accept(htmlRenderer);
        System.out.println("Finished writing the HTML file");
        output.close();

               
        /*Save different headings and their content into seperate file when there
         * are more than one heading
         */
        if ( iID == -1 ) {
	        for (int id=0; id < headingsDecoder.HeadingList.size(); id++) {      
		        docName = baseName + "_" + id + ".html";
		        System.out.println("Opening " + docName + " for HTML output");
		        try { 
		            output = new OutputStreamWriter(new BufferedOutputStream(new FileOutputStream(docName)));
		        } catch (FileNotFoundException e) {
		            System.err.println("Cannot open " + docName + " for output");
		        }
		        System.out.println("Creating the HTML renderer.");
		        
		        /*
		         * Changed by MXZ
		         * htmlRenderer = new HTMLRenderer(styleDecoder.lookupTable,
				 *	      metadataExtractor, output, headingsDecoder.HeadingList, "hdg", id);*/
		     
		        htmlRenderer = new HTMLRenderer(styleDecoder.lookupTable,
				metadataExtractor, output, headingsDecoder.HeadingList, baseRelativeName , id);
		        
		        System.out.println("Writing the HTML file");
		        rootElement.accept(htmlRenderer);
		        System.out.println("Finished writing the HTML file");
		        output.close();
	        }      
        }     
    }
}
