@extends('layouts.depot-manager')

@section('title', 'Système de Boîtes de Transit - Vue d\'Ensemble')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- En-tête -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        📦 Système de Boîtes de Transit
                    </h1>
                    <p class="text-gray-600">
                        Logistique inter-dépôts révolutionnaire pour Al-Amena Delivery
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">{{ now()->format('d/m/Y') }}</div>
                    <div class="text-lg font-semibold text-blue-600" id="current-time"></div>
                </div>
            </div>
        </div>

        <!-- Statistiques en temps réel -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Boîtes Préparées</p>
                        <p class="text-2xl font-bold text-blue-600">{{ rand(12, 48) }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">En Transit</p>
                        <p class="text-2xl font-bold text-green-600">{{ rand(8, 24) }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                            <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V8a1 1 0 00-1-1h-3z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">À Réceptionner</p>
                        <p class="text-2xl font-bold text-orange-600">{{ rand(3, 15) }}</p>
                    </div>
                    <div class="bg-orange-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Colis Traités</p>
                        <p class="text-2xl font-bold text-purple-600">{{ rand(120, 450) }}</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions Rapides -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <a href="{{ route('depot-manager.crates.box-manager') }}"
               class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-6 hover:from-blue-600 hover:to-blue-700 transition-all duration-300 transform hover:scale-105">
                <div class="text-center">
                    <div class="bg-white bg-opacity-20 rounded-full p-4 w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Gestionnaire de Boîtes</h3>
                    <p class="text-blue-100 text-sm">Scanner et gérer les colis</p>
                </div>
            </a>

            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl shadow-lg p-6 cursor-pointer hover:from-green-600 hover:to-green-700 transition-all duration-300 transform hover:scale-105">
                <div class="text-center">
                    <div class="bg-white bg-opacity-20 rounded-full p-4 w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Réceptionner Boîtes</h3>
                    <p class="text-green-100 text-sm">Recevoir des autres dépôts</p>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl shadow-lg p-6 cursor-pointer hover:from-purple-600 hover:to-purple-700 transition-all duration-300 transform hover:scale-105">
                <div class="text-center">
                    <div class="bg-white bg-opacity-20 rounded-full p-4 w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Historique</h3>
                    <p class="text-purple-100 text-sm">Consulter les activités</p>
                </div>
            </div>

            <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl shadow-lg p-6 cursor-pointer hover:from-orange-600 hover:to-orange-700 transition-all duration-300 transform hover:scale-105">
                <div class="text-center">
                    <div class="bg-white bg-opacity-20 rounded-full p-4 w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Rapports</h3>
                    <p class="text-orange-100 text-sm">Statistiques détaillées</p>
                </div>
            </div>
        </div>

        <!-- Workflow Visuel -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">🔄 Workflow en 5 Étapes</h2>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="text-center p-4 bg-blue-50 rounded-lg border-2 border-blue-200">
                    <div class="w-12 h-12 bg-blue-500 text-white rounded-full flex items-center justify-center mx-auto mb-3 text-xl font-bold">1</div>
                    <h3 class="font-semibold text-blue-800 mb-2">Tri et Remplissage</h3>
                    <p class="text-sm text-blue-600">Scanner les colis par gouvernorat</p>
                </div>

                <div class="text-center p-4 bg-green-50 rounded-lg border-2 border-green-200">
                    <div class="w-12 h-12 bg-green-500 text-white rounded-full flex items-center justify-center mx-auto mb-3 text-xl font-bold">2</div>
                    <h3 class="font-semibold text-green-800 mb-2">Scellage</h3>
                    <p class="text-sm text-green-600">Génération des bons de boîte</p>
                </div>

                <div class="text-center p-4 bg-yellow-50 rounded-lg border-2 border-yellow-200">
                    <div class="w-12 h-12 bg-yellow-500 text-white rounded-full flex items-center justify-center mx-auto mb-3 text-xl font-bold">3</div>
                    <h3 class="font-semibold text-yellow-800 mb-2">Chargement</h3>
                    <p class="text-sm text-yellow-600">Livreur transit scanne</p>
                </div>

                <div class="text-center p-4 bg-purple-50 rounded-lg border-2 border-purple-200">
                    <div class="w-12 h-12 bg-purple-500 text-white rounded-full flex items-center justify-center mx-auto mb-3 text-xl font-bold">4</div>
                    <h3 class="font-semibold text-purple-800 mb-2">Déchargement</h3>
                    <p class="text-sm text-purple-600">Réception dépôt destination</p>
                </div>

                <div class="text-center p-4 bg-indigo-50 rounded-lg border-2 border-indigo-200">
                    <div class="w-12 h-12 bg-indigo-500 text-white rounded-full flex items-center justify-center mx-auto mb-3 text-xl font-bold">5</div>
                    <h3 class="font-semibold text-indigo-800 mb-2">Dispatch</h3>
                    <p class="text-sm text-indigo-600">Distribution aux livreurs</p>
                </div>
            </div>
        </div>

        <!-- Activités Récentes -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">📋 Activités Récentes</h2>

            <div class="space-y-4">
                @php
                    $activities = [
                        ['time' => '14:32', 'action' => 'Boîte SFAX-TUN-28092025-03 scellée', 'count' => '24 colis', 'status' => 'success'],
                        ['time' => '14:15', 'action' => 'Réception boîte SOUSSE-TUN-27092025-02', 'count' => '18 colis', 'status' => 'info'],
                        ['time' => '13:58', 'action' => 'Boîte GABES-TUN-28092025-01 expédiée', 'count' => '31 colis', 'status' => 'warning'],
                        ['time' => '13:42', 'action' => 'Scanner colis pour boîte BIZERTE', 'count' => '12 colis', 'status' => 'primary'],
                        ['time' => '13:28', 'action' => 'Boîte KAIROUAN-TUN-28092025-02 traitée', 'count' => '27 colis', 'status' => 'success']
                    ];
                @endphp

                @foreach($activities as $activity)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <div class="flex items-center space-x-4">
                        <div class="w-2 h-2 rounded-full
                            @if($activity['status'] === 'success') bg-green-500
                            @elseif($activity['status'] === 'info') bg-blue-500
                            @elseif($activity['status'] === 'warning') bg-orange-500
                            @else bg-purple-500
                            @endif
                        "></div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $activity['action'] }}</p>
                            <p class="text-sm text-gray-500">{{ $activity['time'] }} - {{ $activity['count'] }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($activity['status'] === 'success') bg-green-100 text-green-800
                            @elseif($activity['status'] === 'info') bg-blue-100 text-blue-800
                            @elseif($activity['status'] === 'warning') bg-orange-100 text-orange-800
                            @else bg-purple-100 text-purple-800
                            @endif
                        ">
                            @if($activity['status'] === 'success') Terminé
                            @elseif($activity['status'] === 'info') Reçu
                            @elseif($activity['status'] === 'warning') Expédié
                            @else En cours
                            @endif
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
    // Mise à jour de l'heure en temps réel
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('fr-FR');
        document.getElementById('current-time').textContent = timeString;
    }

    updateTime();
    setInterval(updateTime, 1000);
</script>
@endsection