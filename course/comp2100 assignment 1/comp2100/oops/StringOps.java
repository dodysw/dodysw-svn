package comp2100.oops;

/**
 * Useful string manipulation routines that should be in the
 * standard library's String class but aren't.
 */
public class StringOps {

    /**
     * Return a string consisting of the given number of spaces
     * for indentation.
     *
     * @param n A non-negative integer
     */
    public static String spaces(final int n) {
	String s = new String();
	for (int i = 0; i < n; i++) {
	    s += " ";
	}
	return s;
    }

    /**
     * Does one string contain the other as a substring?
     *
     * It's incredible that we should have to write this. It
     * should be part of the String class of any mature class
     * library.
     *
     * @param haystack The string we're searching in.
     * @param needle The string we're searching for.
     */
    public static boolean hasSubstring(String haystack, String needle) {
        boolean b = false;
        int i = haystack.length(), j = needle.length();
        if (i < j) {
            b = false;
        } else {
            for(int k = 0; k < i - j + 1; k++) {
                if (haystack.regionMatches(k, needle, 0, j)) {
                    b = true;
                    break;
                }
            }
        }
        return b;
    }

    /** 
     * Replace all occurences of old substring in big string
     * with new substring;
     *  
     * @param bigStr the original string
     * @param oldStr the substring to be replaced
     * @param newStr the replacing string
     */
    public static String replaceAll(String bigStr, 
				    String oldStr,
				    String newStr) {
	int j = 0;
	int k = oldStr.length();
	String s = new String();
	do {
	    if (bigStr.regionMatches(j, oldStr, 0, k)) {
		s += new String(newStr);
		j += k;
	    } else {
		if (j + k > bigStr.length()) {
		    s += bigStr.substring(j);
		    break;
		} else {
		    //s += new Character(bigStr.charAt(j)).toString();
		    s += bigStr.charAt(j);
		    j++;
		}
	    }
	} while (j < bigStr.length());
	return s;
    }
}
