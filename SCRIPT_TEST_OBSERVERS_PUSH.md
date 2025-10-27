# 🧪 SCRIPT DE TEST - OBSERVERS + EMAIL + PUSH

**Objectif** : Tester le système complet d'enregistrement automatique et notifications

---

## 📋 **PRÉPARATION**

### **1. Exécuter la Migration**

```bash
php artisan migrate
```

### **2. Configurer FCM (Optionnel)**

```env
# .env
FCM_SERVER_KEY=your_firebase_cloud_messaging_server_key
```

### **3. Vérifier les Services**

```bash
php artisan tinker
```

```php
// Vérifier que les services sont disponibles
$actionLog = app(App\Services\ActionLogService::class);
$notif = app(App\Services\NotificationService::class);
$push = app(App\Services\PushNotificationService::class);

echo "✅ Services disponibles\n";
```

---

## 🧪 **TESTS DES OBSERVERS**

### **Test 1 : PackageObserver - Création de Colis**

```bash
php artisan tinker
```

```php
use App\Models\Package;
use App\Models\ActionLog;
use App\Models\Notification;

// Compter avant
$logsBefore = ActionLog::count();
$notifsBefore = Notification::count();

// Créer un colis
$package = Package::create([
    'package_code' => 'TEST-' . time(),
    'sender_id' => 1, // Remplacer par un vrai ID
    'receiver_name' => 'Test Receiver',
    'receiver_phone' => '12345678',
    'receiver_address' => 'Test Address',
    'receiver_gouvernorat_id' => 1,
    'receiver_delegation_id' => 1,
    'status' => 'CREATED',
    'delivery_type' => 'HOME',
    'payment_type' => 'COD',
    'cod_amount' => 50.00
]);

echo "📦 Colis créé: {$package->package_code}\n";

// Vérifier les logs
$logsAfter = ActionLog::count();
echo "Action Logs: {$logsBefore} → {$logsAfter} (+" . ($logsAfter - $logsBefore) . ")\n";

// Vérifier les notifications
$notifsAfter = Notification::count();
echo "Notifications: {$notifsBefore} → {$notifsAfter} (+" . ($notifsAfter - $notifsBefore) . ")\n";

// Voir le dernier log
$lastLog = ActionLog::latest()->first();
echo "Dernier log: {$lastLog->action_type} - {$lastLog->target_type} #{$lastLog->target_id}\n";

// Voir la dernière notification
$lastNotif = Notification::latest()->first();
echo "Dernière notif: {$lastNotif->type} - {$lastNotif->title}\n";

echo "\n✅ Test PackageObserver->created() OK\n";
```

---

### **Test 2 : PackageObserver - Changement de Statut**

```php
use App\Models\Package;

$package = Package::latest()->first();
$oldStatus = $package->status;

// Compter avant
$logsBefore = ActionLog::count();
$notifsBefore = Notification::count();

// Changer le statut
$package->update(['status' => 'DELIVERED']);

echo "📦 Statut changé: {$oldStatus} → DELIVERED\n";

// Vérifier
$logsAfter = ActionLog::count();
$notifsAfter = Notification::count();

echo "Action Logs: +{$logsAfter - $logsBefore}\n";
echo "Notifications: +{$notifsAfter - $notifsBefore}\n";

// Voir le log de changement
$statusLog = ActionLog::where('action_type', 'STATUS_CHANGED')
    ->where('target_id', $package->id)
    ->latest()
    ->first();

if ($statusLog) {
    echo "✅ Log changement statut trouvé\n";
    echo "Old: " . json_decode($statusLog->old_value, true)['status'] ?? 'N/A' . "\n";
    echo "New: " . json_decode($statusLog->new_value, true)['status'] ?? 'N/A' . "\n";
}

echo "\n✅ Test PackageObserver->updated() OK\n";
```

---

### **Test 3 : PackageObserver - Assignation Livreur**

