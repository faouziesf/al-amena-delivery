@echo off
echo ========================================
echo  MIGRATION REFONTE PACKAGES
echo ========================================
echo.
echo Cette migration va :
echo 1. Ajouter package_type et colonnes retour
echo 2. Supprimer colonnes inutiles
echo 3. Migrer return_packages vers packages
echo 4. Supprimer la table return_packages
echo.
echo IMPORTANT: Une sauvegarde sera creee automatiquement
echo.
pause

echo.
echo [1/4] Creation de la sauvegarde...
copy database\database.sqlite database\database.sqlite.backup_%date:~-4,4%%date:~-7,2%%date:~-10,2%_%time:~0,2%%time:~3,2%%time:~6,2%
if %errorlevel% neq 0 (
    echo ERREUR: Impossible de creer la sauvegarde
    pause
    exit /b 1
)
echo ✓ Sauvegarde creee avec succes

echo.
echo [2/4] Verification des migrations existantes...
php artisan migrate:status
if %errorlevel% neq 0 (
    echo ERREUR: Impossible de verifier les migrations
    pause
    exit /b 1
)

echo.
echo [3/4] Execution des migrations de refonte...
php artisan migrate --force
if %errorlevel% neq 0 (
    echo ERREUR: Echec de la migration
    echo.
    echo Pour restaurer la sauvegarde:
    echo copy database\database.sqlite.backup_* database\database.sqlite
    pause
    exit /b 1
)
echo ✓ Migrations executees avec succes

echo.
echo [4/4] Verification post-migration...
php artisan tinker --execute="echo 'Types de colis: '; DB::table('packages')->selectRaw('package_type, COUNT(*) as total')->groupBy('package_type')->get()->each(function($row) { echo $row->package_type . ': ' . $row->total . PHP_EOL; });"

echo.
echo ========================================
echo  MIGRATION TERMINEE AVEC SUCCES
echo ========================================
echo.
echo Prochaines etapes:
echo 1. Tester le scan livreur avec RET-XXXXXXXX
echo 2. Tester l'interface paiements
echo 3. Verifier que tout fonctionne
echo.
echo Sauvegarde disponible dans:
echo database\database.sqlite.backup_%date:~-4,4%%date:~-7,2%%date:~-10,2%_%time:~0,2%%time:~3,2%%time:~6,2%
echo.
pause
