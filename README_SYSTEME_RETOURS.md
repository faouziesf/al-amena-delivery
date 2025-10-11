# 📦 Système de Retours - Guide de Démarrage Rapide

> **Status:** ✅ Système complet, testé et prêt pour la production

---

## 🚀 Démarrage Rapide (5 Minutes)

### 1️⃣ Exécuter les Migrations
```bash
php artisan migrate
```

### 2️⃣ Configurer le Scheduler
Ajouter au crontab (Linux/Mac) ou Task Scheduler (Windows):
```bash
* * * * * cd /path/to/al-amena-delivery && php artisan schedule:run >> /dev/null 2>&1
```

### 3️⃣ Tester le Système
```bash
php test_complete_return_system.php
```

**Résultat attendu:**
```
✅✅✅ TOUS LES TESTS SONT PASSÉS AVEC SUCCÈS ! ✅✅✅
```

### 4️⃣ Accéder aux Interfaces

**🧑‍💼 Commercial:**
```
http://votre-domaine.com/commercial/packages/{id}
```

**🏭 Chef Dépôt:**
```
http://votre-domaine.com/depot/returns
```

**👤 Client:**
```
http://votre-domaine.com/client/returns
```

---

## 📚 Documentation Complète

| Document | Description |
|----------|-------------|
| [SYSTEME_RETOURS_FINAL_DOCUMENTATION.md](SYSTEME_RETOURS_FINAL_DOCUMENTATION.md) | Documentation technique complète |
| [ROUTES_SYSTEME_RETOURS.md](ROUTES_SYSTEME_RETOURS.md) | Guide des routes et API |
| Ce fichier | Guide de démarrage rapide |

---

## 🔄 Workflow en Images

```
┌─────────────────────────────────────────────────────────────┐
│                   LIVRAISON IMPOSSIBLE                       │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
                  ┌───────────────────────┐
                  │   3 Tentatives        │
                  │   Échouées            │
                  └───────────────────────┘
                              │
                              ▼
                  ┌───────────────────────┐
                  │  AWAITING_RETURN      │
                  │  (Attente 48h)        │
                  └───────────────────────┘
                              │
                   ┌──────────┴──────────┐
                   │                     │
                   ▼                     ▼
        ┌──────────────────┐   ┌────────────────┐
        │  Job Auto (48h)  │   │  Commercial    │
        │  → RETURN_IN_    │   │  4ème Tentative│
        │    PROGRESS      │   │  → AT_DEPOT    │
        └──────────────────┘   └────────────────┘
                   │
                   ▼
        ┌──────────────────────┐
        │  Chef Dépôt Scanne   │
        │  Crée Colis Retour   │
        └──────────────────────┘
                   │
                   ▼
        ┌──────────────────────┐
        │  Livraison Retour    │
        │  → RETURNED_TO_      │
        │    CLIENT            │
        └──────────────────────┘
                   │
        ┌──────────┴──────────────┐
        │                         │
        ▼                         ▼
┌──────────────┐         ┌────────────────┐
│ Client       │         │ Auto (48h)     │
│ Confirme     │         │ → RETURN_      │
│ → CONFIRMED  │         │   CONFIRMED    │
└──────────────┘         └────────────────┘
        │
        ▼
┌──────────────┐
│ Client       │
│ Signale      │
│ → ISSUE      │
└──────────────┘
```

---

## 🎯 Cas d'Usage Principaux

### 1. Livreur: 3 Tentatives Échouées

**Scénario:** Le destinataire est injoignable

**Actions du Livreur:**
1. Marque le colis comme `UNAVAILABLE` (3 fois)
2. Le système passe automatiquement à `AWAITING_RETURN`

**Automatique:**
- Après 48h → `RETURN_IN_PROGRESS`

### 2. Commercial: Donner une 4ème Chance

**Scénario:** Le client appelle et dit que le destinataire est maintenant disponible

**Actions du Commercial:**
1. Accède au colis (`/commercial/packages/{id}`)
2. Clique sur "Lancer 4ème Tentative"
3. Le colis retourne à `AT_DEPOT` avec 2 tentatives restantes

### 3. Chef Dépôt: Préparer les Retours

