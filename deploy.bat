@echo off

set MSG=%~1
if "%MSG%"=="" set MSG=mise a jour

echo === Commit + push...
cd C:\xampp\htdocs\sb
git add -A
git commit -m "%MSG%"
git push origin main

echo === Deploiement serveur...
ssh tony@180.149.196.39 "cd /var/www/html/sb && git pull origin main && if ! grep -q SENDTEXT_API_KEY .env; then printf '\n# SendText SMS Gateway\nSENDTEXT_API_URL=https://api.sendtext.sn/v1/sms\nSENDTEXT_API_KEY=SNT_API_KEY_3565a712-6956-41c9-8b76-3e1883fbebc6\nSENDTEXT_API_SECRET=SNT_API_SECRET_141e2ffc-5343-4a0b-9392-b24563658160\nSENDTEXT_SENDER_NAME=EPSILON\nAPP_MOB_DOWNLOAD_URL=\nAPP_SEC_DOWNLOAD_URL=\n' >> .env; fi && php artisan migrate --force && php artisan config:clear && rm -rf bootstrap/cache/*.php storage/framework/cache/data/* storage/framework/views/* && php artisan route:clear && php artisan storage:link 2>/dev/null; echo OK"

echo.
echo Done !
pause
