"""
Partial name => AD displayname + AD account + AD employeeid
"""
import active_directory as ad

query = """
SELECT Name, displayName
     FROM 'LDAP://cn=users,DC=sw,DC=local'
"""
all = """
MARVIN ZIMMER
DJUFRI MOH.DJAFAR
CHARLES CHOONG
UTL MAINT COR ON CALL
MARJANTO
MUAMMAR
ISKANDAR
SNR UTILITIES ON CALL
UTIL ELECRICAL ON CALL
UTIL INST ON CALL
MBDG ON CALL
"""

domain = ad.AD_object("LDAP://inco.net")
displaynames = []
for displayName in all.strip().split("\n"):
    found = False
    displayName = displayName.lower().strip()
    if displayName == "":
        print ""
        continue
    #then split
    splitted_words = displayName.split()
    for word in splitted_words:
        if len(word) < 3:
            continue
        for user in domain.search(objectCategory='Person', objectClass='User', displayName="*%s*" % word):
            if reduce(lambda x,y: x and y, [w in user.displayName.lower() for w in splitted_words]):
                displaynames.append(user.sAMAccountName)
                print user.displayName, "\t", user.sAMAccountName, "\t", user.employeeId
                found = True
                break
        if found:
            break
    if not found:
        print "NOT FOUND = ", displayName

print ';'.join(displaynames)