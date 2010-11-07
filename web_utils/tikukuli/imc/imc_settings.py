# this must be put into non-public-accessible location
IMAP_SERVER = 'localhost'
IMAP_USERNAME = 'dody'
import getpass
IMAP_PASSWORD = 'PPPPPPP' or getpass.getpass('imap password for %s:' % IMAP_USERNAME)
