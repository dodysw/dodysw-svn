output = ""
xox = 0
x = 5
i = 1001
r = range(1,i)
while i:
    x -= 1
    i -= 1
    if not x:
        r.insert(i,"*")
        xox ^= 1
        x = xox and 3 or 5    
print ''.join([str(c) for c in r])