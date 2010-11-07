package comp2100.oops.visitor;

import comp2100.oops.Assert;
import comp2100.oops.StringOps;
import comp2100.oops.tree.*;
import comp2100.oops.visitor.*;
import java.io.*;
import java.util.Enumeration;
import java.util.Hashtable;
import java.util.StringTokenizer;
import java.util.Vector;
import java.util.Iterator;

/**
 * Visitor that renders a tree as clean, simple HTML on a given
 * output stream.
 * 
 * @author Ian Barnes (original Eiffel version)
 * @author Alexei B Khorev (Java port)
 * @author $Author: u4267771 $
 * @version $Rev: 629 $
 * $Date: 2006-04-07 02:53:32 +1000 (Fri, 07 Apr 2006) $
 */
public class HTMLRenderer implements Visitor {
	
	/**
	 * Create a renderer which writes to an output stream
	 * and uses a dictionary of style names to create proper
	 * HTML tags.
	 *  
	 * @param d a dictionary of inherited style names
	 * @param s the stream we are writing to
	 */
	
	private boolean doOutput = true;
	private int idHeading = 0;
	private int currentHeadingId = 0;
	
	/* Add by MXZ for number the header*/
	private int iLevelOne = 0;
	private boolean bFirstHeading = false;
	private Vector v = new Vector();
	
	public HTMLRenderer(Hashtable d, MetadataExtractor m, 
			OutputStreamWriter s) {
		output = s;
		styleTable = d;
		metadata = m;
		styles = new Hashtable();
	}
	
	/**add by MXZ */
	private Vector headingVector;
	private String htmlDocName;
	
	/*
	 * add by MXZ
	 */
	public HTMLRenderer(  Hashtable d, MetadataExtractor m,
			OutputStreamWriter s, Vector<Headings> headingVector, String htmlDocName, int id) {
		output = s;
		styleTable = d;
		metadata = m;
		styles = new Hashtable();
		this.headingVector = headingVector;
		this.htmlDocName = htmlDocName;
		idHeading = id;
		
	}
	
	/** incremental number for footnote id*/
	private int lastFootnoteId = 1;
	
	/** array which stores footnotes */
	private Vector<Footnote> footnoteData = new Vector<Footnote>();
	
	
	/** stream we are writing to */
	private OutputStreamWriter output;
	
	/** style name inheritance lookup table */
	private Hashtable styleTable;
	
	/** metadata extractor */
	private MetadataExtractor metadata;
	
	
	
	/**
	 * Text style lookup table for formatting of span elements:
	 * keys are style names, values are the instructions for
	 * processing that style.
	 */
	private Hashtable styles;
	
	/** Formatting details accumulated for the current element */
	private String currentStyleProperties;
	
	/**
	 * Lookup table of what we put at the start of a paragraph,
	 * depending on its style name. Initialized in the static
	 * block to make it essentially a hard-wired constant class
	 * fields.
	 */
	private static Hashtable startTags = new Hashtable();
	
	/**
	 * Lookup table of what we put at the start of a paragraph,
	 * depending on its style name. Initialized in the static
	 * block to make it essentially a hard-wired constant class
	 * fields.
	 */
	private static Hashtable endTags = new Hashtable();
	
	/* Initialise the lookup tables */
	static {
		startTags.put("Title", "<H1 ALIGN=\"CENTER\">");
		startTags.put("Abstract", "<BLOCKQUOTE><FONT SIZE=\"-1\">");
		startTags.put("Author&apos;s Name", "<P ALIGN=\"CENTER\"><B>");
		startTags.put("Affiliation", "<P ALIGN=\"CENTER\">");
		startTags.put("Categories", "<P><FONT SIZE=\"-1\">");
		startTags.put("Text body", "<P>");
		startTags.put("Quoted Text", "<BLOCKQUOTE>");
		startTags.put("Numbered List", "<P>");
		startTags.put("Footnote", "<P><FONT SIZE=\"-1\">");
		startTags.put("Figure Caption", "<P ALIGN=\"CENTER\"><FONT SIZE=\"-1\">");
		startTags.put("Table Head", "<P ALIGN=\"CENTER\">");
		startTags.put("References", "<P>");
		startTags.put("Primary Head", "<H2>");
		startTags.put("Secondary Head", "<H3>");
		startTags.put("Displayed Equation", "<BLOCKQUOTE><I>");
		startTags.put("Initial Body Text", "<P>");
		
		endTags.put("Title", "</H1>");
		endTags.put("Abstract", "</FONT></BLOCKQUOTE>");
		endTags.put("Author&apos;s Name", "</B></P>");
		endTags.put("Affiliation", "</P>");
		endTags.put("Categories", "</FONT></P>");
		endTags.put("Text body", "</P>");
		endTags.put("Quoted Text", "</BLOCKQUOTE>");
		endTags.put("Numbered List", "</P>");
		endTags.put("Footnote", "</FONT></P>");
		endTags.put("Figure Caption", "</FONT></P>");
		endTags.put("Table Head", "</P>");
		endTags.put("References", "</P>");
		endTags.put("Primary Head", "</H2>");
		endTags.put("Secondary Head", "</H3>");
		endTags.put("Displayed Equation", "</I></BLOCKQUOTE>");
		endTags.put("Initial Body Text", "</P>");
	}
	
