# ✅ Import CSV en Masse - Client

## 🎯 Fonctionnalité Ajoutée

L'interface d'importation CSV en masse pour les clients est maintenant **accessible depuis le menu client**.

---

## 📍 Points d'Accès

### 1. **Menu Sidebar** (Mobile & Desktop)
- Nouvelle option **"Import CSV"** ajoutée dans le menu client
- Placée après "Nouveau Colis"
- Icône de cloud/upload
- URL : `/client/packages/import/csv`

### 2. **Page d'Index des Colis**
- Bouton **"Import CSV"** ajouté dans les actions rapides
- Version mobile : 3 boutons (Nouveau, Rapide, Import CSV)
- Version desktop : 3 boutons également
- Couleur distinctive : gradient bleu-cyan

### 3. **Routes Disponibles**
```php
// Routes d'import
Route::get('/csv', [ClientPackageImportController::class, 'showImportForm'])->name('csv');
Route::post('/csv', [ClientPackageImportController::class, 'processImportCsv'])->name('process');
Route::get('/template', [ClientPackageImportController::class, 'downloadTemplate'])->name('template');
Route::get('/{batch}/status', [ClientPackageImportController::class, 'showImportStatus'])->name('status');

// Routes API (ajoutées)
Route::get('/{batch}/progress', [ClientPackageImportController::class, 'apiImportProgress'])->name('progress');
Route::get('/{batch}/errors', [ClientPackageImportController::class, 'apiImportErrors'])->name('errors');
Route::post('/validate-csv', [ClientPackageImportController::class, 'apiValidateCsv'])->name('validate');
```

---

## 🎨 Interface

### **Page d'Import** (`import-csv.blade.php`)
- **Upload drag & drop** de fichiers CSV
- Options de configuration :
  - Délimiteur (point-virgule, virgule, tabulation)
  - Encodage (UTF-8, ISO-8859-1)
  - Première ligne = en-têtes
- Affichage du **solde disponible**
- Instructions claires
- **Téléchargement du template CSV**
- Historique des 10 derniers imports

### **Page de Statut** (`import-status.blade.php`)
- Résumé du batch d'import
- Statistiques en temps réel :
  - Total lignes
  - Créés avec succès
  - Échecs
  - Taux de réussite
- Barre de progression (si en cours)
- Liste des erreurs détectées
- Liste des colis créés avec actions :
  - Sélection multiple
  - Impression groupée
  - Suppression individuelle

---

## 📊 Fonctionnalités du Contrôleur

### **ClientPackageImportController**

#### Méthodes Principales
1. **showImportForm()** - Affiche le formulaire d'import
2. **processImportCsv()** - Traite le fichier CSV uploadé
3. **showImportStatus()** - Affiche le statut d'un import
4. **downloadTemplate()** - Télécharge le template CSV

#### Méthodes API (Nouvellement ajoutées)
5. **apiImportProgress()** - Récupère la progression d'un import
6. **apiImportErrors()** - Récupère les erreurs d'un import
7. **apiValidateCsv()** - Valide un CSV avant traitement

#### Méthodes Privées
- **parseCsvFile()** - Parse le fichier CSV
- **validateCsvData()** - Valide toutes les données
- **processImportBatch()** - Traite le batch complet
- **preparePackageData()** - Prépare les données d'un colis
- **createPackageFromImport()** - Crée un colis depuis l'import

---

## 📝 Format du Template CSV

### Colonnes Obligatoires
| Colonne | Description | Exemple |
|---------|-------------|---------|
| Nom Fournisseur | Nom du fournisseur | Fournisseur Test |
| Téléphone Fournisseur | 8 chiffres | 12345678 |
| Délégation Pickup | Nom exact | Tunis |
| Adresse Pickup | Adresse complète | 123 Rue Exemple |
| Nom Destinataire | Nom client | Client Test |
| Téléphone Destinataire | 8 chiffres | 87654321 |
| Délégation Destination | Nom exact | Sfax |
| Adresse Destination | Adresse complète | 456 Avenue Test |
| Description Contenu | Contenu du colis | Vêtements |
| Montant COD | Montant à collecter | 50.000 |

### Colonnes Optionnelles
| Colonne | Description | Exemple |
|---------|-------------|---------|
| Poids | En kg | 2.500 |
| Valeur Déclarée | Valeur du colis | 100.000 |
| Notes | Instructions spéciales | Livrer avant 17h |
| Fragile | oui/non | non |
| Signature Requise | oui/non | oui |

### Délimiteur par Défaut
- **Point-virgule (;)** recommandé
- Encodage UTF-8

---

## 🔍 Validations

### Automatiques
- ✅ Vérification du nombre de colonnes (minimum 10)
- ✅ Validation des champs obligatoires
- ✅ Vérification que les délégations existent
- ✅ Validation des montants (COD, poids, valeur)
- ✅ Vérification du solde suffisant
- ✅ Détection des délégations pickup/destination identiques

### Limites
- **Taille maximale** : 5 MB
- **Formats acceptés** : CSV, TXT
- **Maximum par import** : 500 colis (recommandé)

---

## 💾 Modèle ImportBatch

