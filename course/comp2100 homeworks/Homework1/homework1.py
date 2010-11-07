
# create sequence of number we want
ascii_code = range(32,128) + range(160,256)

# split into 6
rows = []
len_6 = len(ascii_code) / 6
if 0:
    for n in range(6):
        rows.append(ascii_code[n*len_6 : (n+1)*len_6])

    # swap matrix
    rows = zip(*rows)
else:
    for j in range(len_6):
        t = []
        for i in range(6):
            t.append(ascii_code[len_6*j+i])
        rows.append(t)



# print it
for row in rows:
    for code in row:
        if code != 127:
            print "%s %s" % (code, chr(code)),
        print "\t",
    print
