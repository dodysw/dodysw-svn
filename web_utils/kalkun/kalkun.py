"""
Kalkun
by Dody Suria Wijaya <dodysw@gmail.com>
Aug 2008
Analyze html files of the same URL downloaded at different times, and assign names to changing part of the xml path
to be used for parsing future download of the same file
"""

class Knowledge:
    pass

class Parser:
    pass

class Content:
    pass


class DetikHeadline:
    def __init__(self, title, date):
        self.title = title
        self.date = date

class DetikContent(Content):
    headline = []
    def __init__(self):
        self.headline.append(DetikHeadline("test","tanggal"))

import HTMLParser

class KalkunHtmlParser(HTMLParser.HTMLParser):

    def __init__(self, *args, **kargs):
        HTMLParser.HTMLParser.__init__(self, *args, **kargs)
        self.txt = []
        self.level = 0
        self.order = []
        self.last_order_num = 0

    def handle_starttag(self, tag, attrs):
        #print "Encountered the beginning of a %s tag" % tag
        self.order.append(self.last_order_num + 1)
        self.txt.append("\n%s[%s]<%s %s>" % ("-" * self.level, self.get_breadcrumb(), tag, ",".join(["%s=%s" % (key,val) for key,val in attrs])))
        self.level += 1
        self.last_order_num = 0

    def handle_endtag(self, tag):
        #print "Encountered the end of a %s tag" % tag
        #self.txt.append("-" * self.level + "/"+tag)
        self.level -= 1
        self.last_order_num = self.order.pop()
        #self.last_order_num = self.order[-1]

    def handle_data(self, data):
        if len(data.strip()) > 0:
            self.txt.append('"%s"' % (data.strip()))

    def get_breadcrumb(self):
        return ".".join([str(i) for i in self.order])


def learn(path):
    # get list of files
    import glob

    filelist = glob.glob(path)

    #assert len(filelist) > 1

    # sort it
    filelist.sort()



    # read each file
    i = 0
    for apath in filelist:
    # analyze
        i += 1

        # parse into xml
        p = KalkunHtmlParser()
        p.feed(file(apath).read())
        p.close()

        file("out-%s.txt" % i,"w").write(','.join(p.txt))


        # if xml repository still empty,
            # save the repository tree into it for use to compare the next file

            # in addition, save the content into a matching path->content array for this file

        # otherwise, compare each xml path with this one
            # if different, save the content into a matching path->content array for this file

    # produce list of xml path of changing part, with their detail sample of content from all files
    # e.g.
    # html->body->div[1]->text
    #      A text from file A
    #      A text from file B
    #      A text from file C

    # this is done by comparing how many ..... will think later...since i did assume that the tree structure is constant, only the xml text does. but i most likely wrong.

    # return knowledge object
    return Knowledge()

def parse(knowledge):
    # assign a part of xml path from knowledge into a named variable, geared toward a single file
    return Parser()

def analyze(parser, path):
    # analyze a file using parser object, and return content object that contains the "news"
    return DetikContent()

def show(content):
    # test a content by printing the content of meaningful info
    print "Headline:", content.headline[0].summary
    print "date:", content.headline[0].date

import unittest

class TestKalkun(unittest.TestCase):
    def testContent(self):
        # perform test to make sure all produce known result
        import sys
        knowledge = learn(sys.argv[1])
        parser = parse(knowledge)
        content = analyze(parser, "d:\_py\landak\detik\web\2008-08-22\2008-08-22 170454-detik.htm")
        """

        self.assertEqual(content.headline[0].date, "Jumat, 22/08/2008 15:43 WIB")
        self.assertEqual(content.headline[0].title, "Mantan Sekjen PD Tertawakan Kabar SBY - Artalyta Sudah Lama Kenal")

    headline[0]
        date
            1.1.22.2.1.2
        title_group
            1.1.22.2.1.4
        title
            1.1.22.2.1.4
            1.1.22.2.1.6 (if has title group)
        summary
            1.1.22.2.1.5
            1.1.22.2.1.7 (if has title group)

    headline[1]
        date 1.1.22.3.1.1.1
        ttgp 1.1.22.3.1.1.3
        titl 1.1.22.3.1.1.5
        summ 1.1.22.3.1.1.7, 1.1.22.3.1.1.6


    headline[1]
        date 1.1.22.3.1.1.12, 1.1.22.3.1.1.9
        ttgp 1.1.22.3.1.1.3
        titl 1.1.22.3.1.1.5
        summ 1.1.22.3.1.1.7, 1.1.22.3.1.1.6



    berita sebelumnya
    headline[1]
        date 1.1.22.3.4.1.2.1.1.1
        titl 1.1.22.3.4.1.2.1.1.3
    headline[2]
        date 1.1.22.3.4.1.2.1.2.1
        titl 1.1.22.3.4.1.2.1.2.3
    headline[3]
        date 1.1.22.3.4.1.2.1.3.1
        titl 1.1.22.3.4.1.2.1.3.3
    headline[4]
        date 1.1.22.3.4.1.2.1.4.1
        titl 1.1.22.3.4.1.2.1.4.3
    headline[5]
        date 1.1.22.3.4.1.2.1.5.1
        titl 1.1.22.3.4.1.2.1.5.3
    headline[6]
        date 1.1.22.3.4.1.2.1.6.1
        titl 1.1.22.3.4.1.2.1.6.3
    headline[7]
        date 1.1.22.3.4.1.2.1.7.1
        titl 1.1.22.3.4.1.2.1.7.3
    headline[8]
        date 1.1.22.3.4.1.2.1.8.1
        titl 1.1.22.3.4.1.2.1.8.3
    headline[9]
        date 1.1.22.3.4.1.2.1.9.1
        titl 1.1.22.3.4.1.2.1.9.3
    headline[10]
        date 1.1.22.3.4.1.2.1.10.1
        titl 1.1.22.3.4.1.2.1.10.3

        """


if __name__ == "__main__":
    suite = unittest.TestLoader().loadTestsFromTestCase(TestKalkun)
    unittest.TextTestRunner(verbosity=2).run(suite)