```php
use App\Models\Package;
use App\Models\User;

// Trouver un livreur
$deliverer = User::where('role', 'DELIVERER')->first();

if (!$deliverer) {
    echo "⚠️ Aucun livreur trouvé, créer un utilisateur livreur d'abord\n";
    exit;
}

$package = Package::latest()->first();

// Compter avant
$notifsBefore = Notification::count();

// Assigner le livreur
$package->update(['assigned_deliverer_id' => $deliverer->id]);

echo "📦 Livreur assigné: {$deliverer->name}\n";

// Vérifier notifications
$notifsAfter = Notification::count();
echo "Notifications créées: +" . ($notifsAfter - $notifsBefore) . "\n";

// Vérifier que le livreur a reçu une notification
$delivererNotif = Notification::where('user_id', $deliverer->id)
    ->where('type', 'PACKAGE_ASSIGNED')
    ->latest()
    ->first();

if ($delivererNotif) {
    echo "✅ Notification livreur: {$delivererNotif->title}\n";
}

echo "\n✅ Test assignation livreur OK\n";
```

---

### **Test 4 : UserObserver - Création Utilisateur**

```php
use App\Models\User;
use Illuminate\Support\Str;

// Compter avant
$logsBefore = ActionLog::count();
$notifsBefore = Notification::count();

// Créer un utilisateur
$user = User::create([
    'name' => 'Test User ' . rand(100, 999),
    'email' => 'test' . time() . '@test.com',
    'password' => bcrypt('password'),
    'role' => 'CLIENT',
    'status' => 'PENDING',
    'phone' => '12345678'
]);

echo "👤 Utilisateur créé: {$user->name}\n";

// Vérifier
$logsAfter = ActionLog::count();
$notifsAfter = Notification::count();

echo "Action Logs: +" . ($logsAfter - $logsBefore) . "\n";
echo "Notifications: +" . ($notifsAfter - $notifsBefore) . "\n";

// Vérifier notification bienvenue
$welcomeNotif = Notification::where('user_id', $user->id)
    ->where('type', 'USER_CREATED')
    ->first();

if ($welcomeNotif) {
    echo "✅ Notification bienvenue: {$welcomeNotif->title}\n";
}

echo "\n✅ Test UserObserver->created() OK\n";
```

---

### **Test 5 : UserObserver - Activation Compte**

```php
use App\Models\User;

$user = User::where('status', 'PENDING')->latest()->first();

if (!$user) {
    echo "⚠️ Aucun utilisateur PENDING trouvé\n";
    exit;
}

// Compter avant
$notifsBefore = Notification::count();

// Activer le compte
$user->update(['status' => 'ACTIVE']);

echo "👤 Compte activé: {$user->name}\n";

// Vérifier notification
$notifsAfter = Notification::count();
echo "Notifications: +" . ($notifsAfter - $notifsBefore) . "\n";

$activationNotif = Notification::where('user_id', $user->id)
    ->where('type', 'USER_ACTIVATED')
    ->latest()
    ->first();

if ($activationNotif) {
    echo "✅ Notification activation: {$activationNotif->title}\n";
    echo "Priorité: {$activationNotif->priority}\n";
}

echo "\n✅ Test activation compte OK\n";
```

---

### **Test 6 : TicketObserver - Création Ticket**

```php
use App\Models\Ticket;
use App\Models\User;

// Compter avant
$logsBefore = ActionLog::count();
$notifsBefore = Notification::count();

// Créer un ticket
$ticket = Ticket::create([
    'user_id' => 1, // Remplacer par un vrai ID
    'subject' => 'Test Ticket URGENT',
    'message' => 'Ceci est un test',
    'priority' => 'URGENT',
    'status' => 'OPEN'
]);

echo "🎫 Ticket créé: #{$ticket->id} - {$ticket->subject}\n";

// Vérifier
$logsAfter = ActionLog::count();
$notifsAfter = Notification::count();

echo "Action Logs: +" . ($logsAfter - $logsBefore) . "\n";
echo "Notifications: +" . ($notifsAfter - $notifsBefore) . "\n";

// Vérifier que les commerciaux ont été notifiés
$commercials = User::where('role', 'COMMERCIAL')->pluck('id');
$commercialNotifs = Notification::where('type', 'TICKET_CREATED')
    ->whereIn('user_id', $commercials)
    ->count();

echo "Notifications commerciaux: {$commercialNotifs}\n";

echo "\n✅ Test TicketObserver->created() OK\n";
```

