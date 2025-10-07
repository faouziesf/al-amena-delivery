# ⚡ Test Rapide PWA Livreur - 5 Minutes

## 🎯 Objectif
Vérifier que toutes les corrections PWA fonctionnent correctement en 5 minutes.

---

## ✅ Checklist de Test (5 min)

### 1. Page de Chargement (30 sec)
- [ ] Ouvrir `/deliverer/run-sheet`
- [ ] **Vérifier**: Pas d'erreur console (F12)
- [ ] **Vérifier**: Page charge rapidement (< 3s)
- [ ] **Vérifier**: Pas de "flash" de contenu non stylé

### 2. Toast Notifications (30 sec)
- [ ] Ouvrir console: `showToast('Test', 'success')`
- [ ] **Vérifier**: Toast vert apparaît en bas
- [ ] **Vérifier**: Auto-disparaît après 5s
- [ ] Tester autres types:
  - `showToast('Erreur', 'error')`
  - `showToast('Attention', 'warning')`
  - `showToast('Info', 'info')`

### 3. Indicateur Réseau (30 sec)
- [ ] Mode avion ON
- [ ] **Vérifier**: Badge "🔴 Hors ligne" apparaît en haut à droite
- [ ] Mode avion OFF
- [ ] **Vérifier**: Badge "🟢 En ligne" apparaît puis disparaît

### 4. Service Worker (1 min)
- [ ] F12 → Application → Service Workers
- [ ] **Vérifier**: `sw.js` avec status "activated and is running"
- [ ] F12 → Application → Manifest
- [ ] **Vérifier**: Informations manifest affichées
- [ ] **Vérifier**: Icônes listées (minimum 192x192 et 512x512)

### 5. Haptic Feedback Mobile (30 sec)
**Sur téléphone uniquement:**
- [ ] Cliquer bouton "Livrer" ou "Accepter"
- [ ] **Vérifier**: Vibration ressentie
- [ ] Console: `haptic('success')`
- [ ] **Vérifier**: Vibration pattern

### 6. Pull-to-Refresh Mobile (30 sec)
**Sur téléphone uniquement:**
- [ ] Tirer page vers le bas
- [ ] **Vérifier**: Indicateur de chargement bleu
- [ ] **Vérifier**: Page se recharge

### 7. Copy to Clipboard (30 sec)
- [ ] Cliquer sur un code colis (ex: PKG_ABC123)
- [ ] **Vérifier**: Toast "Copié dans le presse-papiers"
- [ ] Coller ailleurs (Ctrl+V)
- [ ] **Vérifier**: Code collé correctement

### 8. Mode Offline (1 min)
- [ ] Mode avion ON
- [ ] Essayer action (livraison, scan, etc.)
- [ ] **Vérifier**: Toast "Action mise en queue (hors ligne)"
- [ ] Mode avion OFF
- [ ] **Vérifier**: Toast "Synchronisation en cours..."
- [ ] **Vérifier**: Toast "Synchronisation terminée"

### 9. Installation PWA (1 min)

**Android Chrome:**
- [ ] Menu → "Installer l'application"
- [ ] **Vérifier**: Option présente
- [ ] Installer
- [ ] **Vérifier**: Icône sur écran d'accueil
- [ ] Ouvrir depuis icône
- [ ] **Vérifier**: Ouvre en mode app (pas de barre d'adresse)

**iOS Safari:**
- [ ] Partager → "Sur l'écran d'accueil"
- [ ] **Vérifier**: Option présente
- [ ] Ajouter
- [ ] **Vérifier**: Icône sur écran d'accueil

---

## 🐛 Si Problème

### Toast ne s'affiche pas
```javascript
// Console:
console.log(typeof showToast);
// Doit afficher: "function"
// Si "undefined": pwa-manager.js pas chargé
```

### Service Worker erreur
```javascript
// Console:
navigator.serviceWorker.getRegistrations().then(console.log);
// Doit afficher: Array avec registration
// Si vide: sw.js pas enregistré
```

### Indicateur réseau ne change pas
```javascript
// Console:
console.log(pwaManager.isOnline);
// Doit afficher: true ou false
// Si undefined: pwaManager pas initialisé
```

### Haptic ne vibre pas
- Vérifier sur vrai téléphone (pas émulateur)
- Vérifier paramètres téléphone (vibration activée)
- Console: `navigator.vibrate([200])`

---

## ✅ Résultat Attendu

**Tous les tests passent**: 🟢 PRODUCTION READY

**Quelques tests échouent**: 🟡 Vérifier logs et corriger

**Beaucoup de tests échouent**: 🔴 Vérifier déploiement

---

## 📝 Rapport de Test

Date: _______________

Testeur: _______________

### Résultats:
- [ ] Tous les tests passent ✅
- [ ] Quelques problèmes mineurs ⚠️
- [ ] Problèmes majeurs ❌

### Notes:
_________________________________
_________________________________
_________________________________

### Signature: _______________
