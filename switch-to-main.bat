@echo off
REM YZA Automotive - Mode Utama Launcher
echo ====================================================
echo   YZA AUTOMOTIVE - KEMBALI KE DATABASE UTAMA
echo ====================================================

if not exist .env.main.backup (
    echo [ERROR] File .env.main.backup tidak ditemukan!
    exit /b 1
)

echo [RESTORE] Mengembalikan konfigurasi .env utama...
copy /Y .env.main.backup .env >nul

echo [CLEAR] Membersihkan cache konfigurasi Laravel...
C:\xampp\php\php.exe artisan config:clear
C:\xampp\php\php.exe artisan route:clear

echo.
echo ====================================================
echo   STATUS: APLIKASI KEMBALI KE DATABASE UTAMA!
echo   Database Aktif: yza_automotive_laravel
echo   Data bisnis dan akun asli aktif kembali secara normal.
echo ====================================================
