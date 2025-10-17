# 🧪 Guide de Test - Scanner Livreur

## 📋 Checklist Complète

### ✅ Test 1: Bouton Flottant Supprimé

**Page**: `/deliverer/tournee` (Run Sheet)

**Étapes**:
1. Se connecter en tant que livreur
2. Aller sur la page Run Sheet
3. Scroller jusqu'en bas de la page

**Résultat Attendu**:
- ❌ **PLUS de bouton flottant** en bas à droite
- ✅ Interface propre sans bouton hover

---

### ✅ Test 2: Scan Simple avec Caméra

**Page**: `/deliverer/scan`

#### **2.1 Activation Caméra**

**Étapes**:
1. Aller sur `/deliverer/scan`
2. Cliquer sur l'icône caméra (en haut à droite)
3. Autoriser l'accès à la caméra si demandé

**Résultat Attendu**:
- ✅ Flux vidéo s'affiche
- ✅ Ligne de scan animée visible
- ✅ Badge "🎥 Caméra Active" affiché
- ✅ Bouton caméra passe au vert
- ✅ Statut affiche "📷 Scan actif"

#### **2.2 Scan QR Code**

**Étapes**:
1. Avec la caméra active
2. Présenter un QR code devant la caméra
3. Attendre la détection automatique

**Résultat Attendu**:
- ✅ Son de succès
- ✅ Vibration (sur mobile)
- ✅ Toast "✅ [CODE]"
- ✅ Soumission automatique après 500ms
- ✅ Redirection vers la page de détail du colis

#### **2.3 Scan Code-Barres**

**Étapes**:
1. Avec la caméra active
2. Présenter un code-barres devant la caméra
3. Attendre la détection automatique

**Résultat Attendu**:
- ✅ Son de succès
- ✅ Vibration (sur mobile)
- ✅ Toast "✅ [CODE]"
- ✅ Soumission automatique
- ✅ Redirection vers le détail

#### **2.4 Code Non Trouvé**

**Étapes**:
1. Scanner un code qui n'existe pas
2. Ou saisir manuellement un code invalide

**Résultat Attendu**:
- ❌ Son d'erreur
- ❌ Vibration d'erreur (pattern différent)
- ❌ Toast "❌ [CODE] - Non trouvé"
- ❌ Bordure rouge sur l'input

---

### ✅ Test 3: Scan Simple Manuel (Sans Caméra)

**Page**: `/deliverer/scan`

#### **3.1 Validation Temps Réel**

**Étapes**:
1. Aller sur `/deliverer/scan`
2. Commencer à taper un code existant (ex: `PKG_ON5VUI_1015`)
3. Observer le feedback en temps réel

**Résultat Attendu**:
- ⏳ Pendant la saisie: bordure neutre
- ✅ Code valide: bordure **verte** + message "✅ Colis valide (AVAILABLE) - Assigné"
- ❌ Code invalide: bordure **rouge** + message "❌ Colis non trouvé"

#### **3.2 Soumission avec Enter**

**Étapes**:
1. Saisir un code valide
2. Appuyer sur **Enter**

**Résultat Attendu**:
- ✅ Soumission immédiate
- ✅ Redirection vers le détail du colis

#### **3.3 Soumission avec Bouton**

**Étapes**:
1. Saisir un code valide
2. Cliquer sur **"🔍 Rechercher"**

**Résultat Attendu**:
- ✅ Soumission normale
- ✅ Redirection vers le détail

---

### ✅ Test 4: Scan Multiple

**Page**: `/deliverer/scan/multi`

#### **4.1 Chargement des Colis**

**Étapes**:
1. Aller sur `/deliverer/scan/multi`
2. Observer la console (F12)

**Résultat Attendu**:
- ✅ Message console: "✅ Scanner avec validation DB locale initialisé"
- ✅ Message console: "📦 X colis chargés (Y clés de recherche)"
- ✅ Message console: "💾 Taille mémoire estimée: ZKB"
- ✅ Affichage des exemples de codes chargés

#### **4.2 Scan Multiple avec Caméra**

**Étapes**:
1. Activer la caméra
2. Scanner plusieurs codes (3-5 colis)
3. Vérifier la liste

