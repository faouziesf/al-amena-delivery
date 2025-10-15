@echo off
echo ========================================
echo NETTOYAGE VUES OBSOLETES - DELIVERER
echo ========================================
echo.

echo Creation du dossier _OBSOLETE...
mkdir "resources\views\deliverer\_OBSOLETE" 2>nul

echo.
echo Deplacement des vues obsoletes...
echo.

echo [1/6] simple-dashboard.blade.php
move "resources\views\deliverer\simple-dashboard.blade.php" "resources\views\deliverer\_OBSOLETE\" 2>nul

echo [2/6] run-sheet.blade.php
move "resources\views\deliverer\run-sheet.blade.php" "resources\views\deliverer\_OBSOLETE\" 2>nul

echo [3/6] tournee-direct.blade.php
move "resources\views\deliverer\tournee-direct.blade.php" "resources\views\deliverer\_OBSOLETE\" 2>nul

echo [4/6] task-detail-custom.blade.php
move "resources\views\deliverer\task-detail-custom.blade.php" "resources\views\deliverer\_OBSOLETE\" 2>nul

echo [5/6] client-recharge.blade.php
move "resources\views\deliverer\client-recharge.blade.php" "resources\views\deliverer\_OBSOLETE\" 2>nul

echo [6/6] recharge-client.blade.php
move "resources\views\deliverer\recharge-client.blade.php" "resources\views\deliverer\_OBSOLETE\" 2>nul

echo.
echo ========================================
echo NETTOYAGE TERMINE
echo ========================================
echo.
echo Vues obsoletes deplacees vers:
echo resources\views\deliverer\_OBSOLETE\
echo.
echo Vous pouvez les supprimer definitivement plus tard.
echo.
pause
