@echo off
REM YZA Automotive - Mode Demo Launcher
echo ====================================================
echo   YZA AUTOMOTIVE - MODE DEMO DATABASE TERISOLASI
echo ====================================================

if not exist .env.demo (
    echo [ERROR] File .env.demo tidak ditemukan!
    exit /b 1
)

if not exist .env.main.backup (
    echo [BACKUP] Menyimpan konfigurasi .env utama ke .env.main.backup...
    copy /Y .env .env.main.backup >nul
)

echo [SWITCH] Mengalihkan koneksi ke database: yza_automotive_laravel_demo...
copy /Y .env.demo .env >nul

echo [CLEAR] Membersihkan cache konfigurasi Laravel...
C:\xampp\php\php.exe artisan config:clear
C:\xampp\php\php.exe artisan route:clear

echo.
echo ====================================================
echo   STATUS: APLIKASI KINI BERJALAN DALAM MODE DEMO!
echo   Database Aktif: yza_automotive_laravel_demo
echo   Akun Admin:  admin.demo@yza.local
echo   Akun Petugas: petugas.demo@yza.local
echo   Akun Member:  member.demo@yza.local
echo ====================================================
echo.
echo Untuk kembali ke database utama, jalankan: switch-to-main.bat
