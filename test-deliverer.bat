@echo off
echo ========================================
echo TEST DELIVERER ROUTES - AL-AMENA
echo ========================================
echo.

echo [1] Clearing all caches...
call php artisan optimize:clear
echo.

echo [2] Checking deliverer routes...
call php artisan route:list --path=deliverer
echo.

echo [3] Checking client-topup routes...
call php artisan route:list --path=deliverer/client-topup
echo.

echo ========================================
echo TESTS COMPLETED
echo ========================================
echo.
echo You can now access:
echo - http://localhost:8000/deliverer/tournee
echo - http://localhost:8000/deliverer/client-topup
echo - http://localhost:8000/deliverer/menu
echo.
pause
