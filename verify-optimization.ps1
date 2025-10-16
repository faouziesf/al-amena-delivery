# ========================================
# Script de Vérification de l'Optimisation
# Vérifie que toutes les vues ont été optimisées
# ========================================

Write-Host "🔍 VÉRIFICATION DE L'OPTIMISATION MOBILE-FIRST" -ForegroundColor Cyan
Write-Host "==============================================" -ForegroundColor Cyan
Write-Host ""

# Chemin de base
$baseDir = "c:\Users\DELL\OneDrive\Documents\GitHub\al-amena-delivery\resources\views\client"

# Liste de toutes les vues (7 déjà faites + 36 nouvelles = 43 total)
$allViews = @{
    'Déjà Optimisées (7)' = @(
        'dashboard.blade.php',
        'wallet\index.blade.php',
        'tickets\index.blade.php',
        'packages\index.blade.php',
        'packages\partials\packages-list.blade.php'
    )
    'Nouvellement Optimisées (36)' = @(
        'packages\create.blade.php',
        'packages\create-fast.blade.php',
        'packages\edit.blade.php',
        'packages\show.blade.php',
        'packages\filtered.blade.php',
        'tickets\create.blade.php',
        'tickets\show.blade.php',
        'manifests\index.blade.php',
        'manifests\create.blade.php',
        'manifests\show.blade.php',
        'manifests\print.blade.php',
        'manifests\pdf.blade.php',
        'pickup-requests\index.blade.php',
        'pickup-requests\create.blade.php',
        'pickup-requests\show.blade.php',
        'wallet\transactions.blade.php',
        'wallet\transaction-details.blade.php',
        'wallet\topup.blade.php',
        'wallet\topup-requests.blade.php',
        'wallet\topup-request-show.blade.php',
        'wallet\withdrawal.blade.php',
        'pickup-addresses\index.blade.php',
        'pickup-addresses\create.blade.php',
        'pickup-addresses\edit.blade.php',
        'bank-accounts\index.blade.php',
        'bank-accounts\create.blade.php',
        'bank-accounts\edit.blade.php',
        'bank-accounts\show.blade.php',
        'withdrawals\index.blade.php',
        'withdrawals\show.blade.php',
        'profile\index.blade.php',
        'profile\edit.blade.php',
        'returns\pending.blade.php',
        'returns\show.blade.php',
        'returns\return-package-details.blade.php',
        'notifications\index.blade.php',
        'notifications\settings.blade.php'
    )
}

# Patterns à vérifier (doivent être rares après optimisation)
$oldPatterns = @{
    'text-3xl (non optimisé)' = 'text-3xl(?!\s*sm:)'
    'text-2xl (non optimisé)' = 'text-2xl(?!\s*sm:|\s*md:)'
    'mb-8 (non optimisé)' = '\bmb-8\b(?!\s*sm:)'
    'mb-6 (non optimisé)' = '\bmb-6\b(?!\s*sm:)'
    'p-6 (non optimisé)' = '\bp-6\b(?!\s*sm:)'
    'gap-6 (non optimisé)' = '\bgap-6\b(?!\s*sm:)'
    'rounded-2xl' = '\brounded-2xl\b'
    'shadow-lg' = '\bshadow-lg\b'
    'px-6 py-3' = 'px-6\s+py-3'
    'grid-cols-1 sm:grid-cols-2 (peut être optimisé)' = 'grid-cols-1\s+sm:grid-cols-2(?!\s+lg:)'
}

# Patterns optimisés (doivent être présents)
$newPatterns = @{
    'text-xl sm:text-2xl' = 'text-xl\s+sm:text-2xl'
    'mb-4 sm:mb-6' = 'mb-4\s+sm:mb-6'
    'p-3 sm:p-4' = 'p-3\s+sm:p-4'
    'gap-3 sm:gap-4' = 'gap-3\s+sm:gap-4'
    'rounded-xl' = '\brounded-xl\b'
    'shadow-sm' = '\bshadow-sm\b'
    'grid-cols-2' = 'grid-cols-2(?!\s+sm:grid-cols-2)'
}

$totalChecked = 0
$totalOptimized = 0
$totalIssues = 0
$issuesList = @()

Write-Host "📊 Vérification des patterns d'optimisation..." -ForegroundColor Yellow
Write-Host ""

