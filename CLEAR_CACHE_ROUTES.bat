@echo off
echo ========================================
echo CLEAR CACHE ROUTES - AL-AMENA DELIVERY
echo ========================================
echo.

echo [1/5] Clearing route cache...
php artisan route:clear
echo.

echo [2/5] Clearing config cache...
php artisan config:clear
echo.

echo [3/5] Clearing application cache...
php artisan cache:clear
echo.

echo [4/5] Clearing view cache...
php artisan view:clear
echo.

echo [5/5] Listing deliverer routes...
php artisan route:list --name=deliverer
echo.

echo ========================================
echo DONE! Routes should now be available.
echo ========================================
pause
