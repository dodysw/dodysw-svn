; -- Example1.iss --
; Demonstrates copying 3 files and creating an icon.

; SEE THE DOCUMENTATION FOR DETAILS ON CREATING .ISS SCRIPT FILES!

[Setup]
AppName=Popok
AppVerName=Popok version 0.9
DefaultDirName={pf}\Popok
DefaultGroupName=dsw s/h
UninstallDisplayIcon={app}\MyProg.exe
Compression=bzip

[Files]
Source: "MyProg.exe"; DestDir: "{app}"
Source: "MyProg.hlp"; DestDir: "{app}"
Source: "Readme.txt"; DestDir: "{app}"; Flags: isreadme

[Icons]
Name: "{group}\Popok"; Filename: "{app}\popok.exe"
