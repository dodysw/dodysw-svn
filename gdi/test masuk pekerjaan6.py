o = ""
x = 0
a = 5
for i in range(1,1001):
    o += str(i)
    a -= 1      
    if not a:
        o += "*"
        x ^= 1
        a = x and 3 or 5    
print o