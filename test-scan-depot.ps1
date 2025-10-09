# Script de test pour le systeme de scan depot
# Usage: .\test-scan-depot.ps1

$baseUrl = "http://127.0.0.1:8000"

Write-Host "Test du Systeme de Scan Depot" -ForegroundColor Cyan
Write-Host "=================================" -ForegroundColor Cyan
Write-Host ""

# Test 1: Vérifier les colis disponibles
Write-Host "📦 Test 1: Vérification des colis disponibles..." -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/depot/debug/packages" -Method Get
    Write-Host "✅ Total de colis: $($response.total_packages)" -ForegroundColor Green
    Write-Host "✅ Colis scannables: $($response.scannable_packages)" -ForegroundColor Green
    
    if ($response.sample_packages.Count -gt 0) {
        Write-Host "`n📋 Exemples de codes à tester:" -ForegroundColor Cyan
        $response.sample_packages | Select-Object -First 5 | ForEach-Object {
            Write-Host "   - $($_.package_code) (Statut: $($_.status))" -ForegroundColor White
        }
    } else {
        Write-Host "⚠️  Aucun colis scannable trouvé" -ForegroundColor Yellow
        Write-Host "   Création de colis de test..." -ForegroundColor Yellow
        
        # Créer des colis de test
        $createResponse = Invoke-RestMethod -Uri "$baseUrl/depot/debug/create-test-packages" -Method Post
        Write-Host "✅ Colis de test créés: $($createResponse.created_packages -join ', ')" -ForegroundColor Green
    }
} catch {
    Write-Host "❌ Erreur: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""

# Test 2: Tester la recherche d'un code
Write-Host "🔍 Test 2: Test de recherche de code..." -ForegroundColor Yellow
$testCode = Read-Host "Entrez un code à tester (ou appuyez sur Entrée pour 'TEST_001')"
if ([string]::IsNullOrWhiteSpace($testCode)) {
    $testCode = "TEST_001"
}

try {
    $searchResponse = Invoke-RestMethod -Uri "$baseUrl/depot/debug/test-search?code=$testCode" -Method Get
    
    Write-Host "`n📊 Résultats de recherche pour '$testCode':" -ForegroundColor Cyan
    
    $found = $false
    foreach ($variant in $searchResponse.variants_tested.PSObject.Properties) {
        $result = $variant.Value
        if ($result.found) {
            Write-Host "   ✅ Variante '$($variant.Name)': TROUVÉ" -ForegroundColor Green
            Write-Host "      Code réel: $($result.package_code)" -ForegroundColor White
            Write-Host "      Statut: $($result.status)" -ForegroundColor White
            Write-Host "      Scannable: $($result.scannable)" -ForegroundColor White
            $found = $true
        } else {
            Write-Host "   ❌ Variante '$($variant.Name)': Non trouvé" -ForegroundColor Red
        }
    }
    
    if ($searchResponse.like_search.found) {
        Write-Host "`n   🔍 Recherche LIKE: TROUVÉ" -ForegroundColor Green
        Write-Host "      Code réel: $($searchResponse.like_search.package_code)" -ForegroundColor White
    }
    
    if (-not $found -and -not $searchResponse.like_search.found) {
        Write-Host "`n⚠️  Code '$testCode' introuvable dans la base de données" -ForegroundColor Yellow
        Write-Host "   Suggestions:" -ForegroundColor Cyan
        Write-Host "   1. Créez des colis de test avec: POST $baseUrl/depot/debug/create-test-packages" -ForegroundColor White
        Write-Host "   2. Vérifiez les codes disponibles avec: GET $baseUrl/depot/debug/packages" -ForegroundColor White
    }
    
} catch {
    Write-Host "❌ Erreur: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "=================================" -ForegroundColor Cyan
Write-Host "✅ Tests terminés" -ForegroundColor Green
Write-Host ""
Write-Host "📝 Prochaines étapes:" -ForegroundColor Cyan
Write-Host "   1. Ouvrez http://127.0.0.1:8000/depot/scan sur votre PC" -ForegroundColor White
Write-Host "   2. Scannez le QR code avec votre téléphone" -ForegroundColor White
Write-Host "   3. Scannez un des codes listés ci-dessus" -ForegroundColor White
Write-Host "   4. Vérifiez que le colis apparaît dans la liste" -ForegroundColor White
Write-Host ""
