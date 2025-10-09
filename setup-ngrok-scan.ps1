# Script de configuration automatique pour Scan Depot avec Ngrok
# Usage: .\setup-ngrok-scan.ps1

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Configuration Scan Depot avec Ngrok  " -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Etape 1: Verifier que Laravel tourne
Write-Host "1. Verification Laravel..." -ForegroundColor Yellow
try {
    $laravelTest = Invoke-WebRequest -Uri "http://127.0.0.1:8000" -Method Get -TimeoutSec 5 -ErrorAction Stop
    Write-Host "   OK - Laravel est actif sur http://127.0.0.1:8000" -ForegroundColor Green
} catch {
    Write-Host "   ERREUR - Laravel n'est pas actif!" -ForegroundColor Red
    Write-Host "   Veuillez demarrer Laravel avec: php artisan serve" -ForegroundColor Yellow
    exit 1
}

Write-Host ""

# Etape 2: Demander l'URL ngrok
Write-Host "2. Configuration Ngrok..." -ForegroundColor Yellow
Write-Host "   Demarrez ngrok dans un autre terminal avec: ngrok http 8000" -ForegroundColor Cyan
Write-Host ""
$ngrokUrl = Read-Host "   Entrez votre URL ngrok (ex: https://abc123.ngrok-free.app)"

if ([string]::IsNullOrWhiteSpace($ngrokUrl)) {
    Write-Host "   ERREUR - URL ngrok requise!" -ForegroundColor Red
    exit 1
}

# Nettoyer l'URL
$ngrokUrl = $ngrokUrl.Trim()
if (-not $ngrokUrl.StartsWith("http")) {
    $ngrokUrl = "https://$ngrokUrl"
}

Write-Host "   URL ngrok: $ngrokUrl" -ForegroundColor Green
Write-Host ""

# Etape 3: Mettre a jour .env
Write-Host "3. Mise a jour .env..." -ForegroundColor Yellow
$envPath = ".\.env"

if (Test-Path $envPath) {
    $envContent = Get-Content $envPath -Raw
    
    # Remplacer APP_URL
    if ($envContent -match "APP_URL=.*") {
        $envContent = $envContent -replace "APP_URL=.*", "APP_URL=$ngrokUrl"
    } else {
        $envContent += "`nAPP_URL=$ngrokUrl"
    }
    
    Set-Content -Path $envPath -Value $envContent -NoNewline
    Write-Host "   OK - .env mis a jour avec APP_URL=$ngrokUrl" -ForegroundColor Green
} else {
    Write-Host "   ERREUR - Fichier .env introuvable!" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Etape 4: Vider le cache Laravel
Write-Host "4. Vidage du cache Laravel..." -ForegroundColor Yellow
php artisan config:clear | Out-Null
php artisan cache:clear | Out-Null
php artisan route:clear | Out-Null
Write-Host "   OK - Cache vide" -ForegroundColor Green
Write-Host ""

# Etape 5: Tester l'acces ngrok
Write-Host "5. Test de connexion ngrok..." -ForegroundColor Yellow
try {
    $ngrokTest = Invoke-WebRequest -Uri "$ngrokUrl/depot/debug/packages" -Method Get -TimeoutSec 10 -ErrorAction Stop
    Write-Host "   OK - Ngrok fonctionne correctement" -ForegroundColor Green
} catch {
    Write-Host "   AVERTISSEMENT - Impossible d'acceder a ngrok" -ForegroundColor Yellow
    Write-Host "   Verifiez que ngrok est bien demarre" -ForegroundColor Yellow
}

Write-Host ""

# Etape 6: Creer des colis de test
Write-Host "6. Creation de colis de test..." -ForegroundColor Yellow
$createTest = Read-Host "   Voulez-vous creer des colis de test? (O/N)"

if ($createTest -eq "O" -or $createTest -eq "o") {
    try {
        $createResponse = Invoke-RestMethod -Uri "$ngrokUrl/depot/debug/create-test-packages" -Method POST -ErrorAction Stop
        Write-Host "   OK - Colis de test crees:" -ForegroundColor Green
        $createResponse.created_packages | ForEach-Object {
            Write-Host "      - $_" -ForegroundColor White
        }
    } catch {
        Write-Host "   ERREUR - Impossible de creer les colis de test" -ForegroundColor Red
        Write-Host "   Erreur: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""

# Etape 7: Afficher les colis disponibles
Write-Host "7. Verification des colis disponibles..." -ForegroundColor Yellow
try {
    $packagesResponse = Invoke-RestMethod -Uri "$ngrokUrl/depot/debug/packages" -Method GET -ErrorAction Stop
    Write-Host "   Total de colis: $($packagesResponse.total_packages)" -ForegroundColor Green
    Write-Host "   Colis scannables: $($packagesResponse.scannable_packages)" -ForegroundColor Green
    
    if ($packagesResponse.sample_packages.Count -gt 0) {
        Write-Host ""
        Write-Host "   Exemples de codes a tester:" -ForegroundColor Cyan
        $packagesResponse.sample_packages | Select-Object -First 5 | ForEach-Object {
            Write-Host "      - $($_.package_code) (Statut: $($_.status))" -ForegroundColor White
        }
    }
} catch {
    Write-Host "   ERREUR - Impossible de recuperer les colis" -ForegroundColor Red
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Configuration terminee!               " -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Afficher les instructions
Write-Host "PROCHAINES ETAPES:" -ForegroundColor Cyan
Write-Host ""
Write-Host "1. Sur PC - Ouvrez dans votre navigateur:" -ForegroundColor Yellow
Write-Host "   $ngrokUrl/depot/scan" -ForegroundColor White
Write-Host ""
Write-Host "2. Sur Telephone - Scannez le QR code affiche" -ForegroundColor Yellow
Write-Host "   OU ouvrez l'URL manuellement" -ForegroundColor White
Write-Host ""
Write-Host "3. Scanner un colis de test:" -ForegroundColor Yellow
Write-Host "   Tapez: TEST_001" -ForegroundColor White
Write-Host "   Cliquez: Ajouter" -ForegroundColor White
Write-Host ""
Write-Host "4. Verifier que le colis apparait dans la liste" -ForegroundColor Yellow
Write-Host ""
Write-Host "5. Cliquer sur 'Valider Reception au Depot'" -ForegroundColor Yellow
Write-Host ""

# Proposer d'ouvrir le navigateur
$openBrowser = Read-Host "Voulez-vous ouvrir le navigateur maintenant? (O/N)"
if ($openBrowser -eq "O" -or $openBrowser -eq "o") {
    Start-Process "$ngrokUrl/depot/scan"
    Write-Host ""
    Write-Host "Navigateur ouvert!" -ForegroundColor Green
}

Write-Host ""
Write-Host "URLS UTILES:" -ForegroundColor Cyan
Write-Host "  Dashboard PC:        $ngrokUrl/depot/scan" -ForegroundColor White
Write-Host "  Voir colis:          $ngrokUrl/depot/debug/packages" -ForegroundColor White
Write-Host "  Tester code:         $ngrokUrl/depot/debug/test-search?code=TEST_001" -ForegroundColor White
Write-Host "  Creer colis test:    $ngrokUrl/depot/debug/create-test-packages" -ForegroundColor White
Write-Host ""
Write-Host "Bonne chance! " -ForegroundColor Green
Write-Host ""
