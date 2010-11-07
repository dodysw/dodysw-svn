cd ..\distrib
rmdir /S /Q win32
python ..\scripts\setup.py py2exe
copy ..\distutils\* ..\distrib\win32
cd ..\scripts
"C:\Program Files\Inno Setup 4\Compil32.exe" /cc "popok2.iss"