**Résultat Attendu**:
- ✅ Chaque code s'ajoute à la liste
- ✅ Compteur "📦 X Codes Scannés" se met à jour
- ✅ Chaque item affiche:
  - Code du colis
  - Badge "✓ Assigné" ou "ℹ️ Non assigné"
  - Message avec statut
  - Bouton supprimer (X)

#### **4.3 Saisie Manuelle Multiple**

**Étapes**:
1. Saisir un code dans le champ
2. Observer la validation temps réel
3. Appuyer sur Enter ou cliquer "Ajouter"
4. Répéter 2-3 fois

**Résultat Attendu**:
- ✅ Validation temps réel fonctionne
- ✅ Codes ajoutés à la liste
- ✅ Champ se vide après ajout
- ✅ Pas de doublons acceptés

#### **4.4 Choix de l'Action**

**Étapes**:
1. Observer les 2 boutons d'action
2. Cliquer sur "📦 Ramassage"
3. Puis sur "🚚 Livraison"

**Résultat Attendu**:
- ✅ Bouton actif change de couleur (bleu ou vert)
- ✅ L'action sélectionnée influe sur la validation

#### **4.5 Validation du Lot**

**Étapes**:
1. Scanner/ajouter plusieurs codes
2. Cliquer sur "✅ Valider X colis (Ramassage/Livraison)"
3. Confirmer dans le popup

**Résultat Attendu**:
- ✅ Popup de confirmation
- ✅ Message indique le nombre de colis et l'action
- ✅ Soumission du formulaire
- ✅ Traitement côté serveur

---

### ✅ Test 5: Codes avec Variantes

#### **5.1 Code avec Underscores**

**Codes à tester**:
- `PKG_ON5VUI_1015` (avec underscores)
- `PKGON5VUI1015` (sans underscores)

**Résultat Attendu**:
- ✅ Les **deux variantes** sont trouvées
- ✅ Validation identique

#### **5.2 Code depuis URL de Tracking**

**Codes à tester**:
- `http://127.0.0.1:8000/track/PKG_ON5VUI_1015`
- `/track/PKG_ON5VUI_1015`
- `PKG_ON5VUI_1015` (code direct)

**Résultat Attendu**:
- ✅ Code extrait correctement de l'URL
- ✅ Validation fonctionne pour toutes les formes

#### **5.3 Code en Minuscules**

**Codes à tester**:
- `pkg_on5vui_1015` (minuscules)
- `PKG_ON5VUI_1015` (majuscules)

**Résultat Attendu**:
- ✅ Conversion automatique en majuscules
- ✅ Validation identique

---

### ✅ Test 6: Messages d'Erreur

#### **6.1 Code Trop Court**

**Étapes**:
1. Saisir "AB" (moins de 3 caractères)

**Résultat Attendu**:
- ❌ Message: "Code trop court"
- ❌ Bordure rouge

#### **6.2 Code Déjà Scanné (Multi)**

**Étapes**:
1. En mode multi, scanner un code
2. Essayer de scanner le même code

**Résultat Attendu**:
- ⚠️ Message: "Déjà scanné"
- ⚠️ Toast orange
- ⚠️ Vibration pattern différent

#### **6.3 Statut Invalide**

**Étapes**:
1. Scanner un colis avec statut incompatible
2. Ex: DELIVERED pour ramassage

**Résultat Attendu**:
- ⚠️ Message: "Statut invalide pour ramassage (DELIVERED)"
- ⚠️ Bordure orange

---

### ✅ Test 7: Performance

#### **7.1 Chargement Initial**

**Étapes**:
1. Observer le temps de chargement de la page
2. Vérifier la console

**Résultat Attendu**:
- ✅ Chargement instantané (<100ms)
- ✅ Console affiche le nombre de colis
- ✅ Pas de lag

#### **7.2 Validation Temps Réel**

**Étapes**:
1. Saisir rapidement plusieurs caractères
2. Observer le délai de validation

**Résultat Attendu**:
- ✅ Debounce de 300ms (pas de requête à chaque lettre)
- ✅ Feedback instantané après le délai
- ✅ Pas de lag

#### **7.3 Scan Caméra**

