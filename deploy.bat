@echo off
echo ========================================
echo   DEPLOIEMENT SB - 180.149.196.39
echo ========================================
echo.

set SSH_KEY=%USERPROFILE%\.ssh\id_ed25519_sb
set SSH_HOST=tony@180.149.196.39

REM Vérifier que la clé SSH existe
if not exist "%SSH_KEY%" (
    echo [ERREUR] Cle SSH introuvable : %SSH_KEY%
    echo Lancez d'abord : install-ssh-key.bat
    pause
    exit /b 1
)

REM --- 1. Copier le fichier service-account Firebase ---
echo [1/2] Copie du service-account Firebase...
scp -o StrictHostKeyChecking=no -i "%SSH_KEY%" storage\firebase\service-account.json %SSH_HOST%:/tmp/service-account-sb.json
if %ERRORLEVEL% neq 0 (
    echo [ERREUR] Echec SCP. Verifiez la connexion SSH.
    pause
    exit /b 1
)

REM --- 2. Deploiement principal ---
echo [2/2] Deploiement Laravel...
ssh -o StrictHostKeyChecking=no -i "%SSH_KEY%" %SSH_HOST% "echo almeida | sudo -S chown -R tony:tony /var/www/html/sb && cd /var/www/html/sb && git pull origin main && composer install --no-dev --optimize-autoloader --quiet && php artisan migrate --force && mkdir -p storage/firebase && cp /tmp/service-account-sb.json storage/firebase/service-account.json && chmod 600 storage/firebase/service-account.json && php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan cache:clear && php artisan optimize && echo almeida | sudo -S chown -R www-data:www-data /var/www/html/sb/storage /var/www/html/sb/bootstrap/cache && (crontab -l 2>/dev/null | grep -q '/var/www/html/sb' || (crontab -l 2>/dev/null; echo '* * * * * cd /var/www/html/sb && php artisan schedule:run >> /dev/null 2>&1') | crontab -) && echo DEPLOIEMENT_TERMINE"

echo.
if %ERRORLEVEL% equ 0 (
    echo [OK] Deploiement reussi !
) else (
    echo [ERREUR] Deploiement echoue. Code : %ERRORLEVEL%
)
echo.
echo Appuyez sur une touche pour fermer...
pause > nul
