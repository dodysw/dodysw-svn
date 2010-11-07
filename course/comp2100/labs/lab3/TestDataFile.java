package comp2100.junit;

import junit.framework.*;
/*
 * Created on 29/03/2006
 *
 * TODO To change the template for this generated file go to
 * Window - Preferences - Java - Code Style - Code Templates
 */

import java.io.File;
import java.util.ArrayList;
import java.util.Scanner;


/**
 * @author u4267771
 *
 * TODO To change the template for this generated type comment go to
 * Window - Preferences - Java - Code Style - Code Templates
 */
public class TestDataFile extends TestCase {
	ArrayList<TestItem> items;
	
	public void setUp() throws java.io.FileNotFoundException {
		Scanner sc = new Scanner(new File("datafile.txt"));
		items = new ArrayList<TestItem>();
		while (sc.hasNextLine()) {
			String line = sc.nextLine();
			String[] pair = line.split("\t");
			items.add(new TestItem(pair[0].trim(), pair[1].trim()));
		}
	}
	
	public void testMethod() {
		for (int i=0; i < items.size(); i++) {
			String input = items.get(i).getInputValue();
			String output = items.get(i).getOutputValue();
			assertEquals(output, input.toUpperCase()); 
		}
	}
	
}

class TestItem {
	private String inputValue;
	private String outputValue;
	
	TestItem(String i, String o) {
		inputValue = i;
		outputValue = o;
	}
	
	public String getInputValue() {
		return inputValue;
	}
	public String getOutputValue() {
		return outputValue;
	}
}
