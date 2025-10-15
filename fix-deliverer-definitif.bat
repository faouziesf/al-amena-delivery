@echo off
echo ========================================
echo FIX DEFINITIF - COMPTE LIVREUR
echo ========================================
echo.

echo [ETAPE 1/3] Recharger autoload Composer...
call composer dump-autoload
echo.

echo [ETAPE 2/3] Clear tous les caches Laravel...
call php artisan optimize:clear
echo.

echo [ETAPE 3/3] Verification routes deliverer...
call php artisan route:list --name=deliverer.tournee
echo.

echo ========================================
echo FIX TERMINE
echo ========================================
echo.
echo Testez maintenant:
echo http://localhost:8000/deliverer/tournee
echo.
echo Si ca ne fonctionne toujours pas:
echo 1. Redemarrer le serveur PHP
echo 2. Vider cache navigateur (Ctrl+Shift+Delete)
echo 3. Tester en navigation privee
echo.
pause
