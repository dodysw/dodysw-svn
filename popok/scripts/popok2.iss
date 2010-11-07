[Setup]
AppName=Popok
AppVerName=Popok
DefaultDirName={pf}\Popok
DefaultGroupName=dsw software house
UninstallDisplayIcon={app}\uninstall.exe
Compression=lzma/ultra
InternalCompressLevel=ultra
SolidCompression=true
ShowLanguageDialog=auto
OutputBaseFilename=popok.win32
SourceDir=..\distrib\win32
ShowTasksTreeLines=true
VersionInfoVersion=1.0.0
VersionInfoCompany=dsw s/h
VersionInfoDescription=made by dody suria wijaya software house <dodysw@gmail.com>
AppCopyright=GPL
OutputDir=..\setup
UserInfoPage=false
DisableStartupPrompt=false

[Files]
Source: *.*; DestDir: {app}; Flags: recursesubdirs

[Icons]
Name: {group}\Popok; Filename: {app}\popok.exe; WorkingDir: {app}
Name: {group}\Install As Service; Filename: {app}\install.bat; Flags: dontcloseonexit; WorkingDir: {app}
Name: {group}\Remove from Service; Filename: {app}\uninstall.bat; WorkingDir: {app}
Name: {group}\Start Service; Filename: {app}\start.bat; WorkingDir: {app}
Name: {group}\Stop Service; Filename: {app}\stop.bat; WorkingDir: {app}
Name: {group}\{cm:UninstallProgram, Popok}; Filename: {uninstallexe}
Name: {group}\dsw sh website; Filename: {app}\dswsh.url; IconFilename: {app}\popok.ico; Comment: Visit dody suria wijaya software house website
[UninstallRun]
Filename: {app}\stop.bat; WorkingDir: {app}
Filename: {app}\uninstall.bat; WorkingDir: {app}
[Run]
Filename: {app}\install.bat; WorkingDir: {app}
Filename: {app}\start.bat; WorkingDir: {app}
[LangOptions]
TitleFontName=Verdana
WelcomeFontName=Verdana
CopyrightFontName=Verdana
[INI]
Filename: {app}\dswsh.url; Section: InternetShortcut; Key: URL; String: http://dsw.gesit.com/
[UninstallDelete]
Type: files; Name: {app}\dswsh.url
[Messages]
ReadyLabel1=Setup is now ready to begin installing [name] on your computer. Please make sure you have shutdown running instance of this program.
SetupLdrStartupMessage=This will install %1. Please make sure you have shutdown %1 service. Do you wish to continue?
