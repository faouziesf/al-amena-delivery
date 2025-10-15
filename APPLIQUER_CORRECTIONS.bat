@echo off
cls
echo ========================================
echo APPLIQUER TOUTES LES CORRECTIONS
echo ========================================
echo.
echo Cette operation va:
echo 1. Recharger autoload Composer
echo 2. Clear tous les caches Laravel
echo 3. Verifier les routes
echo.
echo Appuyez sur une touche pour continuer...
pause >nul
echo.

echo ========================================
echo ETAPE 1/3: Composer dump-autoload
echo ========================================
call composer dump-autoload
if %ERRORLEVEL% NEQ 0 (
    echo ERREUR lors du dump-autoload!
    pause
    exit /b 1
)
echo ✓ Autoload rechargé
echo.

echo ========================================
echo ETAPE 2/3: Clear caches Laravel
echo ========================================
call php artisan optimize:clear
if %ERRORLEVEL% NEQ 0 (
    echo ERREUR lors du clear cache!
    pause
    exit /b 1
)
echo ✓ Caches vidés
echo.

echo ========================================
echo ETAPE 3/3: Verification routes
echo ========================================
call php artisan route:list --name=deliverer.tournee
echo.
call php artisan route:list --name=deliverer.scan
echo.

echo ========================================
echo ✓ CORRECTIONS APPLIQUEES AVEC SUCCES
echo ========================================
echo.
echo IMPORTANT:
echo 1. Redemarrez le serveur PHP (Ctrl+C puis php artisan serve)
echo 2. Videz le cache navigateur (Ctrl+Shift+Delete)
echo 3. Testez: http://localhost:8000/deliverer/tournee
echo.
echo Problemes corriges:
echo ✓ Cannot redeclare processScan()
echo ✓ Vue tournee avec layout deliverer
echo ✓ Navigation coherente
echo.
pause
