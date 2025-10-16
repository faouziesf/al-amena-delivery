# ========================================
# Script d'Optimisation Mobile-First
# Applique le pattern sur les 36 vues restantes
# ========================================

Write-Host ">> OPTIMISATION MOBILE-FIRST - AUTOMATIQUE" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Chemin de base
$baseDir = "c:\Users\DELL\OneDrive\Documents\GitHub\al-amena-delivery\resources\views\client"

# Liste des 36 vues à optimiser
$views = @(
    # Packages (5 vues)
    "packages\create.blade.php",
    "packages\create-fast.blade.php",
    "packages\edit.blade.php",
    "packages\show.blade.php",
    "packages\filtered.blade.php",
    
    # Tickets (2 vues)
    "tickets\create.blade.php",
    "tickets\show.blade.php",
    
    # Manifests (5 vues)
    "manifests\index.blade.php",
    "manifests\create.blade.php",
    "manifests\show.blade.php",
    "manifests\print.blade.php",
    "manifests\pdf.blade.php",
    
    # Pickup Requests (3 vues)
    "pickup-requests\index.blade.php",
    "pickup-requests\create.blade.php",
    "pickup-requests\show.blade.php",
    
    # Wallet (6 vues)
    "wallet\transactions.blade.php",
    "wallet\transaction-details.blade.php",
    "wallet\topup.blade.php",
    "wallet\topup-requests.blade.php",
    "wallet\topup-request-show.blade.php",
    "wallet\withdrawal.blade.php",
    
    # Pickup Addresses (3 vues)
    "pickup-addresses\index.blade.php",
    "pickup-addresses\create.blade.php",
    "pickup-addresses\edit.blade.php",
    
    # Bank Accounts (4 vues)
    "bank-accounts\index.blade.php",
    "bank-accounts\create.blade.php",
    "bank-accounts\edit.blade.php",
    "bank-accounts\show.blade.php",
    
    # Withdrawals (2 vues)
    "withdrawals\index.blade.php",
    "withdrawals\show.blade.php",
    
    # Profile (2 vues)
    "profile\index.blade.php",
    "profile\edit.blade.php",
    
    # Returns (3 vues)
    "returns\pending.blade.php",
    "returns\show.blade.php",
    "returns\return-package-details.blade.php",
    
    # Notifications (2 vues)
    "notifications\index.blade.php",
    "notifications\settings.blade.php"
)

# Pattern de remplacement (ordre important!)
$replacements = @(
    # Headers
    @{Find='text-3xl lg:text-4xl'; Replace='text-xl sm:text-2xl'},
    @{Find='text-3xl md:text-4xl'; Replace='text-xl sm:text-2xl'},
    @{Find='text-3xl'; Replace='text-xl sm:text-2xl'},
    @{Find='text-2xl md:text-3xl'; Replace='text-lg sm:text-xl'},
    @{Find='text-2xl sm:text-3xl'; Replace='text-lg sm:text-xl'},
    @{Find='text-2xl'; Replace='text-lg sm:text-xl'},
    @{Find='text-xl lg:text-2xl'; Replace='text-base sm:text-lg'},
    
    # Espacements mb
    @{Find='mb-8'; Replace='mb-4 sm:mb-6'},
    @{Find='mb-6'; Replace='mb-3 sm:mb-4'},
    @{Find='mb-4'; Replace='mb-2 sm:mb-3'},
    
    # Espacements p
    @{Find='(?<!\w)p-8(?!\w)'; Replace='p-4 sm:p-6'; Regex=$true},
    @{Find='(?<!\w)p-6(?!\w)'; Replace='p-3 sm:p-4'; Regex=$true},
    @{Find='(?<!\w)p-4(?!\w)'; Replace='p-2.5 sm:p-3'; Regex=$true},
    
    # Espacements px/py
    @{Find='px-8'; Replace='px-4 sm:px-6'},
    @{Find='px-6(?! py-3)'; Replace='px-4'; Regex=$true},
    @{Find='py-8'; Replace='py-4 sm:py-6'},
    @{Find='py-6'; Replace='py-3 sm:py-4'},
    @{Find='py-4'; Replace='py-2 sm:py-3'},
    
    # Gap
    @{Find='gap-8'; Replace='gap-4 sm:gap-6'},
    @{Find='gap-6'; Replace='gap-3 sm:gap-4'},
    @{Find='gap-4'; Replace='gap-2 sm:gap-3'},
    
    # Space-y
    @{Find='space-y-8'; Replace='space-y-4 sm:space-y-6'},
    @{Find='space-y-6'; Replace='space-y-3 sm:space-y-4'},
    @{Find='space-y-4'; Replace='space-y-2 sm:space-y-3'},
    
    # Grilles (important: du plus spécifique au moins)
    @{Find='grid-cols-1 sm:grid-cols-2 lg:grid-cols-3'; Replace='grid-cols-2 lg:grid-cols-3'},
    @{Find='grid-cols-1 sm:grid-cols-2 lg:grid-cols-4'; Replace='grid-cols-2 lg:grid-cols-4'},
    @{Find='grid-cols-1 sm:grid-cols-2 md:grid-cols-3'; Replace='grid-cols-2 md:grid-cols-3'},
    @{Find='grid-cols-1 sm:grid-cols-2 md:grid-cols-4'; Replace='grid-cols-2 md:grid-cols-4'},
    @{Find='grid-cols-1 sm:grid-cols-2'; Replace='grid-cols-2'},
    @{Find='grid-cols-1 md:grid-cols-3'; Replace='grid-cols-1 sm:grid-cols-3'},
    @{Find='grid-cols-1 md:grid-cols-4'; Replace='grid-cols-2 lg:grid-cols-4'},
    @{Find='grid-cols-1 lg:grid-cols-2'; Replace='grid-cols-2'},
    
    # Cartes rounded
    @{Find='rounded-2xl'; Replace='rounded-xl'},
    @{Find='rounded-xl(?! rounded-full)'; Replace='rounded-lg'; Regex=$true},
    
    # Cartes shadow
    @{Find='shadow-xl'; Replace='shadow-md'},
    @{Find='shadow-lg'; Replace='shadow-sm'},
    
    # Boutons px py (combinés)
    @{Find='px-6 py-3'; Replace='px-3 sm:px-4 py-2'},
    @{Find='px-5 py-2\.5'; Replace='px-4 py-2'},
    @{Find='px-8 py-4'; Replace='px-4 sm:px-6 py-2 sm:py-3'},
    
    # Icônes w h
    @{Find='w-8 h-8'; Replace='w-5 h-5'},
    @{Find='w-6 h-6'; Replace='w-5 h-5'},
    
    # Badges
    @{Find='px-3 py-1\.5'; Replace='px-2 py-1'},
    @{Find='px-2\.5 py-1'; Replace='px-2 py-0.5'}
)

