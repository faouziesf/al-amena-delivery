# 🧪 SCRIPT DE TEST - CRÉER DES NOTIFICATIONS

**Utilisation** : Copier-coller ces commandes dans `php artisan tinker`

---

## 🚀 **DÉMARRAGE RAPIDE**

```bash
# Ouvrir Tinker
php artisan tinker
```

---

## 📝 **CRÉER DES NOTIFICATIONS DE TEST**

### **1. Pour Tous les Superviseurs**

```php
use App\Services\NotificationService;
use App\Models\User;

$notif = app(NotificationService::class);
$supervisors = User::where('role', 'SUPERVISOR')->pluck('id')->toArray();

// Notification URGENT
$notif->createForUsers(
    $supervisors,
    'SYSTEM_ALERT',
    '🔴 Alerte Système',
    'Nombre élevé de colis en attente de validation',
    'URGENT',
    ['count' => 150]
);

// Notification HIGH
$notif->createForUsers(
    $supervisors,
    'PAYMENT_PENDING',
    '💰 Paiements en Attente',
    'Vous avez 45 paiements à valider',
    'HIGH',
    ['count' => 45]
);

// Notification NORMAL
$notif->createForUsers(
    $supervisors,
    'REPORT_READY',
    '📊 Rapport Mensuel Disponible',
    'Le rapport du mois d\'octobre est prêt',
    'NORMAL'
);

echo "✅ Notifications créées pour " . count($supervisors) . " superviseur(s)\n";
```

---

### **2. Pour Tous les Livreurs**

```php
$deliverers = User::where('role', 'DELIVERER')->pluck('id')->toArray();

// Notification URGENT - Nouveau colis
$notif->createForUsers(
    $deliverers,
    'PACKAGE_ASSIGNED',
    '📦 Nouveau Colis Urgent',
    'Un nouveau colis URGENT vous a été assigné',
    'URGENT',
    ['package_id' => 123]
);

// Notification HIGH - Tournée modifiée
$notif->createForUsers(
    $deliverers,
    'RUN_SHEET_UPDATED',
    '🚚 Tournée Mise à Jour',
    'Votre tournée a été modifiée - 12 nouveaux colis',
    'HIGH',
    ['new_packages_count' => 12]
);

// Notification NORMAL - Paiement reçu
$notif->createForUsers(
    $deliverers,
    'PAYMENT_RECEIVED',
    '💵 Paiement Reçu',
    'Votre paiement de 450 DT a été crédité',
    'NORMAL',
    ['amount' => 450]
);

// Notification LOW - Information
$notif->createForUsers(
    $deliverers,
    'INFO',
    'ℹ️ Nouvelle Fonctionnalité',
    'Découvrez le nouveau scanner multi-colis',
    'LOW'
);

echo "✅ Notifications créées pour " . count($deliverers) . " livreur(s)\n";
```

---

### **3. Pour Tous les Chefs Dépôt**

```php
$depotManagers = User::where('role', 'DEPOT_MANAGER')->pluck('id')->toArray();

// Notification URGENT
$notif->createForUsers(
    $depotManagers,
    'PACKAGES_AWAITING',
    '⚠️ Colis en Attente Urgente',
    '25 colis en attente de validation depuis plus de 24h',
    'URGENT',
    ['count' => 25]
);

// Notification HIGH
$notif->createForUsers(
    $depotManagers,
    'DELIVERER_ABSENT',
    '👤 Livreur Absent',
    'Livreur Ahmed absent - Réassigner ses 18 colis',
    'HIGH',
    ['deliverer_name' => 'Ahmed', 'packages_count' => 18]
);

// Notification NORMAL
$notif->createForUsers(
    $depotManagers,
    'DAILY_REPORT',
    '📈 Rapport Quotidien',
    '152 colis livrés aujourd\'hui (+12% vs hier)',
    'NORMAL',
    ['delivered' => 152, 'increase' => 12]
);

echo "✅ Notifications créées pour " . count($depotManagers) . " chef(s) dépôt\n";
```

---

### **4. Pour Tous les Commerciaux**

