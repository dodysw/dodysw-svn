package comp2100.oops.scanner;

import java.io.IOException;
import java.io.StringReader;
import junit.framework.*;

/**
 * Various test cases for Scanner class
 *  
 * @author $Author: u4267771 $
 * @version $Rev: 569 $
 * $Date: 2006-04-06 18:48:53 +1000 (Thu, 06 Apr 2006) $
 */

public class TestScanner extends TestCase {

	public static void main(String[] args) {
		junit.swingui.TestRunner.run(TestScanner.class);
	}
	
	/**
	 * When a new scanner object is created and attached to an input stream, the
	 * first call to item shall return the first token in the input stream (if
	 * any).
	 * 
	 */
	public void testFirstCall_1() throws IOException {
		String input = "<office:document-content><office:script/><office:font-decls></office:font-decls></office:document-content>";
		String expectedResult = "TAG\n" + "   TYPE = START\n"
				+ "   NAME = \"office:document-content\"\n";
		Scanner scanner = new Scanner(new StringReader(input));
		Token token = scanner.item();

		assertEquals(token.toString(), expectedResult);
	}

	/**
	 * After stripping out comments, processing instructions and the document
	 * type declaration, the scanner shall remove any white space left at the
	 * head of the document. This means that the first token returned must not
	 * be a character data token that starts with white space
	 * 
	 */
	public void testNoWhiteSpace_2() throws IOException {
		String input = "       <office:document-content><office:script/><office:font-decls></office:font-decls></office:document-content>";
		String expectedResult = "TAG\n" + 
			"   TYPE = START\n" + 
			"   NAME = \"office:document-content\"\n";
		Scanner scanner = new Scanner(new StringReader(input));
		Token token = scanner.item();
		assertEquals(token.toString(), expectedResult);
	}

	/**
	 * The syntax for a comment is: Comment ::= '<!--' Character* '-->' where
	 * the characters in between may contain anything except the sequence "-->".
	 * Comments must be stripped out of the input stream and ignored
	 * 
	 */
	public void testNoComment_3() throws IOException {
		String input = "<!--THIS IS A COMMENT--><office:document-content><office:script/><office:font-decls></office:font-decls></office:document-content>";
		String expectedResult = "TAG\n" + "   TYPE = START\n"
				+ "   NAME = \"office:document-content\"\n";
		Scanner scanner = new Scanner(new StringReader(input));
		Token token = scanner.item();
		assertEquals(token.toString(), expectedResult);
	}

	/**
	 * The syntax above (testNoComment_3) implies that nested comments must not
	 * be recognised
	 * 
	 */
	public void testNoNestedComment_4() throws IOException {
		String input = "<!--THIS IS A COMMENT <!-- NESTED COMMENT --> THIS MUST NOT BE COMMENT --><office:document-content><office:script/><office:font-decls></office:font-decls></office:document-content>";
		String expectedResult = "CHARACTER DATA\n" + 
			"   LENGTH = 28\n" +
			"   CONTENT = \"THIS MUST NOT BE COMMENT -->\"";

		Scanner scanner = new Scanner(new StringReader(input));
		Token token = scanner.item();
		assertEquals(expectedResult, token.toString());
	}
	
	/**
	 * There may be tags inside comments; these should be ignored along with the
	 * rest of the comments
	 * 
	 */
	public void testNoTagsInsideComment_5() throws IOException {
		String input = "<!--THIS IS A COMMENT <this:should_be_ignored/>--><office:document-content><office:script/><office:font-decls></office:font-decls></office:document-content>";
		String expectedResult = "TAG\n" + 
		"   TYPE = START\n" + 
		"   NAME = \"office:document-content\"\n";

		Scanner scanner = new Scanner(new StringReader(input));
		Token token = scanner.item();
		assertEquals(expectedResult, token.toString());
	}
	
	/**
	 * The syntax for a processing instruction is	 * 
	 * Processing instruction ::= '<?' Character* '?>'	 * 
	 * where the characters in between may contain anything except the sequence
	 * "?>".	 * 
	 * Processing instructions (including the XML declaration at the head of the
	 * document if any) must be removed in the same way as comments.
	 * 
	 */
	public void testNoProcessingInstruction_6() throws IOException {
		String input = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><office:document-content><office:script/><office:font-decls></office:font-decls></office:document-content>";
		String expectedResult = "TAG\n" + 
		"   TYPE = START\n" + 
		"   NAME = \"office:document-content\"\n";

		Scanner scanner = new Scanner(new StringReader(input));
		Token token = scanner.item();
		assertEquals(expectedResult, token.toString());
	}