**Scénario:** 10 colis sont en `RETURN_IN_PROGRESS`

**Actions:**
1. Accède au dashboard (`/depot/returns`)
2. Scanne le QR code avec son téléphone
3. Scanne les 10 colis
4. Valide → Création de 10 colis retours
5. Imprime les bordereaux

### 4. Client: Confirmer la Réception

**Scénario:** Le client reçoit son colis retourné

**Actions:**
1. Accède à "Mes Retours" (`/client/returns`)
2. Voit le colis avec compte à rebours (48h)
3. Clique "Confirmer Réception"
4. Statut → `RETURN_CONFIRMED`

**OU signaler un problème:**
1. Clique "Signaler un Problème"
2. Décrit le problème (colis endommagé, etc.)
3. Création automatique d'une réclamation
4. Statut → `RETURN_ISSUE`

---

## ⚙️ Configuration

### Scheduler (Jobs Automatiques)

Les 2 jobs s'exécutent **toutes les heures**:

1. **ProcessAwaitingReturnsJob**
   - Trouve: `AWAITING_RETURN` + `awaiting_return_since` > 48h
   - Action: Passe à `RETURN_IN_PROGRESS`

2. **ProcessReturnedPackagesJob**
   - Trouve: `RETURNED_TO_CLIENT` + `returned_to_client_at` > 48h
   - Action: Passe à `RETURN_CONFIRMED`

**Vérifier le scheduler:**
```bash
php artisan schedule:list
```

**Test manuel:**
```bash
php artisan schedule:run
```

### Variables d'Environnement

Aucune variable spéciale requise. Le système utilise la configuration Laravel standard.

---

## 🧪 Tests

### Test Complet (Recommandé)
```bash
php test_complete_return_system.php
```

**Ce qui est testé:**
- ✅ Migrations
- ✅ Création de données
- ✅ Workflow 3 tentatives → AWAITING_RETURN
- ✅ Job auto 48h → RETURN_IN_PROGRESS
- ✅ Création colis retour
- ✅ Livraison retour
- ✅ Auto-confirmation 48h

### Test des Jobs Uniquement
```bash
php test_return_jobs.php
```

### Test Manuel via Interface

**1. Créer un colis de test:**
```bash
php artisan tinker
```
```php
$client = User::where('role', 'CLIENT')->first();
$package = Package::create([
    'sender_id' => $client->id,
    'package_code' => 'TEST-' . strtoupper(substr(md5(uniqid()), 0, 6)),
    'tracking_number' => 'TRK-' . time(),
    'status' => 'AT_DEPOT',
    'cod_amount' => 100,
    'delivery_type' => 'standard',
    'recipient_data' => ['name' => 'Test', 'phone' => '12345678', 'address' => 'Test', 'city' => 'Tunis'],
    'unavailable_attempts' => 3,
    'awaiting_return_since' => now()->subHours(50),
]);
```

**2. Tester l'interface dépôt:**
- Accéder: `/depot/returns`
- Scanner avec mobile
- Valider

**3. Vérifier les logs:**
```bash
tail -f storage/logs/laravel.log
```

---

## 📊 Monitoring

### Logs Importants

**Localisation:** `storage/logs/laravel.log`

**Événements loggés:**
- ✅ Passage automatique de statut (jobs)
- ✅ Création de colis retour
- ✅ Validation client
- ✅ Changement manuel par commercial
- ✅ Signalement de problème

**Exemple de log:**
```
[2025-10-11 12:34:56] production.INFO: Colis passé en RETURN_IN_PROGRESS
{"package_id":123,"package_code":"PKG-ABC123"}

[2025-10-11 12:40:15] production.INFO: Colis retour créé
{"return_package_id":456,"return_code":"RET-XYZ789","depot_manager":"Chef Dépôt"}

[2025-10-11 14:20:30] production.INFO: Retour confirmé par le client
{"package_id":123,"client_id":4}
```

### Commandes Utiles

**Voir les logs en temps réel:**
```bash
php artisan pail
```

**Compter les colis par statut:**
```bash
php artisan tinker
```
```php
Package::groupBy('status')->selectRaw('status, count(*) as count')->get();
```

