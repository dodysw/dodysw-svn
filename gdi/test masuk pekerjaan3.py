output = ""
xox = 0
x = 5
for i in range(1,1001):
    c = str(i)
    x -= 1      
    if not x:
        c += "*"
        xox ^= 1
        x = xox and 3 or 5
    output += c    
print output