@echo off
echo ========================================
echo TEST PICKUPS DISPONIBLES
echo ========================================
echo.

echo [1/4] Verification pickups en BDD...
php artisan tinker --execute="echo 'Total pickups: ' . App\Models\PickupRequest::count(); echo PHP_EOL; echo 'Pickups disponibles: ' . App\Models\PickupRequest::whereIn('status', ['pending', 'awaiting_assignment'])->whereNull('assigned_deliverer_id')->count();"

echo.
echo [2/4] Verification gouvernorats livreurs...
php artisan tinker --execute="$deliverers = App\Models\User::where('role', 'DELIVERER')->get(); foreach($deliverers as $d) { echo $d->name . ': ' . json_encode($d->deliverer_gouvernorats) . PHP_EOL; }"

echo.
echo [3/4] Liste des pickups disponibles...
php artisan tinker --execute="$pickups = App\Models\PickupRequest::whereIn('status', ['pending', 'awaiting_assignment'])->whereNull('assigned_deliverer_id')->get(); foreach($pickups as $p) { echo 'ID: ' . $p->id . ' - Status: ' . $p->status . ' - Delegation: ' . ($p->delegation->governorate ?? 'N/A') . PHP_EOL; }"

echo.
echo [4/4] Test API pour livreur ID=2...
php artisan tinker --execute="$user = App\Models\User::find(2); if($user) { echo 'Livreur: ' . $user->name . PHP_EOL; echo 'Gouvernorats: ' . json_encode($user->deliverer_gouvernorats) . PHP_EOL; } else { echo 'Livreur ID=2 introuvable' . PHP_EOL; }"

echo.
echo ========================================
echo TEST TERMINE
echo ========================================
pause