---

## 📧 **TESTS EMAIL**

### **Test 7 : Email Changement Statut**

```php
use Illuminate\Support\Facades\Mail;

Mail::send('emails.notifications.package-status-changed', [
    'userName' => 'Ahmed Test',
    'packageCode' => 'AL12345',
    'oldStatusLabel' => 'En Transit',
    'newStatusLabel' => 'Livré',
    'newStatus' => 'DELIVERED',
    'trackingUrl' => 'http://localhost:8000/client/packages/1'
], function($message) {
    $message->to('test@example.com')
            ->subject('Test - Votre colis a été livré');
});

echo "✅ Email changement statut envoyé\n";
```

---

### **Test 8 : Email Assignation**

```php
use Illuminate\Support\Facades\Mail;

Mail::send('emails.notifications.package-assigned', [
    'delivererName' => 'Mohamed Test',
    'packageCode' => 'AL12345',
    'senderName' => 'Client A',
    'receiverName' => 'Client B',
    'deliveryAddress' => '123 Avenue Bourguiba',
    'gouvernorat' => 'Tunis',
    'phoneNumber' => '21234567',
    'codAmount' => 75.50,
    'packageUrl' => 'http://localhost:8000/deliverer/packages/1'
], function($message) {
    $message->to('test@example.com')
            ->subject('Test - Nouveau colis assigné');
});

echo "✅ Email assignation envoyé\n";
```

---

### **Test 9 : Email Générique**

```php
use Illuminate\Support\Facades\Mail;

Mail::send('emails.notifications.generic', [
    'title' => 'Compte Activé',
    'userName' => 'Test User',
    'message' => 'Votre compte a été activé avec succès. Vous pouvez maintenant vous connecter.',
    'icon' => '✅',
    'details' => [
        'Rôle' => 'CLIENT',
        'Date activation' => date('d/m/Y H:i')
    ],
    'actionUrl' => 'http://localhost:8000/login',
    'actionText' => 'Se Connecter'
], function($message) {
    $message->to('test@example.com')
            ->subject('Test - Compte activé');
});

echo "✅ Email générique envoyé\n";
```

---

## 📱 **TESTS PUSH NOTIFICATIONS**

### **Test 10 : Push à un Utilisateur**

```php
use App\Services\PushNotificationService;

$push = app(PushNotificationService::class);

// D'abord, ajouter un token FCM de test
$user = User::find(1);
$user->update(['fcm_token' => 'test_fcm_token_here']);

// Envoyer push
$result = $push->sendToUser(
    1,
    '📦 Nouveau Colis',
    'Un colis vous a été assigné',
    ['package_id' => 123]
);

if ($result) {
    echo "✅ Push notification envoyée\n";
} else {
    echo "⚠️ Push notification échouée (vérifier FCM_SERVER_KEY)\n";
}
```

---

### **Test 11 : Push à un Rôle**

```php
use App\Services\PushNotificationService;

$push = app(PushNotificationService::class);

$result = $push->sendToRole(
    'DELIVERER',
    '🚚 Nouvelle Tournée',
    'Consultez votre planning',
    ['action' => 'open_schedule']
);

if ($result) {
    echo "✅ Push envoyée à tous les livreurs\n";
} else {
    echo "⚠️ Aucun livreur avec FCM token ou erreur\n";
}
```

---

## 📊 **VÉRIFICATION GLOBALE**

### **Test 12 : Statistiques Complètes**

