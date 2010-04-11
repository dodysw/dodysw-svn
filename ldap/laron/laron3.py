"""
get all users
"""
import active_directory as ad

domain = ad.AD_object("LDAP://inco.net")
user_with_employeeid = []

for u in domain.search(objectCategory='Person', objectClass='User'):
    if u.employeeId:
        user_with_employeeid.append(u.sAMAccountName)
        print u.sAMAccountName, "\t", u.employeeId

#~ print ";".join(user_with_employeeid)