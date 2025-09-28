@extends('layouts.depot-manager')

@section('title', 'Tous les Colis')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-indigo-50">
    <!-- Header moderne -->
    <div class="bg-white shadow-lg border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl flex items-center justify-center text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Tous les Colis</h1>
                        <p class="text-slate-500 text-sm">Consultation globale de tous les colis du système</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <button onclick="refreshPackages()"
                            class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Actualiser
                    </button>
                    <a href="{{ route('depot-manager.packages.returns-exchanges') }}"
                       class="inline-flex items-center px-4 py-2 bg-orange-100 hover:bg-orange-200 text-orange-700 rounded-xl transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Retours & Échanges
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Dashboard de statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-blue-100 text-sm font-medium">Total Colis</p>
                            <p class="text-white text-2xl font-bold">{{ number_format($stats['total']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-yellow-100 text-sm font-medium">En Cours</p>
                            <p class="text-white text-2xl font-bold">{{ number_format($stats['in_progress']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-green-100 text-sm font-medium">Livrés Aujourd'hui</p>
                            <p class="text-white text-2xl font-bold">{{ number_format($stats['delivered_today']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-red-100 text-sm font-medium">Urgents</p>
                            <p class="text-white text-2xl font-bold">{{ number_format($stats['urgent']) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6 mb-8">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"/>
                    </svg>
                    <span class="text-slate-600 font-medium">Filtres :</span>
                </div>

                <form method="GET" class="flex flex-wrap items-center gap-4">
                    <select name="status" class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">Tous les statuts</option>
                        <option value="CREATED" {{ request('status') == 'CREATED' ? 'selected' : '' }}>Créé</option>
                        <option value="AVAILABLE" {{ request('status') == 'AVAILABLE' ? 'selected' : '' }}>Disponible</option>
                        <option value="ACCEPTED" {{ request('status') == 'ACCEPTED' ? 'selected' : '' }}>Accepté</option>
                        <option value="PICKED_UP" {{ request('status') == 'PICKED_UP' ? 'selected' : '' }}>Récupéré</option>
                        <option value="DELIVERED" {{ request('status') == 'DELIVERED' ? 'selected' : '' }}>Livré</option>
                        <option value="RETURNED" {{ request('status') == 'RETURNED' ? 'selected' : '' }}>Retourné</option>
                        <option value="CANCELLED" {{ request('status') == 'CANCELLED' ? 'selected' : '' }}>Annulé</option>
                    </select>

                    <select name="gouvernorat" class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">Tous les gouvernorats</option>
                        <option value="Tunis" {{ request('gouvernorat') == 'Tunis' ? 'selected' : '' }}>Tunis</option>
                        <option value="Ariana" {{ request('gouvernorat') == 'Ariana' ? 'selected' : '' }}>Ariana</option>
                        <option value="Ben Arous" {{ request('gouvernorat') == 'Ben Arous' ? 'selected' : '' }}>Ben Arous</option>
                        <option value="Manouba" {{ request('gouvernorat') == 'Manouba' ? 'selected' : '' }}>Manouba</option>
                        <option value="Nabeul" {{ request('gouvernorat') == 'Nabeul' ? 'selected' : '' }}>Nabeul</option>
                        <option value="Zaghouan" {{ request('gouvernorat') == 'Zaghouan' ? 'selected' : '' }}>Zaghouan</option>
                        <option value="Bizerte" {{ request('gouvernorat') == 'Bizerte' ? 'selected' : '' }}>Bizerte</option>
                        <option value="Béja" {{ request('gouvernorat') == 'Béja' ? 'selected' : '' }}>Béja</option>
                        <option value="Jendouba" {{ request('gouvernorat') == 'Jendouba' ? 'selected' : '' }}>Jendouba</option>
                        <option value="Kef" {{ request('gouvernorat') == 'Kef' ? 'selected' : '' }}>Kef</option>
                        <option value="Siliana" {{ request('gouvernorat') == 'Siliana' ? 'selected' : '' }}>Siliana</option>
                        <option value="Kairouan" {{ request('gouvernorat') == 'Kairouan' ? 'selected' : '' }}>Kairouan</option>
                        <option value="Kasserine" {{ request('gouvernorat') == 'Kasserine' ? 'selected' : '' }}>Kasserine</option>
                        <option value="Sidi Bouzid" {{ request('gouvernorat') == 'Sidi Bouzid' ? 'selected' : '' }}>Sidi Bouzid</option>
                        <option value="Sousse" {{ request('gouvernorat') == 'Sousse' ? 'selected' : '' }}>Sousse</option>
                        <option value="Monastir" {{ request('gouvernorat') == 'Monastir' ? 'selected' : '' }}>Monastir</option>
                        <option value="Mahdia" {{ request('gouvernorat') == 'Mahdia' ? 'selected' : '' }}>Mahdia</option>
                        <option value="Sfax" {{ request('gouvernorat') == 'Sfax' ? 'selected' : '' }}>Sfax</option>
                        <option value="Gafsa" {{ request('gouvernorat') == 'Gafsa' ? 'selected' : '' }}>Gafsa</option>
                        <option value="Tozeur" {{ request('gouvernorat') == 'Tozeur' ? 'selected' : '' }}>Tozeur</option>
                        <option value="Kebili" {{ request('gouvernorat') == 'Kebili' ? 'selected' : '' }}>Kebili</option>
                        <option value="Gabès" {{ request('gouvernorat') == 'Gabès' ? 'selected' : '' }}>Gabès</option>
                        <option value="Médenine" {{ request('gouvernorat') == 'Médenine' ? 'selected' : '' }}>Médenine</option>
                        <option value="Tataouine" {{ request('gouvernorat') == 'Tataouine' ? 'selected' : '' }}>Tataouine</option>
                    </select>

                    <select name="deliverer_id" class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">Tous les livreurs</option>
                        @foreach($allDeliverers as $deliverer)
                            <option value="{{ $deliverer->id }}" {{ request('deliverer_id') == $deliverer->id ? 'selected' : '' }}>
                                {{ $deliverer->first_name }} {{ $deliverer->last_name }}
                            </option>
                        @endforeach
                    </select>

                    <input type="text" name="search" placeholder="Rechercher..."
                           value="{{ request('search') }}"
                           class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">

                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">

                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">

                    <button type="submit"
                            class="inline-flex items-center px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Rechercher
                    </button>

                    @if(request()->hasAny(['status', 'gouvernorat', 'deliverer_id', 'search', 'date_from', 'date_to']))
                    <a href="{{ route('depot-manager.packages.all') }}"
                       class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Effacer
                    </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Liste des colis -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
            <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-4 border-b border-slate-200">
                <h3 class="text-lg font-bold text-slate-900">Liste des Colis</h3>
                <p class="text-slate-500 text-sm">{{ $packages->total() }} colis au total</p>
            </div>

            @if($packages->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Destinataire</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Livreur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">COD</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($packages as $package)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="text-sm font-bold text-indigo-600">{{ $package->package_code }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900">{{ $package->recipient_name }}</div>
                                <div class="text-sm text-slate-500">{{ $package->recipient_phone }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($package->assignedDeliverer)
                                    <div class="text-sm font-medium text-slate-900">
                                        {{ $package->assignedDeliverer->first_name }} {{ $package->assignedDeliverer->last_name }}
                                    </div>
                                    <div class="text-sm text-slate-500">{{ $package->assignedDeliverer->assigned_delegation }}</div>
                                @else
                                    <span class="text-sm text-slate-400">Non assigné</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($package->status === 'DELIVERED') bg-green-100 text-green-800
                                    @elseif($package->status === 'PICKED_UP') bg-blue-100 text-blue-800
                                    @elseif($package->status === 'ACCEPTED') bg-yellow-100 text-yellow-800
                                    @elseif($package->status === 'RETURNED') bg-red-100 text-red-800
                                    @elseif($package->status === 'CANCELLED') bg-slate-100 text-slate-800
                                    @else bg-purple-100 text-purple-800
                                    @endif">
                                    {{ $package->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                @if($package->cod_amount > 0)
                                    {{ number_format($package->cod_amount, 3) }} DT
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                {{ $package->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('depot-manager.packages.show', $package) }}"
                                       class="text-indigo-600 hover:text-indigo-900">Voir</a>
                                    @if(in_array($package->status, ['RETURNED', 'EXCHANGE_PROCESSED']))
                                        <a href="{{ route('depot-manager.packages.return-receipt', $package) }}"
                                           class="text-orange-600 hover:text-orange-900"
                                           target="_blank">Bon Retour</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                {{ $packages->withQueryString()->links() }}
            </div>
            @else
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <h3 class="text-lg font-medium text-slate-900 mb-2">Aucun colis trouvé</h3>
                <p class="text-slate-500">Il n'y a pas de colis correspondant à vos critères actuels.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function refreshPackages() {
    window.location.reload();
}

// Notification de succès
@if(session('success'))
    document.addEventListener('DOMContentLoaded', function() {
        showNotification("{{ session('success') }}", 'success');
    });
@endif

// Notification d'erreur
@if(session('error') || $errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        showNotification("{{ session('error') ?? $errors->first() }}", 'error');
    });
@endif

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-xl shadow-lg transform transition-all duration-300 translate-x-full opacity-0 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.remove('translate-x-full', 'opacity-0');
    }, 100);

    setTimeout(() => {
        notification.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}
</script>
@endsection