#!/usr/bin/python
"""
Generic DC Bot
Copyright 2005, Dody Suria Wijaya <dodysw@gmail.com>
"""
import re, time, random, threading, dclib
__version__ = '1.0.0'
__description__ = ''

import trivwhiz
#~ tw = trivwhiz.TrivWhiz('g:/Temp/TriviaEx.Server/Questions/*.*')
tw = trivwhiz.TrivWhiz('Questions/*.*')

class Question:
    question = answer = hint = None
    answer_sent = answered_by_someone = has_answer = False
    _tw = tw
    def __init__(self, question, hint=''):
        self.question = question
        self.hint = hint
    def hint_len(self):
        return len(self.hint)
    def answers(self):
        return self._tw.answer(self.question)



class WhizBot(dclib.DCBot):
    parse_question = re.compile('QUESTION:\s*(.*?)\s*HINT:\s*(.*)\s*',re.S)
    dammit = ['aih', 'wtf', 'hmph', 'sigh', 'alamak', 'so stupid', 'meh', 'zzz', '?', ':(', 'whatta', 'dang', 'huh']
    hooray = ['yea', 'yes!', 'yiha', 'yeehaa', 'im good', 'hehehe','hehe', 'hihihi', ':D',':)','yay', 'yahuu']
    curr_q = None
    answered = False
    answered_by_someone = False

    def handle_public_chat(self, nick, data):
        print nick, ':', data
        if nick == 'Trivia':
            if 'QUESTION:' in data:
                print 'Question!'
                m = self.parse_question.search(data)
                if m:

                    random.seed()
                    self.answered = False
                    self.answered_by_someone = False

                    question = re.sub('\s{2,}',' ', m.group(1)).strip()
                    hint = m.group(2).strip()
                    self.curr_q = q = Question(question, hint)

                    print 'Looking for [%s]' % q.question

                    for answer in q.answers():
                        print 'Got', answer, "(%s len)" % len(answer), 'while hint len is', q.hint_len()
                        if len(answer) == q.hint_len():
                            q.answer = answer
                            break


                    if q.answer != None:
                        q.answer = q.answer.lower()
                        #~ self.send_pm('nism','psst, the answer is: %s' % answer)
                        #~ self.send_pm('bnn','psst, the answer is: %s' % answer)

                        r = random.randint(0,10)
                        delay = len(q.question)*0.05 + 0.2*q.hint_len()
                        if r == 0:
                            print 'Waiting for', delay
                            threading.Timer(delay, self.late_answer, [q,]).start()
                        elif r >= 4:
                            print 'Decided not to answer'
                            return
                        else:
                            delay += 15*r
                            print 'Waiting for', delay
                            threading.Timer(delay, self.late_answer, [q,]).start()
                    else:
                        print 'I do not know the answer'
            elif self.curr_q and not self.curr_q.answer_sent and (data.startswith('The answer is:') or data.startswith('Correct ')):
                print 'Got the answer is'
                self.curr_q.answered_by_someone = True
                time.sleep(random.randint(1,3))
                if random.randint(0,10) > 8:
                    self.send_public_chat(random.choice(self.dammit))

    def late_answer(self, q):
        if self.curr_q != q or q.answered_by_someone:
            print 'Thread: already answered'
            return
        self.send_public_chat(q.answer)
        q.answer_sent = True
        if random.randint(0,10) > 8:
            time.sleep(random.randint(1,3))
            self.send_public_chat(random.choice(self.hooray))

    def handle_to(self, nick, data):
        print 'PM from %s:%s' % (nick, data)
        if data.startswith('/p '):
            txt = data.split(' ',1)[1]
            self.send_public_chat(txt)

if __name__ == '__main__':
    import optparse
    parser = optparse.OptionParser(version="%%prog %s" % __version__)
    parser.add_option("--dc_ip", dest="dc_server_ip", help="DC++ Server address (def:150.203.239.82)", default='150.203.239.82')
    parser.add_option("--dc_port", type="int", dest="dc_server_port", help="CS Server port (def:411)", default=411)
    parser.add_option("--nick", dest="nick", help="DC++ nick name (def:%s)" % 'Si_Pintar', default='Si_Pintar')
    options, args = parser.parse_args()

    bot = WhizBot(address=(options.dc_server_ip, options.dc_server_port), nick=options.nick, description='DC++')
    bot.run()