output = ""
xox = 0
x = 5
for i in range(1,1001):
    if x:
        x -= 1
    c = str(i)
    if not x:
        c += "*"
        xox ^= 1
        x = xox and 3 or 5
    output += c    
print output
assert(output == ''.join([(i & 7 in (0,5) and "%s*" or "%s") % i for i in range(1,1001)]))