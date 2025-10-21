@echo off
cls
echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║                                                              ║
echo ║              VERIFICATION FINALE AVANT DEPLOIEMENT          ║
echo ║                                                              ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.
echo Verification en cours...
echo.

echo [1/7] Verification des migrations...
php artisan migrate:status | findstr "2025_01_19"
if %errorlevel% neq 0 (
    echo ✗ ERREUR: Migrations non appliquees
    pause
    exit /b 1
)
echo ✓ Migrations appliquees
echo.

echo [2/7] Verification table packages...
php artisan tinker --execute="exit(Schema::hasTable('packages') ? 0 : 1);"
if %errorlevel% neq 0 (
    echo ✗ ERREUR: Table packages manquante
    pause
    exit /b 1
)
echo ✓ Table packages OK
echo.

echo [3/7] Verification table return_packages (doit etre supprimee)...
php artisan tinker --execute="exit(Schema::hasTable('return_packages') ? 1 : 0);"
if %errorlevel% neq 0 (
    echo ✗ ERREUR: Table return_packages existe encore
    pause
    exit /b 1
)
echo ✓ Table return_packages supprimee
echo.

echo [4/7] Verification colonne package_type...
php artisan tinker --execute="exit(Schema::hasColumn('packages', 'package_type') ? 0 : 1);"
if %errorlevel% neq 0 (
    echo ✗ ERREUR: Colonne package_type manquante
    pause
    exit /b 1
)
echo ✓ Colonne package_type OK
echo.

echo [5/7] Verification colonne return_package_code...
php artisan tinker --execute="exit(Schema::hasColumn('packages', 'return_package_code') ? 0 : 1);"
if %errorlevel% neq 0 (
    echo ✗ ERREUR: Colonne return_package_code manquante
    pause
    exit /b 1
)
echo ✓ Colonne return_package_code OK
echo.

echo [6/7] Verification suppression colonnes inutiles...
php artisan tinker --execute="exit(Schema::hasColumn('packages', 'supplier_data') ? 1 : 0);"
if %errorlevel% neq 0 (
    echo ✗ ERREUR: Colonne supplier_data existe encore
    pause
    exit /b 1
)
echo ✓ Colonnes inutiles supprimees
echo.

echo [7/7] Verification colis de retour migre...
php artisan tinker --execute="exit(DB::table('packages')->where('package_type', 'RETURN')->exists() ? 0 : 1);"
if %errorlevel% neq 0 (
    echo ⚠ Aucun colis de retour (normal si base vide)
) else (
    echo ✓ Colis de retour migres
)
echo.

echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║                                                              ║
echo ║              ✅ VERIFICATION REUSSIE                        ║
echo ║                                                              ║
echo ║              TOUT EST PRET POUR LE DEPLOIEMENT              ║
echo ║                                                              ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.
echo Vous pouvez maintenant :
echo   1. Pousser sur git
echo   2. Deployer sur le serveur
echo   3. Lancer php artisan migrate --force
echo.
pause