	/**
	 * The syntax for the document type declaration is:
	 * 
	 * Doctype declaration ::= '<!DOCTYPE' Character* Internal? Character* '>'
	 * Internal ::= '[' Character* ']'
	 * 
	 * The characters between "<!DOCTYPE" and the optional internal section can
	 * be anything except the ">" character. The optional internal section is
	 * enclosed in square brackets and may include "<" and ">" characters but
	 * not the "]" character. The part after this ends with a ">" character.
	 * This is more complicated than an ordinary tag because there may be ">"
	 * characters inside the square brackets.
	 * 
	 * The document type declaration must also be removed.
	 * 
	 */
	public void testNoDTD_7() throws IOException {
		String input = "<!DOCTYPE office:document-content PUBLIC \"-//OpenOffice.org//DTD OfficeDocument 1.0//EN\" \"office.dtd\"><office:document-content><office:script/><office:font-decls></office:font-decls></office:document-content>";
		String expectedResult = "TAG\n" + 
		"   TYPE = START\n" + 
		"   NAME = \"office:document-content\"\n";

		Scanner scanner = new Scanner(new StringReader(input));
		Token token = scanner.item();
		assertEquals(expectedResult, token.toString());
	}
	
	/**
	 * A white space character is a tab, newline, carriage return or space
	 * character. The scanner shall replace every sequence of one or more
	 * consecutive white space characters in the input stream with a single
	 * space character (ASCII 32)(8).
	 * 
	 */
	public void testNoMultipleWhiteSpaces_8() throws IOException {
		String input = "<office:document-content>		  \n\n</office:document-content>";
		String expectedResult = "CHARACTER DATA\n" + 
		"   LENGTH = 1\n" +
		"   CONTENT = \" \"";

		Scanner scanner = new Scanner(new StringReader(input));
		scanner.advance();
		Token token = scanner.item();
		assertEquals(expectedResult, token.toString());
	}
	
	/**
	 * The syntax for a tag is:
	 * 
	 * Tag ::= '<' ('/')? Name (' ' Attribute)* ('/')? '>'
	 * 
	 * where the name is a string of letters, digits and the characters '-',
	 * ':', '_', starting with a letter.
	 * 
	 * The type printed must match the type of the tag correctly. That is, if
	 * the tag has the form <.../> then it is an empty tag, and the output must
	 * say "TYPE = Empty". If it has the form </...> then it is and end tag and
	 * the output must say "TYPE = End". If it has no slash character, so that
	 * its form is simply <...>, then it is a start tag, and the output must say
	 * "TYPE = Start".
	 * 
	 */
	public void testTagType_10() throws IOException {
		String input = "<office:document-content><office:script/></office:document-content>";

		Scanner scanner = new Scanner(new StringReader(input));
		assertEquals(true, ((Tag) scanner.item()).isStartTag());
		
		scanner.advance();
		assertEquals(true, ((Tag) scanner.item()).isEmptyElement());
		
		scanner.advance();
		assertEquals(true, ((Tag) scanner.item()).isEndTag());
	}
	
	/**
	 * The syntax for a tag is:
	 * 
	 * Tag ::= '<' ('/')? Name (' ' Attribute)* ('/')? '>'
	 * 
	 * where the name is a string of letters, digits and the characters '-',
	 * ':', '_', starting with a letter.
	 * 
	 * The tag name must be reproduced correctly.
	 */
	
	public void testTagName_11() throws IOException {
		String input = "<office:document-conte_nt></o!ffice:script/><4office:document-content>";

		Scanner scanner = new Scanner(new StringReader(input));

		assertEquals("office:document-conte_nt", ((Tag) scanner.item()).getName());
		
		scanner.advance();
		assertEquals("o!ffice:script", ((Tag) scanner.item()).getName());
		
		scanner.advance();
		assertEquals("4office:document-content", ((Tag) scanner.item()).getName());
	}
	
}
