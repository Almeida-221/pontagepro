@echo off
echo ========================================
echo   DEPLOIEMENT SB - 180.149.196.39
echo ========================================
echo.
echo Mot de passe SSH : almeida
echo.

set SSH_HOST=tony@180.149.196.39

REM --- 1. Copier le fichier service-account Firebase ---
echo [1/2] Copie du service-account Firebase...
scp -o StrictHostKeyChecking=no storage\firebase\service-account.json %SSH_HOST%:/tmp/service-account-sb.json

REM --- 2. Deploiement principal ---
echo [2/2] Deploiement Laravel...
ssh -o StrictHostKeyChecking=no -t %SSH_HOST% "echo almeida | sudo -S chown -R tony:tony /var/www/html/sb && cd /var/www/html/sb && git remote set-url origin https://github.com/Almeida-221/pontagepro.git && git pull origin main && composer install --no-dev --optimize-autoloader --quiet && php artisan migrate --force && mkdir -p storage/firebase && cp /tmp/service-account-sb.json storage/firebase/service-account.json && chmod 600 storage/firebase/service-account.json && php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan cache:clear && php artisan optimize && echo almeida | sudo -S chown -R www-data:www-data /var/www/html/sb/storage /var/www/html/sb/bootstrap/cache && echo DEPLOIEMENT_TERMINE"

echo.
echo Appuyez sur une touche pour fermer...
pause > nul
