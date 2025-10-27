@extends('layouts.client')

@section('title', 'API & Intégrations')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">🔐 API & Intégrations</h1>
    
    <!-- Section Info API -->
    <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg mb-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-2">
            📘 À propos de l'API Al-Amena Delivery
        </h3>
        <p class="text-blue-800 mb-4">
            Notre API REST vous permet d'automatiser la création de colis et de suivre vos livraisons directement depuis votre système e-commerce ou ERP.
        </p>
        <ul class="list-disc list-inside text-blue-700 mb-4 space-y-1">
            <li>Créer des colis automatiquement</li>
            <li>Exporter et suivre vos colis en temps réel</li>
            <li>Recevoir les mises à jour de statut</li>
            <li>Intégrer avec WooCommerce, Shopify, PrestaShop, etc.</li>
        </ul>
        <a href="{{ url('/docs/api') }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            📖 Consulter la Documentation Complète
        </a>
    </div>
    
    <!-- Section Gestion du Token -->
    <div class="bg-white border border-gray-300 rounded-lg shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">🔑 Votre Token API</h3>
        
        @if(!$apiToken)
            <!-- Aucun token -->
            <p class="text-gray-600 mb-4">
                Vous n'avez pas encore de token API. Cliquez sur le bouton ci-dessous pour en générer un.
            </p>
            <button onclick="showGenerateModal()" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                ✨ Générer Mon Token API
            </button>
        @else
            <!-- Token actif -->
            <div class="flex items-center mb-3">
                <span class="flex items-center">
                    <span class="w-3 h-3 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                    <span class="font-medium text-green-700">Actif</span>
                </span>
            </div>
            
            <div class="text-sm text-gray-600 mb-4 space-y-1">
                <p>Créé le : <strong>{{ $apiToken->created_at->format('d/m/Y à H:i') }}</strong></p>
                @if($apiToken->last_used_at)
                    <p>Dernière utilisation : <strong>{{ $apiToken->last_used_at->diffForHumans() }}</strong></p>
                @else
                    <p>Dernière utilisation : <strong>Jamais utilisé</strong></p>
                @endif
            </div>
            
            <!-- Token Display -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Token :</label>
                <div class="relative">
                    <input type="password" 
                           id="apiToken" 
                           value="{{ $apiToken->token }}"
                           readonly
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg font-mono text-sm select-all">
                    <button onclick="toggleVisibility()" 
                            class="absolute right-3 top-3 text-gray-500 hover:text-gray-700 transition">
                        <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex gap-3">
                <button onclick="copyToken()" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center justify-center">
                    📋 Copier
                </button>
                <button onclick="showRegenerateModal()" class="flex-1 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition flex items-center justify-center">
                    🔄 Régénérer
                </button>
                <button onclick="showDeleteModal()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center justify-center">
                    🗑️
                </button>
            </div>
        @endif
    </div>
    
    <!-- Section Sécurité -->
    <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg mb-6">
        <h3 class="text-lg font-semibold text-red-900 mb-3 flex items-center">
            ⚠️ Consignes de Sécurité
        </h3>
        <ul class="space-y-2 text-red-800">
            <li class="flex items-start">
                <span class="mr-2 mt-1">🔒</span>
                <span><strong>Ne partagez JAMAIS votre token</strong> - Il donne un accès complet à votre compte</span>
            </li>
            <li class="flex items-start">
                <span class="mr-2 mt-1">🌐</span>
                <span><strong>Utilisez HTTPS uniquement</strong> - Ne faites jamais d'appels API en HTTP non sécurisé</span>
            </li>
            <li class="flex items-start">
                <span class="mr-2 mt-1">💾</span>
                <span><strong>Variables d'environnement</strong> - Ne codez jamais le token en dur dans votre code source</span>
            </li>
            <li class="flex items-start">
                <span class="mr-2 mt-1">🔄</span>
                <span><strong>Régénérez immédiatement si compromis</strong> - En cas de doute, régénérez un nouveau token</span>
            </li>
            <li class="flex items-start">
                <span class="mr-2 mt-1">👁️</span>
                <span><strong>Surveillez l'activité</strong> - Vérifiez régulièrement les statistiques d'utilisation</span>
            </li>
        </ul>
    </div>
    
    <!-- Section Statistiques (si token existe) -->
    @if($apiToken && $stats)
    <div class="bg-white border border-gray-300 rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4">📊 Statistiques d'Utilisation</h3>
        
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <p class="text-sm text-blue-700 mb-1">Aujourd'hui</p>
                <p class="text-2xl font-bold text-blue-900">{{ $stats['today'] }} req</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <p class="text-sm text-green-700 mb-1">Ce mois</p>
                <p class="text-2xl font-bold text-green-900">{{ $stats['month'] }} req</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <p class="text-sm text-purple-700 mb-1">Limite</p>
                <p class="text-2xl font-bold text-purple-900">120/min</p>
            </div>
        </div>
        
        <!-- Dernières Activités -->
        @if(count($stats['recent_activity']) > 0)
        <div class="border-t pt-4">
            <h4 class="font-medium mb-3">Dernières Activités</h4>
            <div class="space-y-2">
                @foreach($stats['recent_activity'] as $activity)
                <div class="flex justify-between items-center p-2 bg-gray-50 rounded text-sm">
                    <div class="flex items-center space-x-2">
                        <span class="px-2 py-1 rounded text-xs font-mono font-bold
                            @if($activity['method'] == 'GET') bg-blue-100 text-blue-800
                            @elseif($activity['method'] == 'POST') bg-green-100 text-green-800
                            @elseif($activity['method'] == 'DELETE') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ $activity['method'] }}
                        </span>
                        <span class="text-gray-700">{{ $activity['endpoint'] }}</span>
                    </div>
                    <span class="text-gray-500">{{ $activity['time'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        
        <a href="{{ route('client.api.history') }}" class="mt-4 w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-center block">
            📈 Voir l'Historique Détaillé
        </a>
    </div>
    @endif
</div>

<!-- Modales -->
@include('client.settings.api-modals')

@endsection

@push('scripts')
<script src="{{ asset('js/client-api-token.js') }}"></script>
@endpush