	/**
	 * Send a newline to the output.
	 */
	private void putNewLine() {
		try {
			output.write("\n");
		} catch (IOException e) {
			e.printStackTrace(System.err);
			System.exit(1);
		}
	}
	
	/**
	 * Block elements insert a blank line after. Other elements
	 * then know thatthey can start immediately. Inline elements
	 * do not insert anything before or after.
	 * 
	 * @param x the visited node of the parse tree whose
	 * strategy determines the type of visit method.
	 */
	public void visitDocument(XmlContainerElement x) {
		Assert.check(x.getName().equals("office:document-content"), 
		"Visiting wrong node...");
		addStartTag("HTML");
		putNewLine();
		addStartTag("HEAD");
		putNewLine();
		
		addStartTag("TITLE");
		addWord(metadata.getTitle());
		addEndTag("TITLE");
		putNewLine();
		
		addEmptyTag("META NAME=\"Title\" CONTENT=\"" +
				metadata.getTitle() + "\"");
		putNewLine();
		addEmptyTag("META NAME=\"Author&apos;s Name\" CONTENT=\"" +
				metadata.getAuthor() + "\"");
		putNewLine();
		addEmptyTag("META NAME=\"Affiliation\" CONTENT=\"" +
				metadata.getAffiliation() + "\"");
		putNewLine();
		addEndTag("HEAD");
		
		putNewLine();
		putNewLine();
		x.visitChildren(this);
		addEndTag("HTML");
		putNewLine();
	} 
	
	public void printFootnotes() {
		//iterate all footnotes		
		if (!footnoteData.isEmpty()) {
			addEmptyTag("HR");
			while (!footnoteData.isEmpty()) {
				Footnote f = footnoteData.firstElement();
				footnoteData.remove(0);
				addWord("<p><a name=\"footnote_target_" + f.id + "\"></a><a href=\"#footnote_orig_" + f.id + "\">[" + f.id + "]</a> " + f.text);
			}
		}
	}
	
	public void doHeading(XmlContainerElement x, String level) {
		
		doOutput = (idHeading == currentHeadingId);		
		/*
		 * Add by MXZ
		 * Add Previous,Home,Next Link into each html file
		 */
		if ( headingVector.size() >= 1 ) {
			
			boolean tB = doOutput;
			
			//if this the first heading, then show table of content first!
			if (currentHeadingId == 0 && idHeading == -1) {				
				doOutput = true;
				printToc();
				doOutput = tB;
			}
		
			int iTemp = 0;
			addWord( "<table width=\"200\" border=\"1\"><tr>" );
			
			/*Add Previous*/
			if( currentHeadingId > 0 ) {
				iTemp = currentHeadingId - 1;
				addWord( "<td width=\"68\">");
				addWord( "<a href=\"" + htmlDocName + "_" + iTemp + ".html\">" + " &lt;Previous</a>" );
				addWord( "</td> ");
			}
			
			/*Add Home*/
			addWord("<td width=\"34\"><a href=\"" + htmlDocName + ".html\">"+ "Top</a></td>");
			
			/*Add Next*/
			if( currentHeadingId < headingVector.size() - 1 ) {
				addWord( "<td width=\"46\">" );
				iTemp = currentHeadingId + 1;
				addWord( "<a href=\"" + htmlDocName + "_" + iTemp + ".html\">" + "Next&gt;</a>" );
				addWord("</td>");
			}
			
			/*Finish table*/
			addWord("</tr></table>");
			
			putNewLine();
			putNewLine();
			
			currentHeadingId++; //so next heading got correct ID}		
			
			/** add heading mark here add by MXZ*/
			/* Delete by MXZ*/
			//addWord("<a name=\"" + htmlDocName + currentHeadingId + "\"></a>");
			
			addStartTag("H" + level);
			x.visitChildren(this);
			addEndTag("H" + level);
			//a link back to top
			/*
			 * Changed by MXZ
			 * addWord("<a href=\"#" + htmlDocName  + currentHeadingId + "_top\">Back to top</a>");
			 */
		}
	}
	
