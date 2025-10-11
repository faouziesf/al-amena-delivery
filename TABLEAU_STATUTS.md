# 📊 Tableau des Statuts - Scanner Dépôt

## 🎯 Liste Complète des Statuts

| # | Statut | Description | Cycle | Votre Décision |
|---|--------|-------------|-------|----------------|
| 1 | **CREATED** | Colis créé par client | Début | ✅ ❌ ? |
| 2 | **AVAILABLE** | Disponible pour livreurs | Début | ✅ ❌ ? |
| 3 | **ACCEPTED** | Accepté par un livreur | En cours | ✅ ❌ ? |
| 4 | **PICKED_UP** | Ramassé par livreur | En cours | ✅ ❌ ? |
| 5 | **OUT_FOR_DELIVERY** | En cours de livraison | En cours | ✅ ❌ ? |
| 6 | **DELIVERED** | Livré au destinataire | Fin succès | ✅ ❌ ? |
| 7 | **PAID** | Livré et payé au client | Fin succès | ✅ ❌ ? |
| 8 | **VERIFIED** | À retourner (refusé) | Retour | ✅ ❌ ? |
| 9 | **RETURNED** | Retourné à expéditeur | Fin retour | ✅ ❌ ? |
| 10 | **CANCELLED** | Annulé | Fin échec | ✅ ❌ ? |
| 11 | **REFUSED** | Refusé par destinataire | Retour | ✅ ❌ ? |
| 12 | **UNAVAILABLE** | Destinataire indisponible | En cours | ✅ ❌ ? |
| 13 | **AT_DEPOT** | Au dépôt (actuel) | Dépôt | Logique spéciale |
| 14 | **DELIVERED_PAID** | Livré+payé (alternatif) | Fin succès | ✅ ❌ ? |

---

## 💬 Questions pour Classification

### Question 1: Colis Début de Cycle
**CREATED** et **AVAILABLE** - Ces colis arrivent au dépôt pour la première fois?
- ✅ OUI → ACCEPTER (réception initiale)
- ❌ NON → REFUSER

**Votre réponse:** ___________

---

### Question 2: Colis En Cours de Livraison
**ACCEPTED**, **PICKED_UP**, **OUT_FOR_DELIVERY** - Livreur ramène au dépôt (problème, retour temporaire)?
- ✅ OUI → ACCEPTER (retour temporaire)
- ❌ NON → REFUSER (déjà en livraison)

**Votre réponse:** ___________

---

### Question 3: Colis Livrés
**DELIVERED**, **PAID**, **DELIVERED_PAID** - Colis déjà livrés reviennent au dépôt?
- ✅ OUI → ACCEPTER (cas rare)
- ❌ NON → REFUSER (déjà terminé)

**Votre réponse:** ___________

---

### Question 4: Colis Retours
**VERIFIED**, **REFUSED**, **RETURNED** - Colis refusés arrivent au dépôt?
- ✅ OUI → ACCEPTER (gestion retours)
- ❌ NON → REFUSER (déjà en retour)

**Votre réponse:** ___________

---

### Question 5: Colis Problèmes
**CANCELLED**, **UNAVAILABLE** - Ces colis passent par le dépôt?
- ✅ OUI → ACCEPTER
- ❌ NON → REFUSER

**Votre réponse:** ___________

---

### Question 6: AT_DEPOT
**Logique actuelle:**
- Même dépôt → ❌ REFUSER "Déjà au dépôt Omar"
- Dépôt différent → ✅ ACCEPTER (transfert)

**Cette logique est correcte?** ___________

---

## 🎯 Format de Réponse Souhaité

Merci de répondre ainsi:

```
1. CREATED - ✅ ACCEPTER
2. AVAILABLE - ❌ REFUSER - "Déjà disponible"
3. ACCEPTED - ✅ ACCEPTER
4. PICKED_UP - ✅ ACCEPTER
5. OUT_FOR_DELIVERY - ✅ ACCEPTER
6. DELIVERED - ❌ REFUSER - "Déjà livré"
7. PAID - ❌ REFUSER - "Déjà payé"
8. VERIFIED - ✅ ACCEPTER
9. RETURNED - ❌ REFUSER - "Déjà retourné"
10. CANCELLED - ❌ REFUSER - "Annulé"
11. REFUSED - ✅ ACCEPTER
12. UNAVAILABLE - ✅ ACCEPTER
13. AT_DEPOT - Logique actuelle OK ✅
14. DELIVERED_PAID - ❌ REFUSER - "Déjà livré et payé"
```

**Une fois votre réponse reçue, je configurerai immédiatement le système avec la logique exacte que vous souhaitez!** 🎯