```php
use App\Models\ActionLog;
use App\Models\Notification;
use App\Models\PackageStatusHistory;

// Action Logs
$totalLogs = ActionLog::count();
$logsToday = ActionLog::whereDate('created_at', today())->count();

echo "═══════════════════════════════════════\n";
echo "📊 STATISTIQUES SYSTÈME\n";
echo "═══════════════════════════════════════\n\n";

echo "Action Logs:\n";
echo "  Total: {$totalLogs}\n";
echo "  Aujourd'hui: {$logsToday}\n\n";

// Par type d'action
$logsByType = ActionLog::selectRaw('action_type, count(*) as count')
    ->groupBy('action_type')
    ->orderBy('count', 'desc')
    ->get();

echo "Top actions:\n";
foreach($logsByType->take(5) as $log) {
    echo "  - {$log->action_type}: {$log->count}\n";
}

// Notifications
$totalNotifs = Notification::count();
$notifsToday = Notification::whereDate('created_at', today())->count();
$unreadNotifs = Notification::where('is_read', false)->count();

echo "\nNotifications:\n";
echo "  Total: {$totalNotifs}\n";
echo "  Aujourd'hui: {$notifsToday}\n";
echo "  Non lues: {$unreadNotifs}\n\n";

// Par type
$notifsByType = Notification::selectRaw('type, count(*) as count')
    ->groupBy('type')
    ->orderBy('count', 'desc')
    ->get();

echo "Top notifications:\n";
foreach($notifsByType->take(5) as $notif) {
    echo "  - {$notif->type}: {$notif->count}\n";
}

// Historique statuts
$totalHistory = PackageStatusHistory::count();
$historyToday = PackageStatusHistory::whereDate('created_at', today())->count();

echo "\nHistorique Statuts:\n";
echo "  Total: {$totalHistory}\n";
echo "  Aujourd'hui: {$historyToday}\n";

echo "\n═══════════════════════════════════════\n";
echo "✅ Système fonctionnel !\n";
echo "═══════════════════════════════════════\n";
```

---

## 🎯 **SCRIPT COMPLET DE TEST**

Copier-coller ce script dans `php artisan tinker` :

```php
echo "🧪 DÉBUT DES TESTS OBSERVERS + NOTIFICATIONS\n\n";

use App\Models\Package;
use App\Models\User;
use App\Models\Ticket;
use App\Models\ActionLog;
use App\Models\Notification;

// 1. Test création colis
echo "1️⃣ Test création colis...\n";
$pkg = Package::create([
    'package_code' => 'TEST-' . time(),
    'sender_id' => 1,
    'receiver_name' => 'Test',
    'receiver_phone' => '12345678',
    'receiver_address' => 'Test',
    'receiver_gouvernorat_id' => 1,
    'receiver_delegation_id' => 1,
    'status' => 'CREATED',
    'delivery_type' => 'HOME',
    'payment_type' => 'COD',
    'cod_amount' => 50
]);
$log1 = ActionLog::where('target_id', $pkg->id)->where('action_type', 'CREATED')->exists();
echo $log1 ? "  ✅ Log créé\n" : "  ❌ Log manquant\n";

// 2. Test changement statut
echo "\n2️⃣ Test changement statut...\n";
$pkg->update(['status' => 'DELIVERED']);
$log2 = ActionLog::where('target_id', $pkg->id)->where('action_type', 'STATUS_CHANGED')->exists();
echo $log2 ? "  ✅ Log changement statut\n" : "  ❌ Log manquant\n";

// 3. Test assignation
echo "\n3️⃣ Test assignation livreur...\n";
$deliverer = User::where('role', 'DELIVERER')->first();
if ($deliverer) {
    $pkg->update(['assigned_deliverer_id' => $deliverer->id]);
    $notif = Notification::where('user_id', $deliverer->id)->where('type', 'PACKAGE_ASSIGNED')->exists();
    echo $notif ? "  ✅ Notification livreur\n" : "  ❌ Notification manquante\n";
} else {
    echo "  ⚠️ Pas de livreur\n";
}

echo "\n✅ TESTS TERMINÉS\n";
```

---

## ✅ **RÉSULTAT ATTENDU**

Si tout fonctionne correctement :

```
✅ Chaque création de colis génère:
   - 1 ActionLog
   - 1 Notification au client
   - 1 PackageStatusHistory

✅ Chaque changement de statut génère:
   - 1 ActionLog
   - 1 Notification au client
   - 1 PackageStatusHistory

✅ Chaque assignation génère:
   - 1 ActionLog
   - 1 Notification au livreur
   
✅ Chaque création utilisateur génère:
   - 1 ActionLog
   - 1 Notification bienvenue

✅ Chaque création ticket génère:
   - 1 ActionLog
   - X Notifications (1 par commercial actif)
```

---

**Script prêt à l'emploi !** 🚀