	public void visitHeading(XmlContainerElement x) {
		Assert.check(x.getName().equals("text:h"), 
		"Visiting wrong node...");
		
		String level;	
		if (x.attributes.has("text:level")) {
			level = x.attributes.at("text:level");
		} else {
			level = "2";
			System.err.println("Error: Heading without level!!");
		}
		
		doHeading(x, level);
		
		
	}	
	public void visitAutomaticStyles(XmlContainerElement x) {
		Assert.check(x.getName().equals("office:automatic-styles"), 
		"Visiting wrong node...");
		x.visitChildren(this);
	}
	
	public void visitStyle(XmlContainerElement x) {
		Assert.check(x.getName().equals("style:style"), 
		"Visiting wrong node...");
		String name;
		if (x.attributes.has("style:family") && 
				x.attributes.at("style:family").equals("text") &&
				x.attributes.has("style:name")) {
			name = x.attributes.at("style:name");
			currentStyleProperties = new String();
			x.visitChildren(this);
			styles.put(name, currentStyleProperties);
		}
	}
	
	public void visitStyleProperties(XmlContainerElement x) {
		Assert.check(x.getName().equals("style:properties"), 
		"Visiting wrong node...");
		if (x.attributes.has("fo:font-style")) {
			currentStyleProperties += x.attributes.at("fo:font-style");
		}
		if (x.attributes.has("fo:font-weight")) {
			currentStyleProperties += x.attributes.at("fo:font-weight");
		}
		if (x.attributes.has("fo:font-variant")) {
			currentStyleProperties += x.attributes.at("fo:font-variant");
		}
	}
	
	public void printToc() {
		
		if ( idHeading == -1 || idHeading == -2 ) {
			doOutput = true;
		}	
		else {
			doOutput = false;
		}
		/*
		 * Add by MXZ
		 * For adding Header number
		 */
		float[] fLastHeading = new float[2];
		fLastHeading[0] = 0;
		fLastHeading[1] = 0;
		boolean bFirstHeading = false;
		
		/** add heading List here add by MXZ*/ 
		//dsw:moved this block to visitBody method
		if (!headingVector.isEmpty()) {//dsw:only do this if heading vector is not empty
			addWord("<H2>Table of Content</H2>");
			addStartTag("UL");
			int iLastLevel = ((Headings)headingVector.get(0)).getLevel();
			int iCurrentLevel = 0;
			int id = 0;
			/*
			 * Add by MXZ
			 * For adding Header number
			 */
			String strHeadingNumber = "";
			int iTimes = 1;
			
			for (Iterator it = headingVector.iterator(); it.hasNext(); id++) {
				Headings headingsTemp = ( Headings )it.next();
				iCurrentLevel = headingsTemp.getLevel();
				
				if( iCurrentLevel < iLastLevel ) {
					for (int i=0; i < iLastLevel - iCurrentLevel; i++ ) {
						addEndTag("UL");
					}
				}
				
				if ( iCurrentLevel > iLastLevel ) {
					addStartTag("UL");	
				}
				iLastLevel = iCurrentLevel;
				addStartTag ("LI");
				
				if( idHeading == -2 ) {
					doOutput = false;
				}
				
				//anchor link as target from part
				addWord("<a name=\"" + htmlDocName + id + "_top\"></a>");
				
				/*
				 * Changed by MXZ
				 * addStartTag("A HREF=\#" + htmlDocName  + id + ".html");
				 */
				addStartTag("A HREF=\"" + htmlDocName + "_" + id + ".html\"");
				
				/*
				 * Add by MXZ
				 * For adding Header number
				 */				
				float fHeadingNumber = 1;
				int i = 0; 
				if ( !bFirstHeading && iCurrentLevel != 1) {
					for( i = 1; i < iCurrentLevel; i++ ){
						fHeadingNumber += 10 * i;
					}
					bFirstHeading = true;
				}
				else if ( !bFirstHeading && iCurrentLevel == 1 ) { 
					fHeadingNumber = 1;
					bFirstHeading = true;
				}
				else {    
					if ( iCurrentLevel == fLastHeading[1] ) {
						fHeadingNumber = ++ fLastHeading[0]; 
					}
					else if ( iCurrentLevel > fLastHeading[1] ){
						fHeadingNumber = fLastHeading[0]* ((int)(java.lang.Math.pow(10,( iCurrentLevel - (int)fLastHeading[1] )))) + 1;
					}
					else {
						fHeadingNumber = (float) ((int)(fLastHeading[0]* ((float)(java.lang.Math.pow(10, ( iCurrentLevel - (int)fLastHeading[1] ))))) + 1.0); 
					}
				}
				
				fLastHeading[0] = fHeadingNumber;
				fLastHeading[1] = iCurrentLevel;
				
				int iResult = (int)(fHeadingNumber/( (int)(java.lang.Math.pow(10,( iCurrentLevel - 1 )) ))); 
				strHeadingNumber = Integer.toString( iResult ); 
				fHeadingNumber -= (float)(iResult * ((int)(java.lang.Math.pow(10,(iCurrentLevel -1))))) ;
				
				for( int j = iCurrentLevel - 2; j > 0; j-- ) {
					iResult = (int)(fHeadingNumber/( (java.lang.Math.pow(10, j ))));
					strHeadingNumber += "." + iResult; 
					fHeadingNumber -= iResult * ((int)(java.lang.Math.pow(10, j)));
				} 
				
				if( iCurrentLevel != 1 ) {
					strHeadingNumber += "." + (int)fHeadingNumber;		    
				}
				
				
				if ( idHeading == -1 || idHeading == -2 ) {
					doOutput = true;
				}    
				if ( idHeading == -2 ) {
					strHeadingNumber = "";
				}
				String strTemp = strHeadingNumber + " " + headingsTemp.getTitle();
				addWord( strTemp );				
				addEndTag("A");
				addEndTag("LI");
			}	
			addEndTag("UL");
		}
	}
	
