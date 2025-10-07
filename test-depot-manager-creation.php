<?php
/**
 * Script de test pour la création de chef dépôt
 * À exécuter avec: php artisan tinker
 * Ensuite copier-coller le contenu de testDepotManagerCreation()
 */

// Test de création d'un chef dépôt
function testDepotManagerCreation() {
    $data = [
        'name' => 'Test Chef Depot',
        'email' => 'chef.depot.test@example.com',
        'phone' => '+21698765432',
        'password' => 'password123',
        'role' => 'DEPOT_MANAGER',
        'account_status' => 'ACTIVE',
        'assigned_gouvernorats' => ['Tunis', 'Ariana', 'Ben Arous']
    ];

    echo "=== Test Création Chef Dépôt ===\n";
    echo "Données à envoyer:\n";
    print_r($data);
    
    try {
        // Créer l'utilisateur
        $user = \App\Models\User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => \Hash::make($data['password']),
            'role' => $data['role'],
            'account_status' => $data['account_status'],
            'assigned_gouvernorats' => json_encode($data['assigned_gouvernorats']),
            'is_depot_manager' => true,
            'verified_at' => now(),
            'email_verified_at' => now(),
        ]);
        
        echo "\n✅ Chef Dépôt créé avec succès!\n";
        echo "ID: " . $user->id . "\n";
        echo "Nom: " . $user->name . "\n";
        echo "Email: " . $user->email . "\n";
        echo "Rôle: " . $user->role . "\n";
        echo "Gouvernorats: " . $user->assigned_gouvernorats . "\n";
        
        return $user;
    } catch (\Exception $e) {
        echo "\n❌ Erreur lors de la création:\n";
        echo $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
        return null;
    }
}

// Instructions d'utilisation
echo "=== Instructions ===\n";
echo "1. Ouvrir le terminal dans le dossier du projet\n";
echo "2. Lancer: php artisan tinker\n";
echo "3. Copier-coller la fonction testDepotManagerCreation() et l'appeler\n";
echo "4. Ou utiliser directement:\n";
echo "\$user = \\App\\Models\\User::create([\n";
echo "    'name' => 'Chef Depot Test',\n";
echo "    'email' => 'depot@test.com',\n";
echo "    'phone' => '+21698765432',\n";
echo "    'password' => \\Hash::make('password123'),\n";
echo "    'role' => 'DEPOT_MANAGER',\n";
echo "    'account_status' => 'ACTIVE',\n";
echo "    'assigned_gouvernorats' => json_encode(['Tunis', 'Ariana']),\n";
echo "    'is_depot_manager' => true,\n";
echo "    'verified_at' => now(),\n";
echo "    'email_verified_at' => now(),\n";
echo "]);\n";
echo "\n";
