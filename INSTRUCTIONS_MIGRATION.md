# 🚀 Instructions de Migration - Refonte Packages

## ⚡ Migration Rapide (Recommandé)

### **Option 1 : Script Automatique** 
```bash
# Lancer le script (crée automatiquement une sauvegarde)
MIGRATION_REFONTE.bat
```

### **Option 2 : Manuelle**
```bash
# 1. Sauvegarder
copy database\database.sqlite database\database.sqlite.backup

# 2. Migrer
php artisan migrate

# 3. Vérifier
php artisan migrate:status
```

---

## ✅ Tests de Validation

### **1. Scan Livreur**
```
✅ Scanner RET-2258CB1D
✅ Scanner PAY-ABC123
✅ Scanner PKG-NORMAL123
```

### **2. Interface Paiements**
```
✅ Approuver un paiement
✅ Rejeter un paiement
✅ Créer un colis
✅ Voir détails (toujours visible)
```

---

## 📚 Documentation Complète

Voir : **REFONTE_PACKAGES_RETOURS_PAIEMENTS.md**

---

## 🆘 En Cas de Problème

### **Restaurer la Sauvegarde**
```bash
copy database\database.sqlite.backup database\database.sqlite
```

### **Rollback des Migrations**
```bash
php artisan migrate:rollback --step=3
```

---

## 📝 Résumé des Changements

1. ✅ Table `return_packages` supprimée → tout dans `packages`
2. ✅ Colonnes inutiles supprimées (supplier_data, pickup_*, etc.)
3. ✅ Scanner fonctionne avec RET-XXX, PAY-XXX, PKG-XXX
4. ✅ Interface paiements avec Approuver/Rejeter
5. ✅ Performance améliorée

---

**Durée estimée** : 2-5 minutes  
**Impact** : Base de données + Code  
**Rollback** : Possible