**Voir les retours en cours:**
```php
ReturnPackage::with('originalPackage')->where('status', 'AT_DEPOT')->get();
```

---

## 🔧 Dépannage

### Problème: Les jobs ne s'exécutent pas

**Solution:**
1. Vérifier le crontab:
```bash
crontab -l
```

2. Tester manuellement:
```bash
php artisan schedule:run
```

3. Vérifier les logs:
```bash
tail -f storage/logs/laravel.log
```

### Problème: Erreur "Session expirée"

**Cause:** Cache Laravel vidé ou expiré

**Solution:**
1. Redémarrer une nouvelle session
2. Vérifier la configuration du cache:
```bash
php artisan config:cache
```

### Problème: QR Code ne fonctionne pas

**Solutions:**
1. Vérifier que ngrok est actif (pour test local)
2. Vérifier l'URL dans le QR code
3. Vérifier les permissions caméra sur mobile

### Problème: Migrations échouent

**Solution:**
```bash
# Voir le statut
php artisan migrate:status

# Rollback et re-migrer
php artisan migrate:refresh

# OU force
php artisan migrate --force
```

---

## 📱 Support Mobile

### Configuration Ngrok (Développement)

**1. Installer ngrok:**
```bash
npm install -g ngrok
# OU télécharger depuis ngrok.com
```

**2. Démarrer le tunnel:**
```bash
ngrok http 8000
```

**3. Utiliser l'URL ngrok:**
```
https://abc123.ngrok.io/depot/returns
```

### Production

En production, utilisez HTTPS avec un domaine valide:
```
https://votre-domaine.com/depot/returns
```

---

## 🔐 Sécurité

### Permissions par Rôle

| Rôle | Permissions |
|------|-------------|
| **COMMERCIAL** | ✅ Lancer 4ème tentative<br>✅ Changement manuel statut<br>❌ Scan dépôt<br>❌ Validation client |
| **CHEF_DEPOT** | ✅ Scan retours<br>✅ Créer colis retours<br>✅ Impression bordereaux<br>❌ Changement statut |
| **CLIENT** | ✅ Confirmer réception<br>✅ Signaler problème<br>❌ Scan<br>❌ Changement statut |

### Validations Automatiques

- ✅ Vérification ownership (sender_id)
- ✅ Vérification statut avant action
- ✅ Raison obligatoire pour changement manuel
- ✅ Empêche modification statuts critiques (PAID)

---

## 📈 Statistiques

### Métriques Importantes

**Calculées automatiquement:**
- Nombre de colis en retour
- Taux de confirmation client
- Temps moyen de traitement
- Problèmes signalés

**Accès:**
- Via l'interface de gestion (`/depot/returns/manage`)
- Via les logs
- Via requêtes SQL directes

---

## 🎉 Fonctionnalités Bonus

### Impression de Bordereaux
- QR code automatique sur le bordereau
- Format optimisé pour impression
- Détection auto du navigateur

### Scan Mobile Avancé
- Vibration au scan
- Feedback visuel (flash vert)
- Detection session terminée
- Mode offline (à venir)

### Traçabilité Complète
- Historique complet de chaque colis
- Logs horodatés
- Réclamations liées
- Rapports exportables

---

## 🆘 Support

### En Cas de Problème

1. **Consulter les logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Vérifier les routes:**
   ```bash
   php artisan route:list | grep returns
   ```

3. **Tester les jobs:**
   ```bash
   php test_return_jobs.php
   ```

4. **Vider les caches:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   ```

### Contacts

- **Documentation:** Voir les fichiers `*_DOCUMENTATION.md`
- **Tests:** Exécuter `test_complete_return_system.php`
- **Logs:** `storage/logs/laravel.log`

---

## ✨ Prochaines Améliorations (Optionnelles)

- [ ] Notifications Email automatiques
- [ ] Notifications Push mobile
- [ ] Dashboard analytics avancé
- [ ] Export Excel des retours
- [ ] API externe pour intégrations
- [ ] Mode offline complet (PWA)

---

**Version:** 1.0
**Date:** 11 Octobre 2025
**Status:** ✅ Production Ready
**Tests:** ✅ 100% Passés

🚀 **Bon déploiement!**
