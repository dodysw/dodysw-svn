import sys, glob

class TrivWhiz:
    def __init__(self, main_path):
        self.db = {}
        for path in glob.glob(main_path):
            for line in file(path):
                if ':' in line and '*' in line:
                    try:
                        category, temp = line.split(':',1)
                        question, answer = temp.split('*',1)
                        self.db[question] = answer
                    except:
                        print line
                        raise
    def answer(self, q):
        q = q.strip()
        for key in self.db:
            if q in key:
                yield self.db[key].strip()


if __name__ == "__main__":
    if len(sys.argv) != 2:
        print "Use: trivwhiz.py c:\\path_to_trivtxt\\*.txt"
        sys.exit()
    tw = TrivWhiz(sys.argv[1])
    input = ''
    while input != 'q':
        input = raw_input('Part of the question: ').strip()
        for answer in tw.answer(q):
            print answer
