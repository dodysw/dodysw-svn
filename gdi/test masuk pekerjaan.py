output = []
xox = 0
x = 5
for i in range(1,100):
    if x:
        x -= 1
    output.append(str(i))
    if not x:
        output.append("*")
        xox ^= 1
        x = xox and 3 or 5
        
print ''.join(output)