# Compteurs
$total = $views.Count
$success = 0
$failed = 0
$skipped = 0

# Créer dossier de backup
$backupDir = "c:\Users\DELL\OneDrive\Documents\GitHub\al-amena-delivery\backups\optimization-" + (Get-Date -Format "yyyyMMdd-HHmmss")
New-Item -ItemType Directory -Path $backupDir -Force | Out-Null
Write-Host "[BACKUP] Backup cree: $backupDir" -ForegroundColor Green
Write-Host ""

# Traiter chaque vue
foreach ($view in $views) {
    $filePath = Join-Path $baseDir $view
    
    Write-Host "[PROCESS] Traitement: $view" -ForegroundColor Yellow
    
    # Vérifier si le fichier existe
    if (-Not (Test-Path $filePath)) {
        Write-Host "   [SKIP] Fichier non trouve, ignore" -ForegroundColor DarkYellow
        $skipped++
        continue
    }
    
    try {
        # Backup du fichier
        $backupPath = Join-Path $backupDir $view
        $backupFolder = Split-Path $backupPath -Parent
        New-Item -ItemType Directory -Path $backupFolder -Force | Out-Null
        Copy-Item $filePath $backupPath -Force
        
        # Lire le contenu
        $content = Get-Content $filePath -Raw -Encoding UTF8
        $originalContent = $content
        $changesCount = 0
        
        # Appliquer tous les remplacements
        foreach ($replacement in $replacements) {
            $oldContent = $content
            
            if ($replacement.Regex) {
                $content = $content -replace $replacement.Find, $replacement.Replace
            } else {
                $content = $content -replace [regex]::Escape($replacement.Find), $replacement.Replace
            }
            
            if ($oldContent -ne $content) {
                $changesCount++
            }
        }
        
        # Sauvegarder si des changements ont été faits
        if ($content -ne $originalContent) {
            Set-Content $filePath -Value $content -Encoding UTF8 -NoNewline
            Write-Host "   [OK] Optimise ($changesCount remplacements)" -ForegroundColor Green
            $success++
        } else {
            Write-Host "   [INFO] Aucun changement necessaire" -ForegroundColor Gray
            $success++
        }
        
    } catch {
        Write-Host "   [ERREUR] Erreur: $_" -ForegroundColor Red
        $failed++
    }
}

# Rapport final
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ">> OPTIMISATION TERMINEE" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "[STATS] Statistiques:" -ForegroundColor White
Write-Host "   Total vues: $total" -ForegroundColor White
Write-Host "   [OK] Succes: $success" -ForegroundColor Green
Write-Host "   [FAIL] Echecs: $failed" -ForegroundColor Red
Write-Host "   [SKIP] Ignorees: $skipped" -ForegroundColor Yellow
Write-Host ""
Write-Host "[BACKUP] Backups: $backupDir" -ForegroundColor Green
Write-Host ""

# Mettre à jour le fichier de progression
$progressionFile = "c:\Users\DELL\OneDrive\Documents\GitHub\al-amena-delivery\PROGRESSION_AUTOMATIQUE.md"
$progressionContent = @"
# 🎉 OPTIMISATION AUTOMATIQUE TERMINÉE

**Date**: $(Get-Date -Format "dd/MM/yyyy HH:mm:ss")

## 📊 Résultats

- **Total vues traitées**: $total
- **✅ Succès**: $success
- **❌ Échecs**: $failed
- **⚠️ Ignorées**: $skipped

## 📁 Backup

Tous les fichiers originaux sont sauvegardés dans:
``````
$backupDir
``````

## 🎯 Prochaine Étape

1. Vérifier visuellement les vues optimisées
2. Tester l'application sur mobile
3. Exécuter: ``php artisan view:clear``
4. Commit des changements

---

**Script exécuté**: optimize-views.ps1
"@

Set-Content $progressionFile -Value $progressionContent -Encoding UTF8

Write-Host "[OK] Rapport genere: PROGRESSION_AUTOMATIQUE.md" -ForegroundColor Green
Write-Host ""
Write-Host "[NEXT] Prochaines etapes:" -ForegroundColor Cyan
Write-Host "   1. Executer: php artisan view:clear" -ForegroundColor White
Write-Host "   2. Tester visuellement les vues" -ForegroundColor White
Write-Host "   3. Commit des changements" -ForegroundColor White
Write-Host ""
