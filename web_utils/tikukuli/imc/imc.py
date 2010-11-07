import imaplib, email, os, base64, time, sys
import imc_settings as settings

KEY_SUBJECT = '/./././,/,/,'
TIKIKULI_SEND_NOTIFY = False
TIKIKULI_COMPRESSED = False
TIKIKULI_FAKE_EXTENSION = True
EMAIL_ON_EXCEPTION = True
ADMIN_EMAIL = "dodysw@gmail.com"

def print_exc_plus():
    import traceback
    tb = sys.exc_info()[2]
    while tb.tb_next:
        tb = tb.tb_next
    stack = []
    f = tb.tb_frame
    while f:
        stack.append(f)
        f = f.f_back
    stack.reverse()
    traceback.print_exc()
    print "Locals by frame, innermost last"
    for frame in stack:
        print
        print "Frame %s in %s at line %s" % (frame.f_code.co_name,
                                            frame.f_code.co_filename,
                                            frame.f_lineno)
        for key, value in frame.f_locals.items():
            print "\t%20s = " % key,
            try:
                print value
            except:
                print "<ERROR WHILE PRINTING VALUES>"

def email_output(to=ADMIN_EMAIL, from_addr="IMC bot <dodysw+noreply@gmail.com>", subject="IMC Output", body="", if_any=True):
    if body != '' or not if_any:
        import tikikuli
        tikikuli.send_simple_email(to=to, from_addr=from_addr, subject=subject, body=body)

def output_wrapper(func, *args, **kwargs):
    # wrap stdout/stderr output of func and return it
    import StringIO
    old_stdout, old_stderr = sys.stdout, sys.stderr
    try:
        sys.stderr = sys.stdout = file_like = StringIO.StringIO()
        try:
            ret = func(*args, **kwargs)
        except:
            ret = None
            print_exc_plus()
    finally:
        output = file_like.getvalue()
        file_like.close()
        sys.stdout, sys.stderr = old_stdout, old_stderr
        return ret, output

def send_by_tikikuli(email_to, url):
    import tikikuli
    tikikuli.main(email_to, url, compressed=TIKIKULI_COMPRESSED, notify=TIKIKULI_SEND_NOTIFY, fake=TIKIKULI_FAKE_EXTENSION)

def do_tikikuli(msg, body):
    urls = body.split('\n')
    urls = [url.strip() for url in urls if url.strip() != '']
    send_by_tikikuli(msg.get('reply-to', msg['from']), urls)

def do_system(msg, body):
    try:
        import command  # only available in unix
        ret, output = command.getstatusoutput(body)
    except ImportError:
        # you have to manually give suffix "2>&1" to capture stderr
        import os
        f = os.popen(body.strip(),'r')
        output = f.read()
        ret = f.close()
    output += "\r\n" + "="*30 + "\r\n" + "Return Value: %s" % ret + "\r\n" + "="*30
    email_output(to=msg.get('reply-to', msg['from']), subject="IMC System Output", body=output, if_any=False)

def main():
    tasks = []
    M = imaplib.IMAP4_SSL(settings.IMAP_SERVER)
    M.login(settings.IMAP_USERNAME, settings.IMAP_PASSWORD)
    M.select()
    typ, data = M.search(None, '(SUBJECT "%s")' % KEY_SUBJECT)
    for num in data[0].split():
        typ, data = M.fetch(num, '(RFC822)')
        msg = email.message_from_string(data[0][1])
        del data,typ
        if msg.is_multipart(): # html message?
            for payload in msg.get_payload():
                if payload.get_content_type() == 'text/plain':
                    body = payload.get_payload()
        else:
            body = msg.get_payload()
        assert(type(body) == str)

        tasks += [(msg, body)]

        # delete email quickly before sending to tikikuli since tikikuli could take time more and another of this instance of this run at the sametime
        M.store(num, '+FLAGS', '\\Deleted')

    M.expunge()
    M.close()
    M.logout()

    me = sys.modules[__name__]
    for msg, body in tasks:
        # subject should be formatted like this: KEY_SUBJECT|command_type
        # call function based on command_type
        if '|' in msg['Subject']:
            c = msg['Subject'].split('|')[1]
        else:
            c = 'tikikuli'
        try:
            getattr(me, 'do_'+c)(msg, body)
        except AttributeError:
            pass


if __name__ == '__main__':
    # if unhandled exception happens, email to user
    if EMAIL_ON_EXCEPTION:
        ret, output = output_wrapper(main)
        email_output(subject="IMC Exception", body=output)
    else:
        main()