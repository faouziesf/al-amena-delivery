<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Vérification des Données ===" . PHP_EOL . PHP_EOL;

// Users
$usersCount = \App\Models\User::count();
$clientsCount = \App\Models\User::where('role', 'CLIENT')->count();
$deliverersCount = \App\Models\User::where('role', 'DELIVERER')->count();
echo "👥 Utilisateurs:" . PHP_EOL;
echo "   Total: $usersCount" . PHP_EOL;
echo "   Clients: $clientsCount" . PHP_EOL;
echo "   Livreurs: $deliverersCount" . PHP_EOL . PHP_EOL;

// Packages
$packagesCount = \App\Models\Package::count();
echo "📦 Colis: $packagesCount" . PHP_EOL . PHP_EOL;

// Tickets
$ticketsCount = \App\Models\Ticket::count();
echo "🎫 Tickets: $ticketsCount" . PHP_EOL . PHP_EOL;

// Fixed Charges
$chargesCount = \App\Models\FixedCharge::count();
$chargesTotal = \App\Models\FixedCharge::where('is_active', true)->sum('monthly_equivalent');
echo "💰 Charges Fixes:" . PHP_EOL;
echo "   Total: $chargesCount" . PHP_EOL;
echo "   Total mensuel: " . number_format($chargesTotal, 3) . " DT" . PHP_EOL . PHP_EOL;

// Vehicles
$vehiclesCount = \App\Models\Vehicle::count();
echo "🚗 Véhicules: $vehiclesCount" . PHP_EOL . PHP_EOL;

// Action Logs
try {
    $logsCount = \App\Models\ActionLog::count();
    echo "📋 Action Logs: $logsCount" . PHP_EOL . PHP_EOL;
} catch (\Exception $e) {
    echo "📋 Action Logs: Table n'existe pas encore" . PHP_EOL . PHP_EOL;
}

echo "=== Fin ===" . PHP_EOL;
