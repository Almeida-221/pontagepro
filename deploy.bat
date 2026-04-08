@echo off

set MSG=%~1
if "%MSG%"=="" set MSG=mise a jour

echo === Commit + push backend...
cd C:\xampp\htdocs\sb
git add -A
git commit -m "%MSG%"
git push origin main

echo === Deploiement sur le serveur...
ssh tony@180.149.196.39 "cd /var/www/html/sb && git pull origin main && rm -rf bootstrap/cache/*.php storage/framework/cache/data/* storage/framework/views/* && php artisan route:clear && echo Serveur OK"

echo === Build APK Flutter...
cd C:\Users\almei\sb_securite
call flutter clean
call flutter pub get
call flutter build apk --release

echo.
echo Deploy termine !
echo APK : C:\Users\almei\sb_securite\build\app\outputs\flutter-apk\app-release.apk
pause
