import active_directory as ad

query = """
SELECT Name, displayName
     FROM 'LDAP://cn=users,DC=sw,DC=local'
"""

all = "Rahardjo, Kunto (PTI - SOR); Irmanto, Bernardus (PTI-SOR); Fauzi, Ade (PTI - SOR); Sugiarto, S.W (PTI - SOR); Aji, Bayu (PTI-SOR); Kamerman, Paul (PTI - SOR); Ashar, Abu (PTI-SOR); Hunneman, Rudjito (PTI - SOR); Rusdadi, Johanes (PTI-SOR); Hermansyah, Oky (PTI-SOR); Diyen, Richard (PTI-SOR); Azis, Ichsan (PTI-SOR); Alamako, Yanderson (PTI-SOR); Fitriani, Titin (PTI-SOR); Parulian, Edralin (PTI - SOR); Vinyaman, Gunawardana (PTI - SOR); Ramliah (PTI-SOR); Utama, Yudi (PTI - SOR); Kambatu, Basri (PTI-SOR); I Putu, Yudi (PTI-SOR); Sorimuda, Pulungan (PTI-SOR); Nasrul, Agus (PTI-SOR); Kasman - MEM (PTI-SOR); Fotunadi, Didik (PTI-SOR); Kroll, Dwayne (PTI - SOR); Kroll, Dwayne (PTI - SOR); Mudriyanto (PTI-SOR); Nelson, Ricky (PTI - SOR); Halas, Ron (PTI - SOR); Yatna, I Made (PTI-SOR); Syakir, Jinan (PTI-SOR); Handrijono (PTI-SOR); Sudarno, An (PTI-SOR); Barus, Roimon (PTI-SOR); Subrata, Ganda (PTI-SOR); Solski, Larry (Sudbury); Wirantaya, Dewa (PTI - SOR); Pangaribuan, Julius (PTI-SOR); Pammu, Pamrih (PTI-SOR); Keizer, Wesley Gene (PTI - SOR); Rustam (PTI-SOR); Choong, Charles (PTI - SOR); Iskandar (UT) (PTI-SOR); Achmad, Yani (PTI-SOR); Hadi, Jimmy (PTI - SOR); Bichel, Bruce (PTI - SOR); Suntoro, Andi (PTI-SOR); Zimmer, Marvin (PTI - SOR); Mappaselle (PTI-SOR); Holmberg, Martin (PTI - SOR); I Gusti Putu, Oka (PTI-SOR); Syam, Islamuddin Wirawan (PTI - SCM)"
all = """
Fernandez, Carlos (SNC Lavalin - PTI)
"""

domain = ad.AD_object("LDAP://inco.net")
displaynames = []
for displayName in all.split("\n"):
    displayName = displayName.strip()
    if displayName == "":
        print ""
        continue
    for user in domain.search(objectCategory='Person', objectClass='User', displayName=displayName):
        displaynames.append(user.sAMAccountName)
        print user.displayName, "\t", user.sAMAccountName, "\t", user.employeeId

#~ print ';'.join(displaynames)