```php
$commercials = User::where('role', 'COMMERCIAL')->pluck('id')->toArray();

// Notification URGENT
$notif->createForUsers(
    $commercials,
    'COMPLAINT_URGENT',
    '🆘 Réclamation Urgente',
    'Client VIP mécontent - Intervention immédiate requise',
    'URGENT',
    ['client_name' => 'Société ABC', 'complaint_id' => 45]
);

// Notification HIGH
$notif->createForUsers(
    $commercials,
    'TICKET_PENDING',
    '🎫 Tickets en Attente',
    'Vous avez 8 tickets en attente de réponse',
    'HIGH',
    ['count' => 8]
);

// Notification NORMAL
$notif->createForUsers(
    $commercials,
    'NEW_CLIENT',
    '👥 Nouveau Client',
    'Nouveau client enregistré : Entreprise XYZ',
    'NORMAL',
    ['client_name' => 'Entreprise XYZ']
);

echo "✅ Notifications créées pour " . count($commercials) . " commercial(aux)\n";
```

---

### **5. Pour Tous les Clients**

```php
$clients = User::where('role', 'CLIENT')->limit(10)->pluck('id')->toArray();

// Notification URGENT
$notif->createForUsers(
    $clients,
    'PACKAGE_DELAYED',
    '⏰ Colis en Retard',
    'Votre colis #AL12345 est en retard - Livraison reportée',
    'URGENT',
    ['package_code' => 'AL12345']
);

// Notification HIGH
$notif->createForUsers(
    $clients,
    'PACKAGE_OUT_FOR_DELIVERY',
    '🚚 Colis en Livraison',
    'Votre colis #AL12346 est en cours de livraison',
    'HIGH',
    ['package_code' => 'AL12346']
);

// Notification NORMAL
$notif->createForUsers(
    $clients,
    'PACKAGE_DELIVERED',
    '✅ Colis Livré',
    'Votre colis #AL12347 a été livré avec succès',
    'NORMAL',
    ['package_code' => 'AL12347']
);

echo "✅ Notifications créées pour " . count($clients) . " client(s)\n";
```

---

## 🔧 **CRÉER DES ACTION LOGS DE TEST**

```php
use App\Services\ActionLogService;

$log = app(ActionLogService::class);

// Log création package
$log->logCreated('Package', 123, [
    'package_code' => 'AL12345',
    'sender_id' => 1,
    'receiver_name' => 'Test Client'
]);

// Log modification package
$log->logUpdated('Package', 123, 
    ['status' => 'PENDING'],
    ['status' => 'DELIVERED']
);

// Log changement statut
$log->logStatusChanged('Package', 123, 'PENDING', 'DELIVERED');

// Log assignation
$log->logAssignment('Package', 123, null, 5);

// Log connexion
$user = User::first();
$log->logLogin($user);

// Log transaction
$log->logFinancialTransaction('PAYMENT', 450.50, 1, [
    'method' => 'cash',
    'package_id' => 123
]);

echo "✅ Action logs de test créés\n";
```

---

## 📊 **VÉRIFIER LES RÉSULTATS**

### **Compter les Notifications**

```php
use App\Models\Notification;

// Total notifications
echo "Total notifications : " . Notification::count() . "\n";

// Par rôle
$byRole = Notification::join('users', 'notifications.user_id', '=', 'users.id')
    ->selectRaw('users.role, COUNT(*) as count')
    ->groupBy('users.role')
    ->get();

foreach ($byRole as $stat) {
    echo "{$stat->role}: {$stat->count} notifications\n";
}

// Non lues
echo "Non lues : " . Notification::whereNull('read_at')->count() . "\n";

// Par priorité
$byPriority = Notification::selectRaw('priority, COUNT(*) as count')
    ->groupBy('priority')
    ->get();

foreach ($byPriority as $stat) {
    echo "{$stat->priority}: {$stat->count} notifications\n";
}
```

### **Compter les Action Logs**

```php
use App\Models\ActionLog;

// Total action logs
echo "Total action logs : " . ActionLog::count() . "\n";

// Par type d'action
$byAction = ActionLog::selectRaw('action_type, COUNT(*) as count')
    ->groupBy('action_type')
    ->orderByDesc('count')
    ->get();

foreach ($byAction as $stat) {
    echo "{$stat->action_type}: {$stat->count} logs\n";
}

// Logs aujourd'hui
echo "Logs aujourd'hui : " . ActionLog::whereDate('created_at', today())->count() . "\n";
```

---

## 🗑️ **NETTOYER LES DONNÉES DE TEST**

### **Supprimer Toutes les Notifications de Test**

```php
// Supprimer toutes les notifications
Notification::truncate();
echo "✅ Toutes les notifications supprimées\n";

// OU supprimer seulement les anciennes
Notification::where('created_at', '<', now()->subDays(7))->delete();
echo "✅ Notifications de +7 jours supprimées\n";

// OU supprimer par type
Notification::where('type', 'SYSTEM_TEST')->delete();
echo "✅ Notifications de test supprimées\n";
```