foreach ($category in $allViews.Keys) {
    Write-Host "🔍 Catégorie: $category" -ForegroundColor Cyan
    
    foreach ($view in $allViews[$category]) {
        $filePath = Join-Path $baseDir $view
        
        if (-Not (Test-Path $filePath)) {
            Write-Host "   ⚠️  $view - Fichier non trouvé" -ForegroundColor Yellow
            continue
        }
        
        $content = Get-Content $filePath -Raw -Encoding UTF8
        $totalChecked++
        
        $hasOldPatterns = $false
        $hasNewPatterns = $false
        $fileIssues = @()
        
        # Vérifier les anciens patterns (ne doivent pas être nombreux)
        foreach ($patternName in $oldPatterns.Keys) {
            $pattern = $oldPatterns[$patternName]
            $matches = [regex]::Matches($content, $pattern)
            
            if ($matches.Count -gt 3) {  # Tolérance de 3 occurrences
                $hasOldPatterns = $true
                $fileIssues += "      ❌ $patternName trouvé $($matches.Count) fois"
            }
        }
        
        # Vérifier les nouveaux patterns (doivent être présents)
        $newPatternsCount = 0
        foreach ($patternName in $newPatterns.Keys) {
            $pattern = $newPatterns[$patternName]
            if ($content -match $pattern) {
                $newPatternsCount++
            }
        }
        
        if ($newPatternsCount -ge 2) {  # Au moins 2 patterns optimisés
            $hasNewPatterns = $true
        }
        
        # Afficher le résultat
        if ($hasNewPatterns -and -not $hasOldPatterns) {
            Write-Host "   ✅ $view - Optimisé" -ForegroundColor Green
            $totalOptimized++
        } elseif ($hasNewPatterns) {
            Write-Host "   ⚠️  $view - Partiellement optimisé" -ForegroundColor Yellow
            foreach ($issue in $fileIssues) {
                Write-Host $issue -ForegroundColor DarkYellow
            }
            $totalIssues++
            $issuesList += @{File=$view; Issues=$fileIssues}
        } else {
            Write-Host "   ❌ $view - Non optimisé" -ForegroundColor Red
            $totalIssues++
            $issuesList += @{File=$view; Issues=@("Aucun pattern optimisé détecté")}
        }
    }
    
    Write-Host ""
}

# Rapport final
Write-Host "==============================================" -ForegroundColor Cyan
Write-Host "📊 RAPPORT DE VÉRIFICATION" -ForegroundColor Cyan
Write-Host "==============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Vues vérifiées: $totalChecked" -ForegroundColor White
Write-Host "✅ Vues optimisées: $totalOptimized" -ForegroundColor Green
Write-Host "⚠️  Vues avec problèmes: $totalIssues" -ForegroundColor Yellow
Write-Host ""

$percentage = [math]::Round(($totalOptimized / $totalChecked) * 100, 1)
Write-Host "Taux d'optimisation: $percentage%" -ForegroundColor $(if ($percentage -ge 90) {'Green'} elseif ($percentage -ge 70) {'Yellow'} else {'Red'})
Write-Host ""

if ($totalIssues -gt 0) {
    Write-Host "⚠️  PROBLÈMES DÉTECTÉS:" -ForegroundColor Yellow
    Write-Host ""
    
    foreach ($issue in $issuesList) {
        Write-Host "   📄 $($issue.File)" -ForegroundColor White
        foreach ($detail in $issue.Issues) {
            Write-Host "   $detail" -ForegroundColor DarkYellow
        }
        Write-Host ""
    }
    
    Write-Host "💡 Recommandation: Ré-exécuter le script d'optimisation sur ces vues" -ForegroundColor Cyan
} else {
    Write-Host "🎉 TOUTES LES VUES SONT OPTIMISÉES!" -ForegroundColor Green
    Write-Host ""
    Write-Host "✅ Prochaines étapes:" -ForegroundColor Cyan
    Write-Host "   1. Exécuter: php artisan view:clear" -ForegroundColor White
    Write-Host "   2. Tester l'application sur mobile" -ForegroundColor White
    Write-Host "   3. Commit des changements" -ForegroundColor White
}

Write-Host ""

# Générer un rapport détaillé
$reportFile = "c:\Users\DELL\OneDrive\Documents\GitHub\al-amena-delivery\RAPPORT_VERIFICATION.md"
$reportContent = @"
# 🔍 Rapport de Vérification de l'Optimisation

**Date**: $(Get-Date -Format "dd/MM/yyyy HH:mm:ss")

## 📊 Statistiques Globales

- **Vues vérifiées**: $totalChecked
- **✅ Vues optimisées**: $totalOptimized
- **⚠️ Vues avec problèmes**: $totalIssues
- **Taux d'optimisation**: $percentage%

## $(if ($totalIssues -eq 0) {'🎉'} else {'⚠️'}) Résultat

$(if ($totalIssues -eq 0) {
    "### ✅ TOUTES LES VUES SONT OPTIMISÉES!`n`n" +
    "Le pattern mobile-first a été appliqué avec succès sur toutes les vues.`n`n" +
    "**Prochaines étapes**:`n" +
    "1. Exécuter: ``php artisan view:clear```n" +
    "2. Tester l'application sur mobile`n" +
    "3. Commit des changements"
} else {
    "### ⚠️ PROBLÈMES DÉTECTÉS`n`n" +
    "Certaines vues nécessitent une attention supplémentaire:`n`n" +
    ($issuesList | ForEach-Object {
        "#### $($_.File)`n" +
        ($_.Issues | ForEach-Object { "- $_`n" }) +
        "`n"
    } | Out-String) +
    "**Recommandation**: Ré-exécuter le script d'optimisation"
})

---

**Script**: verify-optimization.ps1
"@

Set-Content $reportFile -Value $reportContent -Encoding UTF8

Write-Host "📄 Rapport détaillé généré: RAPPORT_VERIFICATION.md" -ForegroundColor Green
Write-Host ""