**Étapes**:
1. Scanner rapidement plusieurs codes
2. Observer la réactivité

**Résultat Attendu**:
- ✅ Détection rapide (<1 seconde)
- ✅ Anti-doublon efficace (2 secondes)
- ✅ Feedback immédiat

---

## 🎯 Scénarios Complets

### **Scénario 1: Ramassage de Colis**

1. Livreur reçoit une notification de ramassage
2. Va sur `/deliverer/scan/multi`
3. Sélectionne "📦 Ramassage"
4. Active la caméra
5. Scanne 5 colis à ramasser
6. Valide le lot
7. Colis passent à `PICKED_UP`

### **Scénario 2: Livraison Unique**

1. Livreur arrive chez un client
2. Va sur `/deliverer/scan`
3. Active la caméra
4. Scanne le colis à livrer
5. Redirigé vers la page de détail
6. Confirme la livraison avec signature

### **Scénario 3: Tournée Complète**

1. Livreur consulte sa tournée
2. Pour chaque colis:
   - Scan simple du code
   - Validation de la livraison
   - Passage au suivant

---

## 📊 Tableau de Test

| Test | Page | Résultat | Notes |
|------|------|----------|-------|
| Bouton flottant supprimé | /deliverer/tournee | ⏳ |  |
| Caméra scan simple | /deliverer/scan | ⏳ |  |
| Saisie manuelle simple | /deliverer/scan | ⏳ |  |
| Caméra scan multiple | /deliverer/scan/multi | ⏳ |  |
| Saisie manuelle multiple | /deliverer/scan/multi | ⏳ |  |
| Validation temps réel | /deliverer/scan | ⏳ |  |
| Codes variantes | Both | ⏳ |  |
| Messages d'erreur | Both | ⏳ |  |
| Performance | Both | ⏳ |  |

**Légende**:
- ⏳ À tester
- ✅ Validé
- ❌ Échec
- ⚠️ Partiel

---

## 🐛 Bugs Potentiels à Surveiller

### **1. Caméra**
- [ ] Caméra ne démarre pas (permissions)
- [ ] Flux vidéo gelé
- [ ] Détection lente ou inexistante
- [ ] Caméra ne s'arrête pas proprement

### **2. Validation**
- [ ] Codes valides non reconnus
- [ ] Faux positifs (codes invalides acceptés)
- [ ] Doublons acceptés
- [ ] Variantes non reconnues

### **3. Performance**
- [ ] Chargement lent (>1 seconde)
- [ ] Lag pendant la saisie
- [ ] Mémoire qui augmente
- [ ] Freeze après plusieurs scans

### **4. Interface**
- [ ] Boutons qui ne répondent pas
- [ ] Messages qui ne s'affichent pas
- [ ] Sons qui ne jouent pas
- [ ] Vibrations qui ne fonctionnent pas

---

## 💡 Conseils de Test

### **Pour le Scan Caméra**
1. **Luminosité**: Tester avec différentes lumières
2. **Distance**: 10-30 cm de la caméra
3. **Angle**: Maintenir le code bien droit
4. **Stabilité**: Ne pas bouger pendant le scan

### **Pour les Codes de Test**
Utiliser les codes fournis par l'utilisateur:
- `PKG_ON5VUI_1015`
- `PKG_FGUBCF_1015`
- `PKG_PCKZOE_1015`

### **Pour la Console**
Toujours avoir F12 ouvert pour voir:
- Les logs de chargement
- Les erreurs JavaScript
- Les performances réseau

---

## ✅ Critères de Succès

Pour valider les corrections:

1. ✅ **Bouton flottant**: PLUS visible sur /deliverer/tournee
2. ✅ **Scan simple caméra**: Fonctionne avec QR + codes-barres
3. ✅ **Scan multiple**: Charge les colis et valide localement
4. ✅ **Validation temps réel**: Feedback instantané (<300ms)
5. ✅ **Variantes de codes**: Toutes reconnues
6. ✅ **Performance**: Pas de lag, chargement rapide
7. ✅ **UX**: Sons, vibrations, feedback visuel

---

**Date**: 17 Octobre 2025  
**Version**: 1.0  
**Status**: 🧪 Prêt pour tests