### **Supprimer Tous les Action Logs de Test**

```php
// Supprimer tous les logs
ActionLog::truncate();
echo "✅ Tous les action logs supprimés\n";

// OU supprimer seulement les anciens
ActionLog::where('created_at', '<', now()->subMonths(1))->delete();
echo "✅ Logs de +1 mois supprimés\n";
```

---

## 🎯 **SCRIPT COMPLET DE TEST**

**Copier-coller tout ce bloc dans Tinker** :

```php
use App\Services\NotificationService;
use App\Services\ActionLogService;
use App\Models\User;

$notif = app(NotificationService::class);
$log = app(ActionLogService::class);

echo "🚀 Création de notifications de test...\n\n";

// 1. Superviseurs
$supervisors = User::where('role', 'SUPERVISOR')->pluck('id')->toArray();
if (count($supervisors) > 0) {
    $notif->createForUsers($supervisors, 'SYSTEM_ALERT', '🔴 Alerte Système', 'Test alerte urgente', 'URGENT');
    $notif->createForUsers($supervisors, 'PAYMENT_PENDING', '💰 Paiements', 'Test paiements', 'HIGH');
    $notif->createForUsers($supervisors, 'REPORT_READY', '📊 Rapport', 'Test rapport', 'NORMAL');
    echo "✅ {count($supervisors)} superviseur(s) : 3 notifications\n";
}

// 2. Livreurs
$deliverers = User::where('role', 'DELIVERER')->pluck('id')->toArray();
if (count($deliverers) > 0) {
    $notif->createForUsers($deliverers, 'PACKAGE_ASSIGNED', '📦 Nouveau Colis', 'Test colis urgent', 'URGENT');
    $notif->createForUsers($deliverers, 'RUN_SHEET_UPDATED', '🚚 Tournée', 'Test tournée', 'HIGH');
    $notif->createForUsers($deliverers, 'PAYMENT_RECEIVED', '💵 Paiement', 'Test paiement', 'NORMAL');
    echo "✅ " . count($deliverers) . " livreur(s) : 3 notifications\n";
}

// 3. Chefs dépôt
$depotManagers = User::where('role', 'DEPOT_MANAGER')->pluck('id')->toArray();
if (count($depotManagers) > 0) {
    $notif->createForUsers($depotManagers, 'PACKAGES_AWAITING', '⚠️ Colis en Attente', 'Test colis', 'URGENT');
    $notif->createForUsers($depotManagers, 'DELIVERER_ABSENT', '👤 Livreur Absent', 'Test absent', 'HIGH');
    $notif->createForUsers($depotManagers, 'DAILY_REPORT', '📈 Rapport', 'Test rapport', 'NORMAL');
    echo "✅ " . count($depotManagers) . " chef(s) dépôt : 3 notifications\n";
}

// 4. Action logs
$user = User::first();
if ($user) {
    $log->log('TEST_ACTION', 'System', 1, null, ['test' => true]);
    $log->logCreated('Package', 999, ['package_code' => 'TEST123']);
    $log->logStatusChanged('Package', 999, 'PENDING', 'DELIVERED');
    echo "✅ 3 action logs créés\n";
}

echo "\n✅ Test terminé ! Vérifiez vos notifications.\n";
```

---

## 🔗 **URLS À TESTER**

**Après avoir créé les notifications de test, visitez** :

```
Superviseur :
→ http://localhost:8000/supervisor/notifications
→ http://localhost:8000/supervisor/action-logs

Livreur :
→ http://localhost:8000/deliverer/notifications

Chef Dépôt :
→ http://localhost:8000/depot-manager/notifications

Commercial :
→ http://localhost:8000/commercial/notifications

Client :
→ http://localhost:8000/client/notifications
```

---

## ✅ **CHECKLIST DE VÉRIFICATION**

Après avoir créé les notifications de test, vérifiez :

- [ ] Badge rouge avec nombre apparaît dans le menu
- [ ] Clic sur "Notifications" affiche la page
- [ ] Statistiques s'affichent correctement
- [ ] Liste des notifications visible
- [ ] Badge "Nouveau" sur notifications non lues
- [ ] Badge priorité avec bonnes couleurs
- [ ] "Marquer comme lu" fonctionne
- [ ] "Tout marquer lu" fonctionne
- [ ] "Supprimer" fonctionne
- [ ] Filtres fonctionnent
- [ ] Pagination fonctionne
- [ ] Action logs visibles (superviseur)

---

**Version** : 1.0  
**Date** : 21 Octobre 2025  
**Auteur** : Cascade AI
