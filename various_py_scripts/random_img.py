buff = ""
buff += """<html>
<head><title>Test</title></head>
<body>"""
buff += ''.join(["<img src='http://10.1.1.2/%s.jpg'/>" % x for x in range(5000)])
buff += "</body></html>"
file("c:/temp/out.html","w").write(buff)

















