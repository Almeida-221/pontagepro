#!/bin/bash

# ── 1. Backend : commit + push ─────────────────────────────────────────────────
echo ">>> Commit + push backend..."
cd /c/xampp/htdocs/sb
git add -A
git commit -m "${1:-mise a jour}"
git push origin main

# ── 2. Serveur SSH : pull + clear cache ────────────────────────────────────────
echo ">>> Déploiement sur le serveur..."
ssh tony@180.149.196.39 "
  cd /var/www/html/sb &&
  git pull origin main &&
  rm -rf bootstrap/cache/*.php storage/framework/cache/data/* storage/framework/views/* &&
  php artisan route:clear &&
  echo 'Serveur OK'
"

# ── 3. Flutter : build APK ─────────────────────────────────────────────────────
echo ">>> Build APK Flutter..."
cd /c/Users/almei/sb_securite
flutter clean && flutter pub get && flutter build apk --release

echo ""
echo "✅ Déploiement terminé !"
echo "APK : build/app/outputs/flutter-apk/app-release.apk"
