python setup.py py2exe -b1
REM del dist\lib\unicodedata.pyd
REM del dist\lib\_ssl.pyd
copy ..\resource\install.bat dist
copy ..\resource\uninstall.bat dist
copy ..\resource\start.bat dist
copy ..\resource\stop.bat dist
copy ..\resource\quickstart.txt dist
copy ..\resource\readme.txt dist