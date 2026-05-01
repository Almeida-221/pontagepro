@echo off

set MSG=%~1
if "%MSG%"=="" set MSG=mise a jour

echo === Commit + push...
cd C:\xampp\htdocs\sb
git add -A
git commit -m "%MSG%"
git push origin main

echo === Deploiement serveur...
ssh tony@180.149.196.39 "cd /var/www/html/sb && git pull origin main && php artisan migrate --force && rm -rf bootstrap/cache/*.php storage/framework/cache/data/* storage/framework/views/* && php artisan route:clear && php artisan storage:link 2>/dev/null; echo OK"

echo.
echo Done !
pause