### Propriétés
```php
'batch_code'       // Code unique (IMP_XXXXXXXX_YYYYMMDD)
'user_id'          // ID du client
'filename'         // Nom du fichier
'total_rows'       // Total de lignes
'processed_rows'   // Lignes traitées
'successful_rows'  // Lignes réussies
'failed_rows'      // Lignes échouées
'status'           // PENDING, PROCESSING, COMPLETED, FAILED
'started_at'       // Date de début
'completed_at'     // Date de fin
'errors'           // JSON des erreurs
'summary'          // JSON du résumé
'file_path'        // Chemin du fichier
```

### Méthodes Utiles
- `isCompleted()` - Import terminé ?
- `isFailed()` - Import échoué ?
- `isProcessing()` - Import en cours ?
- `getSuccessRateAttribute()` - Taux de réussite (%)
- `hasErrors()` - A des erreurs ?
- `getTopErrors()` - Top X erreurs
- `markAsStarted()` - Marquer comme démarré
- `markAsCompleted()` - Marquer comme terminé
- `incrementProcessed()` - Incrémenter progression

---

## 🚀 Workflow d'Import

1. **Client accède** à l'interface via le menu ou la page colis
2. **Upload** du fichier CSV (drag & drop ou sélection)
3. **Configuration** des options (délimiteur, encodage)
4. **Validation** automatique du fichier
5. **Création** du batch d'import
6. **Traitement** ligne par ligne
7. **Affichage** du statut en temps réel
8. **Consultation** des résultats et erreurs
9. **Actions** possibles :
   - Impression des bons de livraison
   - Téléchargement du template
   - Nouvel import

---

## 💰 Gestion Financière

### Déduction Automatique
- **Montant escrow** calculé pour chaque colis :
  - Si COD ≥ frais livraison → déduction frais retour
  - Si COD < frais livraison → déduction frais livraison
- **Vérification solde** avant traitement
- **Transaction enregistrée** pour chaque colis créé
- **Type** : `PACKAGE_CREATION_DEBIT`

---

## 📈 Suivi et Historique

### Sur la Page d'Import
- **10 derniers imports** affichés
- Informations par import :
  - Code batch
  - Nom du fichier
  - Date et heure
  - Statut coloré
  - Taux de réussite
  - Lien vers les détails

### Sur la Page de Statut
- **Auto-refresh** toutes les 5 secondes (si en cours)
- **Statistiques détaillées**
- **Liste complète** des erreurs
- **Actions groupées** sur les colis créés

---

## 🎯 Fichiers Modifiés

1. **resources/views/layouts/partials/client-menu.blade.php**
   - Ajout de l'option "Import CSV" dans le menu

2. **resources/views/client/packages/index.blade.php**
   - Ajout du bouton "Import CSV" (mobile + desktop)

3. **app/Http/Controllers/Client/ClientPackageImportController.php**
   - Ajout de 3 méthodes API :
     - `apiImportProgress()`
     - `apiImportErrors()`
     - `apiValidateCsv()`

---

## ✅ Tests Recommandés

1. **Accès Menu**
   - Vérifier l'option dans le sidebar
   - Tester sur mobile et desktop

2. **Accès Page Index**
   - Vérifier les 3 boutons d'action
   - Tester responsive

3. **Upload Fichier**
   - Drag & drop
   - Sélection manuelle
   - Validation format/taille

4. **Traitement**
   - Import réussi (toutes lignes valides)
   - Import avec erreurs
   - Import avec solde insuffisant

5. **Affichage Statut**
   - Progression en temps réel
   - Erreurs détaillées
   - Actions sur colis créés

---

## 📖 Documentation Utilisateur

### Pour le Client

**Comment importer des colis en masse ?**

1. Cliquez sur **"Import CSV"** dans le menu ou sur la page Mes Colis
2. Téléchargez le **template CSV** fourni
3. Remplissez votre fichier avec les données des colis
4. Uploadez votre fichier (drag & drop ou sélection)
5. Configurez les options si nécessaire
6. Cliquez sur **"Lancer l'import"**
7. Suivez la progression en temps réel
8. Consultez les résultats et imprimez les bons

**Conseils**
- Utilisez le template fourni pour éviter les erreurs
- Vérifiez votre solde avant l'import
- Les délégations doivent correspondre exactement aux noms du système
- Gardez une copie de votre fichier CSV

---

## 🔐 Sécurité

- ✅ **Authentification** requise (middleware auth + CLIENT role)
- ✅ **Isolation utilisateur** - Chaque client voit uniquement ses imports
- ✅ **Validation fichier** - Type, taille, format
- ✅ **Validation données** - Toutes les données sont validées
- ✅ **Transaction atomique** - Tout réussit ou tout échoue
- ✅ **Nettoyage** - Fichiers temporaires supprimés

---

## 🎉 Résultat Final

La fonctionnalité d'**import CSV en masse** est maintenant :
- ✅ **Accessible** depuis le menu client
- ✅ **Visible** sur la page d'index des colis
- ✅ **Complète** avec toutes les fonctionnalités
- ✅ **Intuitive** et facile à utiliser
- ✅ **Sécurisée** et validée
- ✅ **Responsive** (mobile + desktop)

---

**Auteur** : Cascade AI  
**Date** : 17 Octobre 2025  
**Version** : 1.0
