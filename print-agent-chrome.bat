@echo off
rem Launcher Print Agent: Chrome dengan profil terpisah + --kiosk-printing (print tanpa dialog).
rem Ubah URL di bawah sesuai alamat aplikasi Anda.
set "URL=http://localhost/posoptikmelati/public/print-agent"

set "CHROME=%ProgramFiles%\Google\Chrome\Application\chrome.exe"
if not exist "%CHROME%" set "CHROME=%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe"
if not exist "%CHROME%" set "CHROME=%LocalAppData%\Google\Chrome\Application\chrome.exe"
if not exist "%CHROME%" (
    echo Chrome tidak ditemukan. Ubah variabel CHROME di file ini.
    pause
    exit /b 1
)

rem Profil terpisah membuat Chrome selalu memulai proses baru, jadi flag tidak diabaikan.
start "" "%CHROME%" --kiosk-printing --user-data-dir="%LocalAppData%\PrintAgentProfile" --no-first-run --app="%URL%"
