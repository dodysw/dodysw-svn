; -- Example1.iss --
; Demonstrates copying 3 files and creating an icon.

; SEE THE DOCUMENTATION FOR DETAILS ON CREATING .ISS SCRIPT FILES!

[Setup]
AppName=Popok
AppVerName=Popok version 0.9
DefaultDirName={pf}\Popok
DefaultGroupName=Popok
UninstallDisplayIcon={app}\MyProg.exe
Compression=bzip

SolidCompression=true
AppCopyright=dsw software house
[Files]
Source: popok.exe; DestDir: {app}

[Icons]
Name: {group}\Popok; Filename: {app}\popok.exe
[_ISTool]
Use7zip=false
