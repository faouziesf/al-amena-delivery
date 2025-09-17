@extends('layouts.client')

@section('title', "Transaction #{$transaction->transaction_id}")

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header avec navigation -->
    <div class="flex items-center mb-6">
        <a href="{{ route('client.wallet.transactions') }}" 
           class="flex items-center text-gray-600 hover:text-gray-900 transition-colors mr-4">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Retour aux transactions
        </a>
        <div class="text-gray-400">•</div>
        <a href="{{ route('client.wallet.index') }}" 
           class="ml-4 text-gray-600 hover:text-gray-900 transition-colors">
            Portefeuille
        </a>
    </div>

    <!-- Titre principal -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    📊 Transaction #{{ Str::limit($transaction->transaction_id, 20) }}
                </h1>
                <p class="text-gray-600">Détails complets de la transaction</p>
            </div>
            <div class="mt-4 md:mt-0">
                <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium {{ $transaction->status_color }}">
                    {{ $transaction->status_display }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Colonne principale -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Informations principales -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-6">💳 Détails de la transaction</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">ID de transaction</p>
                        <p class="text-lg font-mono text-gray-900 bg-gray-100 px-3 py-2 rounded">
                            {{ $transaction->transaction_id }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Montant</p>
                        <p class="text-3xl font-bold {{ $transaction->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $transaction->formatted_amount }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Type de transaction</p>
                        <div class="flex items-center">
                            @if($transaction->amount > 0)
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-2">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                    </svg>
                                </div>
                            @else
                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-2">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                    </svg>
                                </div>
                            @endif
                            <span class="font-medium text-gray-900">{{ $transaction->type_display }}</span>
                        </div>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Statut</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $transaction->status_color }}">
                            {{ $transaction->status_display }}
                        </span>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Date de création</p>
                        <p class="text-lg font-medium text-gray-900">{{ $transaction->created_at->format('d/m/Y à H:i:s') }}</p>
                        <p class="text-sm text-gray-500">{{ $transaction->created_at->diffForHumans() }}</p>
                    </div>

                    @if($transaction->completed_at)
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Date de traitement</p>
                            <p class="text-lg font-medium text-gray-900">{{ $transaction->completed_at->format('d/m/Y à H:i:s') }}</p>
                            <p class="text-sm text-gray-500">{{ $transaction->completed_at->diffForHumans() }}</p>
                        </div>
                    @endif
                </div>

                <div class="mt-6">
                    <p class="text-sm font-medium text-gray-600 mb-2">Description</p>
                    <p class="text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $transaction->description }}</p>
                </div>
            </div>

            <!-- Informations de solde -->
            @if($transaction->wallet_balance_before !== null && $transaction->wallet_balance_after !== null)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">💰 Impact sur le solde</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <p class="text-sm font-medium text-blue-600 mb-1">Solde avant</p>
                            <p class="text-2xl font-bold text-blue-900">{{ number_format($transaction->wallet_balance_before, 3) }} DT</p>
                        </div>
                        
                        <div class="text-center p-4 bg-gray-50 rounded-lg flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </div>
                        
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <p class="text-sm font-medium text-green-600 mb-1">Solde après</p>
                            <p class="text-2xl font-bold text-green-900">{{ number_format($transaction->wallet_balance_after, 3) }} DT</p>
                        </div>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <p class="text-sm text-gray-600">
                            Variation: 
                            <span class="font-bold {{ $transaction->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 3) }} DT
                            </span>
                        </p>
                    </div>
                </div>
            @endif

            <!-- Informations de colis (si applicable) -->
            @if($transaction->package_id && $transaction->package)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📦 Colis associé</h3>
                    
                    <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                        <div>
                            <p class="font-semibold text-blue-900">{{ $transaction->package->package_code }}</p>
                            <p class="text-sm text-blue-700">{{ $transaction->package->recipient_name ?? 'Destinataire non spécifié' }}</p>
                            <p class="text-sm text-blue-600">Statut: {{ $transaction->package->status }}</p>
                        </div>
                        <div>
                            @if(Route::has('client.packages.show'))
                                <a href="{{ route('client.packages.show', $transaction->package_id) }}" 
                                   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                    📦 Voir le colis
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Métadonnées -->
            @if($transaction->metadata && is_array($transaction->metadata) && count($transaction->metadata) > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">🔧 Métadonnées techniques</h3>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="space-y-2">
                            @foreach($transaction->metadata as $key => $value)
                                <div class="flex justify-between items-start">
                                    <span class="text-sm font-medium text-gray-700 capitalize">
                                        {{ str_replace('_', ' ', $key) }}:
                                    </span>
                                    <span class="text-sm text-gray-900 ml-4 text-right">
                                        @if(is_array($value))
                                            <pre class="text-xs bg-white px-2 py-1 rounded">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                        @else
                                            {{ $value }}
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Transactions liées -->
            @if($relatedTransactions->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">🔗 Transactions liées</h3>
                    
                    <div class="space-y-3">
                        @foreach($relatedTransactions as $related)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="flex items-center">
                                    <div class="w-6 h-6 {{ $related->amount >= 0 ? 'bg-green-100' : 'bg-red-100' }} rounded-full flex items-center justify-center mr-3">
                                        <div class="w-2 h-2 {{ $related->amount >= 0 ? 'bg-green-600' : 'bg-red-600' }} rounded-full"></div>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ Str::limit($related->description, 40) }}</p>
                                        <p class="text-xs text-gray-500">{{ $related->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold {{ $related->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $related->formatted_amount }}
                                    </p>
                                    <a href="{{ route('client.wallet.transaction.show', $related) }}" 
                                       class="text-xs text-blue-600 hover:text-blue-800">
                                        Voir →
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Colonne latérale -->
        <div class="space-y-6">
            <!-- Informations techniques -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">⚙️ Détails techniques</h3>
                
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="font-medium text-gray-600">Numéro de séquence</p>
                        <p class="text-gray-900 font-mono">{{ $transaction->sequence_number ?? 'N/A' }}</p>
                    </div>

                    @if($transaction->checksum)
                        <div>
                            <p class="font-medium text-gray-600">Checksum</p>
                            <p class="text-gray-900 font-mono text-xs break-all">{{ Str::limit($transaction->checksum, 20) }}...</p>
                        </div>
                    @endif

                    @if($transaction->reference)
                        <div>
                            <p class="font-medium text-gray-600">Référence</p>
                            <p class="text-gray-900 font-mono">{{ $transaction->reference }}</p>
                        </div>
                    @endif

                    <div>
                        <p class="font-medium text-gray-600">Vérification intégrité</p>
                        @if($transaction->checksum && $transaction->verifyChecksum())
                            <p class="text-green-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Validé
                            </p>
                        @else
                            <p class="text-gray-500">Non disponible</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">⚡ Actions</h3>
                
                <div class="space-y-3">
                    @if($transaction->package_id && Route::has('client.packages.show'))
                        <a href="{{ route('client.packages.show', $transaction->package_id) }}" 
                           class="block w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium text-center">
                            📦 Voir le colis
                        </a>
                    @endif

                    <a href="{{ route('client.wallet.transactions') }}" 
                       class="block w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium text-center">
                        📋 Toutes les transactions
                    </a>

                    <a href="{{ route('client.wallet.index') }}" 
                       class="block w-full bg-green-100 text-green-700 px-4 py-2 rounded-lg hover:bg-green-200 transition-colors text-sm font-medium text-center">
                        💰 Retour au portefeuille
                    </a>
                </div>
            </div>

            <!-- Support -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-yellow-900 mb-3">💡 Besoin d'aide ?</h3>
                <div class="text-sm text-yellow-800 space-y-2">
                    <p>• Conservez cet ID de transaction pour toute réclamation</p>
                    <p>• Les transactions complétées ne peuvent être modifiées</p>
                    <p>• Contactez le support en cas de problème</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Animation d'entrée
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.bg-white');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>
@endpush
@endsection