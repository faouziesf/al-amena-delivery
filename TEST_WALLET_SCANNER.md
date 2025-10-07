# ⚡ Test Rapide Wallet & Scanner - 5 Minutes

## 🎯 Test Wallet Production (2 min)

### 1. Accéder à la Page
```
URL: /deliverer/wallet
Compte: livreur connecté
```

### 2. Vérifications Visuelles
- [ ] Page charge en < 3 secondes
- [ ] Montant total affiché en gros
- [ ] Pas de données fictives (AL2025001, Mohamed Salah, etc.)
- [ ] Date du jour correcte
- [ ] Statistiques (Livrés, COD, Moyen)

### 3. Test Fonctionnel
```javascript
// Ouvrir Console (F12)
// Vérifier qu'aucune erreur
// Doit voir: GET /api/deliverer/wallet/cod-today → 200 OK
```

### 4. Test Données
- [ ] Total = 0 DT si aucun COD aujourd'hui ✅
- [ ] Total > 0 DT si colis livrés avec COD ✅
- [ ] Liste vide si aucune transaction
- [ ] Transactions affichées si présentes

### 5. Test Actions
- [ ] Cliquer bouton refresh (🔄) → Recharge données
- [ ] Sur mobile: Tirer vers le bas → Pull-to-refresh
- [ ] "Charger plus" si beaucoup de transactions
- [ ] "Demander vidage" désactivé si 0 DT

---

## 📱 Test Scanner Mobile (3 min)

### Test 1: Sur Desktop (30 sec)
```
URL: /deliverer/scanner
```

1. Cliquer "Activer Caméra"
2. Autoriser permission si demandée
3. Caméra s'allume → ✅
4. Cliquer "Manuel" → Formulaire saisie
5. Taper code → Valider

### Test 2: Sur Téléphone ⭐ IMPORTANT (2 min)

**Étape A: Mode Caméra**
1. Ouvrir `/deliverer/scanner` sur téléphone
2. Cliquer "Activer Caméra"
3. ✅ Permission demandée clairement
4. Autoriser
5. ✅ Caméra arrière s'active
6. ✅ Overlay de scan visible (carrés aux coins)
7. ✅ Ligne animée qui descend
8. Scanner un QR code test
9. ✅ Vibration au scan
10. ✅ Résultat affiché

**Étape B: Switch Caméra**
- Cliquer bouton 🔄
- ✅ Caméra change (avant/arrière)

**Étape C: Mode Manuel**
- Cliquer "Manuel" en haut
- ✅ Formulaire saisie apparaît
- Taper "PKG_TEST123"
- Appuyer Entrée ou Valider
- ✅ Vérification effectuée

**Étape D: Gestion Erreurs**
- Refuser permission → ✅ Message + bouton "Autoriser"
- Code invalide → ✅ "Colis introuvable"
- Pas de caméra → ✅ Basculer en manuel

### Test 3: Vérifier API (30 sec)

```javascript
// Console navigateur (F12)

// Test 1: Vérifier scan
fetch('/api/deliverer/scan/verify', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ code: 'PKG_TEST' })
}).then(r => r.json()).then(console.log);

// Doit retourner:
// { success: false, message: "Colis introuvable" } → ✅ API fonctionne
```

---

## ✅ Checklist Globale

### Wallet
- [ ] Page charge sans erreur
- [ ] Données réelles affichées
- [ ] Aucune donnée fictive (AL2025xxx)
- [ ] Total correct
- [ ] Refresh fonctionne
- [ ] Bouton vidage réactif

### Scanner Desktop
- [ ] Caméra s'ouvre
- [ ] Permission gérée
- [ ] Mode manuel fonctionne

### Scanner Mobile ⭐
- [ ] Caméra arrière s'active
- [ ] Permission claire
- [ ] Overlay visible
- [ ] Scan fonctionne
- [ ] Vibration au scan
- [ ] Switch caméra OK
- [ ] Mode manuel OK
- [ ] Erreurs gérées

### API
- [ ] Routes existent
- [ ] Authentification OK
- [ ] Réponses JSON correctes

---

## 🐛 Si Problème

### Wallet Vide Alors Que J'ai Livré

```sql
-- Vérifier en DB
SELECT 
    id, package_code, cod_amount, 
    status, delivered_at, assigned_deliverer_id
FROM packages 
WHERE assigned_deliverer_id = [ID_LIVREUR]
AND status = 'DELIVERED'
AND cod_amount > 0
AND DATE(delivered_at) = CURDATE();
```

Si vide → Normal, aucun COD aujourd'hui  
Si résultats → Vérifier API appel

### Scanner Caméra Noire

1. **Vérifier HTTPS**
```
http://localhost → ❌ Caméra bloquée
https://localhost → ✅ OK
```

2. **Vérifier Permission**
- Chrome: Icône 🔒 → Paramètres site
- Autoriser caméra

3. **Test Rapide**
```javascript
navigator.mediaDevices.getUserMedia({ video: true })
  .then(() => alert('✅ Caméra OK'))
  .catch(err => alert('❌ ' + err.message));
```

### Code Non Reconnu

```javascript
// Vérifier colis existe
fetch('/api/deliverer/scan/verify', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ code: 'PKG_VRAI_CODE' })
}).then(r => r.json()).then(console.log);
```

---

## 📊 Résultat Attendu

### ✅ TOUS LES TESTS PASSENT
→ **PRODUCTION READY** 🎉

### ⚠️ QUELQUES ÉCHECS
→ Vérifier logs et corriger

### ❌ BEAUCOUP D'ÉCHECS
→ Vérifier déploiement, HTTPS, permissions

---

## 📝 Rapport de Test

**Date**: _______________  
**Testeur**: _______________  
**Environnement**: [ ] Desktop [ ] Mobile

### Wallet
- [ ] ✅ Fonctionne parfaitement
- [ ] ⚠️ Fonctionne avec problèmes mineurs
- [ ] ❌ Ne fonctionne pas

**Notes**: _________________________________

### Scanner Mobile
- [ ] ✅ Fonctionne parfaitement sur téléphone
- [ ] ⚠️ Fonctionne avec problèmes mineurs
- [ ] ❌ Ne fonctionne pas sur téléphone

**Notes**: _________________________________

### Conclusion Générale
- [ ] ✅ READY FOR PRODUCTION
- [ ] ⚠️ Corrections mineures requises
- [ ] ❌ Corrections majeures requises

**Signature**: _______________
