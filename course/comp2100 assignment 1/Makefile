# Makefile for the Oops system Java implementation
#
# Author: Ian Barnes
# $Revision: 2005.9 $
# $Date: 2005/05/02 06:27:48 $

default: test

sources = $(wildcard ./comp2100/oops/*.java ./comp2100/oops/*/*.java)
classes = $(sources:.java=.class)
samples = $(wildcard tests/sample*.sxw)

oops: $(sources) Makefile
	javac $(sources)

clean:
	rm -f $(classes)
	rm -f ./comp2100/oops/*~
	rm -f ./comp2100/oops/*/*~
	rm -rf docs/
	rm -f tests/*.txt tests/*.xml tests/*.html

%.class: %.java
	javac -classpath . $<

docs: $(classes) Makefile
	javadoc -d docs -classpath . -author -version \
	-link http://java.sun.com/j2se/1.5.0/docs/api/ \
	-linksource -private -use comp2100.oops.tree \
	comp2100.oops.scanner comp2100.oops.visitor \
	comp2100.oops

%.txt: %.sxw
	./oops $<

%.xml: %.sxw
	./oops $<

test: oops $(samples)
	./run_tests

jar: a1.jar

a1.jar: $(sources)
	jar cvf $@ $(sources)
