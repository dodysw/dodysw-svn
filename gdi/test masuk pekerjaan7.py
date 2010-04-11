o = ""
for i in range(1,1001):
    o += str(i) + (i&7 in (0,5) and "*" or "")
print o


