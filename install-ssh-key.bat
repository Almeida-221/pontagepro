@echo off
echo ========================================
echo   INSTALLATION CLE SSH - 180.149.196.39
echo ========================================
echo.
echo Cette operation installe votre cle SSH sur le serveur.
echo Vous devrez entrer le mot de passe UNE SEULE FOIS : almeida
echo.
echo Appuyez sur une touche pour continuer...
pause > nul

set SSH_KEY=%USERPROFILE%\.ssh\id_ed25519_sb.pub

REM Copier la cle publique sur le serveur
type "%SSH_KEY%" | ssh -o StrictHostKeyChecking=no -o PreferredAuthentications=password tony@180.149.196.39 "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys && chmod 700 ~/.ssh && chmod 600 ~/.ssh/authorized_keys && echo CLE_INSTALLEE_AVEC_SUCCES"

echo.
if %ERRORLEVEL% equ 0 (
    echo [OK] Cle SSH installee ! Vous pouvez maintenant utiliser deploy.bat sans mot de passe.
) else (
    echo [ERREUR] Echec. Verifiez le mot de passe et la connexion reseau.
)
echo.
pause
