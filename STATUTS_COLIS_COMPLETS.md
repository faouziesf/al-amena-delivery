# 📋 Liste Complète des Statuts de Colis

## 🎯 Tous les Statuts Disponibles

Voici la liste complète de tous les statuts utilisés dans le système:

### 1. **CREATED** (Créé)
- Colis vient d'être créé par le client
- Pas encore disponible pour les livreurs
- En attente de traitement

### 2. **AVAILABLE** (Disponible)
- Colis disponible pour les livreurs
- Peut être accepté par n'importe quel livreur
- Prêt pour ramassage

### 3. **ACCEPTED** (Accepté)
- Colis accepté par un livreur
- Assigné à un livreur spécifique
- En attente de ramassage

### 4. **PICKED_UP** (Ramassé)
- Colis ramassé par le livreur
- En route vers le destinataire
- En possession du livreur

### 5. **OUT_FOR_DELIVERY** (En cours de livraison)
- Colis en cours de livraison
- Livreur est en route vers le destinataire
- Alternative à PICKED_UP pour tracking précis

### 6. **DELIVERED** (Livré)
- Colis livré au destinataire
- Transaction réussie
- En attente de paiement au client

### 7. **PAID** (Payé)
- Colis livré ET payé au client
- Transaction complète et fermée
- Statut final

### 8. **VERIFIED** (Vérifié/À retourner)
- Colis refusé par le destinataire
- En attente de retour à l'expéditeur
- Livreur doit le retourner

### 9. **RETURNED** (Retourné)
- Colis retourné à l'expéditeur
- Refusé par destinataire
- Transaction annulée

### 10. **CANCELLED** (Annulé)
- Colis annulé par le client ou système
- Plus en circulation
- Transaction abandonnée

### 11. **REFUSED** (Refusé)
- Refusé par le destinataire
- Similar à VERIFIED
- En attente de traitement

### 12. **UNAVAILABLE** (Indisponible)
- Destinataire temporairement indisponible
- Nouvelle tentative nécessaire
- Toujours en possession du livreur

### 13. **AT_DEPOT** (Au dépôt)
- Colis arrivé au dépôt
- Scanné par le chef de dépôt
- En attente de traitement ou dispatching

### 14. **DELIVERED_PAID** (Livré et payé)
- Alternative à PAID
- Livraison confirmée avec paiement
- Statut final combiné

---

## 📊 Classification pour Scanner Dépôt

### ✅ **STATUTS À ACCEPTER** (Scanner Dépôt)

Merci de m'indiquer pour chaque statut ci-dessous s'il doit être **ACCEPTÉ** ou **REFUSÉ** dans le scanner dépôt:

1. **CREATED** - Colis créé, pas encore traité
   - Votre décision: ___________

2. **AVAILABLE** - Disponible pour livreurs
   - Votre décision: ___________

3. **ACCEPTED** - Accepté par un livreur
   - Votre décision: ___________

4. **PICKED_UP** - Ramassé par livreur
   - Votre décision: ___________

5. **OUT_FOR_DELIVERY** - En cours de livraison
   - Votre décision: ___________

6. **DELIVERED** - Livré au destinataire
   - Votre décision: ___________

7. **PAID** - Livré et payé
   - Votre décision: ___________

8. **VERIFIED** - À retourner (refusé)
   - Votre décision: ___________

9. **RETURNED** - Retourné à expéditeur
   - Votre décision: ___________

10. **CANCELLED** - Annulé
    - Votre décision: ___________

11. **REFUSED** - Refusé par destinataire
    - Votre décision: ___________

12. **UNAVAILABLE** - Destinataire indisponible
    - Votre décision: ___________

13. **AT_DEPOT** - Déjà au dépôt
    - Logique actuelle: Accepté si dépôt différent, refusé si même dépôt
    - Votre confirmation: ___________

14. **DELIVERED_PAID** - Livré et payé (alternatif)
    - Votre décision: ___________

---

## 🎯 Instructions

**Merci de remplir pour chaque statut:**
- ✅ **ACCEPTER** - Si le colis avec ce statut DOIT pouvoir être scanné au dépôt
- ❌ **REFUSER** - Si le colis avec ce statut NE DOIT PAS être scanné au dépôt
- 📝 **Message personnalisé** - Le message à afficher si refusé

**Exemple de réponse:**
```
1. CREATED - ✅ ACCEPTER (colis arrive au dépôt pour traitement)
2. AVAILABLE - ❌ REFUSER - Message: "Déjà disponible pour livraison"
3. ACCEPTED - ✅ ACCEPTER (livreur ramène au dépôt)
...
```

---

## 💡 Contexte d'Utilisation Scanner Dépôt

Le scanner dépôt est utilisé pour:
- Réception des colis au dépôt
- Transfert entre dépôts
- Traçabilité des mouvements
- Mise à jour statut → AT_DEPOT (Nom Chef)

**Après votre réponse, je configurerai le système selon vos règles métier exactes.**
