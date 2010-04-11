import active_directory as ad

domain = ad.AD_object("LDAP://inco.net")

me = domain.find_user("suriad") # defaults to current user
for group in me.memberOf:
  print "Members of group", group.cn
  for group_member in group.member:
    print "  ", group_member