	public void visitBody(XmlContainerElement x) {
		Assert.check(x.getName().equals("office:body"), 
		"Visiting wrong node...");
		addStartTag("BODY");
		putNewLine();
		
		//disable output if this is not TOC, since we dont want "metadata" info shown on non TOC files
		if (idHeading >= 0) {
			doOutput = false;
		}
		
		x.visitChildren(this);
		putNewLine();
		
		
		
		//turn it back on for </body></html> tag and potential footnotes
		doOutput = true;
		
		printFootnotes();
		
		addEndTag("BODY");
	}	
	public void visitParagraph(XmlContainerElement x) {
		Assert.check(x.getName().equals("text:p"), 
		"Visiting wrong node...");
		
		if (x.isHorizontalLine) {
			addEmptyTag("HR");
			//dont visit children if paragraph deemed as HR
			return;
		}
		
		String styleName;
		if (x.children.size() > 0) {
			if (x.attributes.has("text:style-name")) {
				styleName = x.attributes.at("text:style-name");
				if (styleTable.containsKey(styleName)) {
					styleName = (String) styleTable.get(styleName);
				}
				
				//note, Lecturer change requirements!
				//1st level heading now: <text:p text:style-name="Primary Head">1. INTRODUCTION</text:p>
				//2nd level heading now: <text:p text:style-name="Secondary Head">1. INTRODUCTION</text:p>
				String ilevel = "";
				if (styleName.equals("Primary Head")) {
					ilevel = "1";
					doHeading(x, ilevel);
				}
				else if (styleName.equals("Secondary Head")) {
					ilevel = "2";
					doHeading(x, ilevel);
				}
				else {
					//normal paragraph
					
					if (startTags.containsKey(styleName)) {
						addWord((String) startTags.get(styleName));
					} else {
						addStartTag("P");
					}
					x.visitChildren(this);
					if (endTags.containsKey(styleName)) {
						addWord((String) endTags.get(styleName));
					} else {
						addEndTag("P");
					}
				}
			} else {
				addStartTag("P");
				x.visitChildren(this);
				addEndTag("P");
			}
			putNewLine();
			putNewLine();
		}
	}
	
	public void visitUnorderedList(XmlContainerElement x) {
		Assert.check(x.getName().equals("text:unordered-list"), 
		"Visiting wrong node...");
		addStartTag("UL");
		putNewLine();
		x.visitChildren(this);
		putNewLine();
		addEndTag("UL");
		putNewLine();
	}
	
