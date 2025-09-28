@extends('layouts.depot-manager')

@section('title', 'Rapports & Analytics')
@section('page-title', 'Rapports & Analytics')
@section('page-description', 'Analyse de performance de vos gouvernorats')

@section('content')
<div class="space-y-6">

    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Rapports & Analytics</h1>
            <p class="text-gray-600 mt-1">Analyse de performance de vos gouvernorats assignés</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <button onclick="exportReport()"
                    class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors text-sm font-medium inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Exporter PDF
            </button>
        </div>
    </div>

    <!-- Filtres de période -->
    <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Période</label>
                <select name="period" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="today" {{ request('period', 'today') == 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                    <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>Cette semaine</option>
                    <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>Ce mois</option>
                    <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Personnalisée</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date début</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date fin</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            </div>

            <div class="flex items-end">
                <button type="submit"
                        class="w-full bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors text-sm font-medium">
                    Générer Rapport
                </button>
            </div>
        </form>
    </div>

    <!-- Métriques principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Colis</p>
                    <p class="text-3xl font-bold text-orange-600">{{ $metrics['total_packages'] ?? 0 }}</p>
                    <p class="text-sm text-gray-500">Sur la période</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Livrés</p>
                    <p class="text-3xl font-bold text-green-600">{{ $metrics['delivered_packages'] ?? 0 }}</p>
                    <p class="text-sm text-gray-500">{{ number_format(($metrics['delivered_packages'] ?? 0) / max(($metrics['total_packages'] ?? 1), 1) * 100, 1) }}% de réussite</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">COD Collecté</p>
                    <p class="text-3xl font-bold text-purple-600">{{ number_format($metrics['total_cod'] ?? 0, 0) }}</p>
                    <p class="text-sm text-gray-500">DT collectés</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Retournés</p>
                    <p class="text-3xl font-bold text-red-600">{{ $metrics['returned_packages'] ?? 0 }}</p>
                    <p class="text-sm text-gray-500">{{ number_format(($metrics['returned_packages'] ?? 0) / max(($metrics['total_packages'] ?? 1), 1) * 100, 1) }}% de retour</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance par gouvernorat -->
    <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Performance par Gouvernorat</h3>

        <div class="space-y-4">
            @foreach($governorateStats ?? [] as $stat)
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-medium text-gray-900">{{ $stat['name'] }}</h4>
                    <span class="text-sm text-gray-500">{{ $stat['total_packages'] }} colis</span>
                </div>

                <div class="grid grid-cols-4 gap-4 text-center">
                    <div>
                        <p class="text-lg font-bold text-green-600">{{ $stat['delivered'] }}</p>
                        <p class="text-xs text-gray-500">Livrés</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-blue-600">{{ $stat['in_progress'] }}</p>
                        <p class="text-xs text-gray-500">En cours</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-red-600">{{ $stat['returned'] }}</p>
                        <p class="text-xs text-gray-500">Retournés</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-purple-600">{{ number_format($stat['cod_collected'], 0) }}</p>
                        <p class="text-xs text-gray-500">COD DT</p>
                    </div>
                </div>

                <!-- Barre de progression -->
                <div class="mt-4">
                    <div class="flex text-xs text-gray-600 mb-1">
                        <span>Taux de livraison</span>
                        <span class="ml-auto">{{ number_format($stat['delivery_rate'], 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full transition-all duration-300"
                             style="width: {{ $stat['delivery_rate'] }}%"></div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Top livreurs -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Top Livreurs - Livraisons</h3>

            <div class="space-y-4">
                @foreach($topDeliverers ?? [] as $index => $deliverer)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                            <span class="text-orange-600 font-semibold text-sm">{{ $index + 1 }}</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $deliverer['name'] }}</p>
                            <p class="text-sm text-gray-500">{{ $deliverer['gouvernorat'] }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-green-600">{{ $deliverer['deliveries'] }}</p>
                        <p class="text-xs text-gray-500">Livraisons</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Top Livreurs - COD</h3>

            <div class="space-y-4">
                @foreach($topDeliverersCOD ?? [] as $index => $deliverer)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                            <span class="text-purple-600 font-semibold text-sm">{{ $index + 1 }}</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $deliverer['name'] }}</p>
                            <p class="text-sm text-gray-500">{{ $deliverer['gouvernorat'] }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-purple-600">{{ number_format($deliverer['cod_amount'], 0) }}</p>
                        <p class="text-xs text-gray-500">DT collectés</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Analyse des colis d'échange -->
    @if(($exchangeStats['total'] ?? 0) > 0)
    <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            <span class="flex items-center">
                <svg class="w-5 h-5 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                Analyse Colis d'Échange
            </span>
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center">
                <p class="text-2xl font-bold text-orange-600">{{ $exchangeStats['total'] ?? 0 }}</p>
                <p class="text-sm text-gray-600">Total échanges</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-green-600">{{ $exchangeStats['delivered'] ?? 0 }}</p>
                <p class="text-sm text-gray-600">Livrés</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-blue-600">{{ $exchangeStats['returned'] ?? 0 }}</p>
                <p class="text-sm text-gray-600">Retournés</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-purple-600">{{ number_format($exchangeStats['success_rate'] ?? 0, 1) }}%</p>
                <p class="text-sm text-gray-600">Taux de succès</p>
            </div>
        </div>

        <div class="mt-6 p-4 bg-orange-50 rounded-lg border border-orange-200">
            <div class="flex items-start space-x-2">
                <svg class="w-5 h-5 text-orange-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-orange-800">Gestion des Échanges</p>
                    <p class="text-xs text-orange-600 mt-1">
                        Les colis d'échange représentent {{ number_format(($exchangeStats['total'] ?? 0) / max(($metrics['total_packages'] ?? 1), 1) * 100, 1) }}%
                        de votre volume total. Surveillez le taux de retour pour optimiser la logistique.
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Évolution temporelle -->
    <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Évolution des Livraisons</h3>

        <div class="h-64 flex items-end justify-between space-x-2">
            @foreach($dailyStats ?? [] as $day)
            <div class="flex-1 flex flex-col items-center">
                <div class="w-full bg-gray-200 rounded-t-lg relative" style="height: 200px;">
                    @if($day['total'] > 0)
                    <div class="bg-green-500 rounded-t-lg absolute bottom-0 w-full transition-all duration-500"
                         style="height: {{ ($day['delivered'] / max($day['total'], 1)) * 200 }}px;"
                         title="Livrés: {{ $day['delivered'] }}"></div>
                    <div class="bg-blue-500 absolute bottom-0 w-full transition-all duration-500"
                         style="height: {{ (($day['delivered'] + $day['in_progress']) / max($day['total'], 1)) * 200 }}px;"
                         title="En cours: {{ $day['in_progress'] }}"></div>
                    @endif
                </div>
                <div class="mt-2 text-center">
                    <p class="text-xs font-medium text-gray-900">{{ $day['date'] }}</p>
                    <p class="text-xs text-gray-500">{{ $day['total'] }} colis</p>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4 flex justify-center space-x-6 text-sm">
            <div class="flex items-center">
                <div class="w-3 h-3 bg-green-500 rounded mr-2"></div>
                <span class="text-gray-700">Livrés</span>
            </div>
            <div class="flex items-center">
                <div class="w-3 h-3 bg-blue-500 rounded mr-2"></div>
                <span class="text-gray-700">En cours</span>
            </div>
            <div class="flex items-center">
                <div class="w-3 h-3 bg-gray-300 rounded mr-2"></div>
                <span class="text-gray-700">Total</span>
            </div>
        </div>
    </div>

</div>

<script>
function exportReport() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'pdf');
    window.open(`${window.location.pathname}?${params.toString()}`, '_blank');
}

// Auto-refresh des données toutes les 5 minutes
setInterval(() => {
    if (document.hidden) return; // Ne pas actualiser si l'onglet n'est pas visible

    fetch(window.location.href)
        .then(response => response.text())
        .then(html => {
            // Mettre à jour seulement les métriques
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');

            // Mise à jour des cartes de métriques
            const oldCards = document.querySelectorAll('.text-3xl.font-bold');
            const newCards = newDoc.querySelectorAll('.text-3xl.font-bold');

            oldCards.forEach((card, index) => {
                if (newCards[index]) {
                    card.textContent = newCards[index].textContent;
                }
            });
        })
        .catch(error => console.log('Erreur mise à jour:', error));
}, 300000); // 5 minutes
</script>

@endsection