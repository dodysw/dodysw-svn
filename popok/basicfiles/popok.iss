; -- Example1.iss --
; Demonstrates copying 3 files and creating an icon.

; SEE THE DOCUMENTATION FOR DETAILS ON CREATING .ISS SCRIPT FILES!

[Setup]
AppName=Popok
AppVerName=Popok 0.12
DefaultDirName={pf}\Popok
DefaultGroupName=dsw s/h
UninstallDisplayIcon={app}\uninstall.exe
Compression=lzma
InternalCompressLevel=ultra
SolidCompression=true
ShowLanguageDialog=auto

[Files]
Source: ..\scripts\dist\popok.exe; DestDir: {app}
Source: ..\scripts\dist\popoksvc.exe; DestDir: {app}
Source: ..\scripts\dist\python23.dll; DestDir: {app}
Source: ..\scripts\dist\lib\_socket.pyd; DestDir: {app}\lib
Source: ..\scripts\dist\lib\_sre.pyd; DestDir: {app}\lib
Source: ..\scripts\dist\lib\_winreg.pyd; DestDir: {app}\lib
Source: ..\scripts\dist\lib\datetime.pyd; DestDir: {app}\lib
Source: ..\scripts\dist\lib\perfmon.pyd; DestDir: {app}\lib
Source: ..\scripts\dist\lib\pylib.dat; DestDir: {app}\lib
Source: ..\scripts\dist\lib\servicemanager.pyd; DestDir: {app}\lib
Source: ..\scripts\dist\lib\w9xpopen.exe; DestDir: {app}\lib
Source: ..\scripts\dist\lib\win32api.pyd; DestDir: {app}\lib
Source: ..\scripts\dist\lib\win32event.pyd; DestDir: {app}\lib
Source: ..\scripts\dist\lib\win32evtlog.pyd; DestDir: {app}\lib
Source: ..\scripts\dist\lib\win32service.pyd; DestDir: {app}\lib
Source: ..\scripts\dist\lib\PyWinTypes23.dll; DestDir: {app}\lib
Source: ..\scripts\dist\uninstall.bat; DestDir: {app}
Source: ..\scripts\dist\install.bat; DestDir: {app}
Source: ..\scripts\dist\start.bat; DestDir: {app}
Source: ..\scripts\dist\stop.bat; DestDir: {app}

[Icons]
Name: {group}\Popok; Filename: {app}\popok.exe; WorkingDir: {app}; IconIndex: 0
Name: {group}\Install As Service; Filename: {app}\install.bat; Flags: dontcloseonexit; WorkingDir: {app}
Name: {group}\Remove from Service; Filename: {app}\uninstall.bat; WorkingDir: {app}
Name: {group}\Start Service; Filename: {app}\start.bat; WorkingDir: {app}
Name: {group}\Stop Service; Filename: {app}\stop.bat; WorkingDir: {app}
[Dirs]
Name: {app}\lib
[UninstallRun]
Filename: {app}\uninstall.bat; WorkingDir: {app}
[Run]
Filename: {app}\install.bat; WorkingDir: {app}
Filename: {app}\start.bat; WorkingDir: {app}