	public void visitOrderedList(XmlContainerElement x) {
		Assert.check(x.getName().equals("text:ordered-list"), 
		"Visiting wrong node...");
		
		if (x.attributes.has("text:continue-numbering") &&
				x.attributes.at("text:continue-numbering").equals("true")) {
			addStartTag("OL START=\"" + listItemNumber + "\"");
		} else {
			addStartTag("OL");
			listItemNumber = 1;
		}
		
		putNewLine();
		x.visitChildren(this);
		putNewLine();
		addEndTag("OL");
		putNewLine();
	}
	
	private int listItemNumber;
	
	public void visitListItem(XmlContainerElement x) {
		Assert.check(x.getName().equals("text:list-item"), 
		"Visiting wrong node...");
		addStartTag("LI");
		putNewLine();
		x.visitChildren(this);
		addEndTag("LI");
		putNewLine();
		listItemNumber++;
	}
	
	public void visitSpan(XmlContainerElement x) {
		Assert.check(x.getName().equals("text:span"), 
		"Visiting wrong node...");
		String style, properties = new String();
		boolean known = false;
		if (x.attributes.has("text:style-name")) {
			style = x.attributes.at("text:style-name");
			if (styles.containsKey(style)) {
				properties = (String) styles.get(style);
				known = true;
			}
		}
		if (known) {
			if (StringOps.hasSubstring(properties, "italic")) {
				addStartTag("I");
			}
			if (StringOps.hasSubstring(properties, "bold")) {
				addStartTag("B");
			}
			if (StringOps.hasSubstring(properties, "small-caps")) {
				addStartTag("SPAN STYLE=\"font-variant: small-caps\"");
			}
		}
		x.visitChildren(this);
		if (known) {
			if (StringOps.hasSubstring(properties, "bold")) {
				addEndTag("B");
			}
			if (StringOps.hasSubstring(properties, "italic")) {
				addEndTag("I");
			}
			if (StringOps.hasSubstring(properties, "small-caps")) {
				addEndTag("SPAN");
			}
		}
	}
	
	public void visitAnchor(XmlContainerElement x) {
		Assert.check(x.getName().equals("text:a"), 
		"Visiting wrong node...");
		String target;
		if (x.attributes.has("xlink:href")) {
			target = x.attributes.at("xlink:href");     
			addStartTag("A HREF=\"" + target + "\"");
			x.visitChildren(this);
			addEndTag("A");
		} else {
			x.visitChildren(this);
		}
	}
	
	public void visitSpace(XmlContainerElement x) {
		Assert.check(x.getName().equals("text:s"), 
		"Visiting wrong node...");
		addWord(new String(" "));
	}
	
	public void visitLineBreak(XmlContainerElement x) {
		Assert.check(x.getName().equals("text:line-break"), 
		"Visiting wrong node...");
		addEmptyTag("BR");
		putNewLine();
	}
	
	/** visit an unspecified element */
	public void visitUnknown(XmlContainerElement x) {
		x.visitChildren(this);
	}
	
	/**
	 * Visit a data item (the actual content)
	 * @param data The XmlDataElement to visit
	 */
	public void visitData(XmlDataElement data) {
		if (data.isFootnote) {
			
			//in splited file, only add footnote if this is the current heading body
			//otherwise, always add it
			if (idHeading == -2 || idHeading == currentHeadingId-1) {
				boolean b = doOutput;
				doOutput = true;
				addWord("<A name=\"footnote_orig_" + lastFootnoteId + "\"></A><A href=\"#footnote_target_" + lastFootnoteId + "\">[" + lastFootnoteId + "]</A>");
				doOutput = b;
				Footnote f = new Footnote(lastFootnoteId, data.content);
				footnoteData.add(f);
				lastFootnoteId++;
			}
			
		}
		else
			addWord(data.content);
	}
	
	/**
	 * Add word w to the output.
	 * 
	 * @param w the string to be added
	 */
	private void addWord(String w) {
		if (w != null && doOutput == true) {
			try {
				output.write(w);
			} catch (IOException e) {
				System.out.println("IO Exception thrown");
				System.exit(1);
			}
		}
	}
	
	/* auxiliary methods for writing tags and comments */
	
	private void addStartTag(String s) {
		addWord("<" + s + ">");
	}
	
	private void addEndTag(String s) {
		addWord("</" + s + ">");
	}
	
	private void addEmptyTag(String s) {
		addWord("<" + s + " />");
	}
	
	private void addComment(String s) {
		putNewLine();
		addWord("<!-- " + s + " -->");
		putNewLine();
	}
}
