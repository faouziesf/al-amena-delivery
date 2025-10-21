@echo off
echo ========================================
echo  VALIDATION MIGRATION
echo ========================================
echo.
echo Verification de l'etat de la migration...
echo.

echo [1/5] Verification des migrations appliquees...
php artisan migrate:status
echo.

echo [2/5] Verification de la table packages...
php artisan tinker --execute="echo Schema::hasTable('packages') ? '✓ Table packages existe' : '✗ Table packages manquante'; echo PHP_EOL;"
echo.

echo [3/5] Verification de la table return_packages (doit etre supprimee)...
php artisan tinker --execute="echo Schema::hasTable('return_packages') ? '✗ ERREUR: Table return_packages existe encore' : '✓ Table return_packages supprimee'; echo PHP_EOL;"
echo.

echo [4/5] Verification des colonnes ajoutees...
php artisan tinker --execute="echo Schema::hasColumn('packages', 'package_type') ? '✓ package_type existe' : '✗ package_type manquante'; echo PHP_EOL;"
php artisan tinker --execute="echo Schema::hasColumn('packages', 'return_package_code') ? '✓ return_package_code existe' : '✗ return_package_code manquante'; echo PHP_EOL;"
php artisan tinker --execute="echo Schema::hasColumn('packages', 'original_package_id') ? '✓ original_package_id existe' : '✗ original_package_id manquante'; echo PHP_EOL;"
echo.

echo [5/5] Verification des colonnes supprimees...
php artisan tinker --execute="echo Schema::hasColumn('packages', 'supplier_data') ? '✗ ERREUR: supplier_data existe encore' : '✓ supplier_data supprimee'; echo PHP_EOL;"
php artisan tinker --execute="echo Schema::hasColumn('packages', 'pickup_delegation_id') ? '✗ ERREUR: pickup_delegation_id existe encore' : '✓ pickup_delegation_id supprimee'; echo PHP_EOL;"
echo.

echo ========================================
echo  VALIDATION TERMINEE
echo ========================================
echo.
pause
