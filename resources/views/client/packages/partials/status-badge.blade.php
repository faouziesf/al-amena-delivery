<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
    {{ match($status) {
        'CREATED' => 'bg-gray-100 text-gray-800',
        'AVAILABLE' => 'bg-blue-100 text-blue-800',
        'PICKED_UP' => 'bg-indigo-100 text-indigo-800',
        'AT_DEPOT' => 'bg-yellow-100 text-yellow-800',
        'IN_TRANSIT' => 'bg-purple-100 text-purple-800',
        'DELIVERED' => 'bg-green-100 text-green-800',
        'PAID' => 'bg-emerald-100 text-emerald-800',
        'RETURNED' => 'bg-red-100 text-red-800',
        'CANCELLED' => 'bg-gray-100 text-gray-600',
        default => 'bg-gray-100 text-gray-800',
    } }}">
    {{ match($status) {
        'CREATED' => '🆕 Créé',
        'AVAILABLE' => '📋 Disponible',
        'PICKED_UP' => '🚚 Collecté',
        'AT_DEPOT' => '🏭 Au Dépôt',
        'IN_TRANSIT' => '🚛 En Transit',
        'DELIVERED' => '✅ Livré',
        'PAID' => '💰 Payé',
        'RETURNED' => '↩️ Retourné',
        'CANCELLED' => '❌ Annulé',
        default => '📦 ' . $status,
    } }}
</span>
