@extends('layouts.client')

@section('title', 'Mes Adresses de Collecte')
@section('page-title', 'Adresses de Collecte')
@section('page-description', 'Gérez vos adresses de collecte pour un envoi plus rapide')

@section('content')
<style>
@keyframes slideInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
@keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-8px); } }
.address-card { animation: slideInUp 0.4s ease-out; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.address-card:hover { transform: translateY(-6px) scale(1.02); box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.1); }
</style>

<div class="max-w-6xl mx-auto">

    <!-- Header avec Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 space-y-4 sm:space-y-0">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg" style="animation: float 3s ease-in-out infinite">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Mes Adresses de Collecte</h2>
                <p class="text-gray-600">{{ $addresses->count() }} adresse(s) enregistrée(s)</p>
            </div>
        </div>

        <div class="flex space-x-3">
            <a href="{{ route('client.pickup-requests.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Demandes de Collecte
            </a>
            <a href="{{ route('client.pickup-addresses.create') }}" class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:from-emerald-700 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouvelle Adresse
            </a>
        </div>
    </div>

    <!-- Liste des Adresses -->
    @if($addresses->count() > 0)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($addresses as $address)
            <div class="address-card bg-white rounded-2xl shadow-lg border border-gray-200 p-6 hover:shadow-xl {{ $address->is_default ? 'ring-2 ring-emerald-500 bg-emerald-50' : '' }}">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br {{ $address->is_default ? 'from-emerald-500 to-teal-600' : 'from-gray-100 to-gray-200' }} rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 {{ $address->is_default ? 'text-white' : 'text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ $address->name }}</h3>
                            @if($address->is_default)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Adresse par défaut
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Menu Actions -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                            </svg>
                        </button>

                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                            <div class="py-1">
                                <a href="{{ route('client.pickup-addresses.edit', $address) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Modifier
                                </a>
                                @if(!$address->is_default)
                                <button onclick="setDefault({{ $address->id }})" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Définir par défaut
                                </button>
                                @endif
                                <div class="border-t border-gray-100"></div>
                                <form action="{{ route('client.pickup-addresses.destroy', $address) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette adresse ?')" class="flex items-center w-full px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations de l'adresse -->
                <div class="space-y-3">
                    <div>
                        <p class="text-sm font-medium text-gray-900 mb-1">📍 Adresse</p>
                        <p class="text-gray-700 text-sm">{{ $address->address }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900 mb-1">🏢 Délégation</p>
                            <p class="text-gray-700 text-sm">{{ $address->delegation }}</p>
                        </div>
                        @if($address->contact_name)
                        <div>
                            <p class="text-sm font-medium text-gray-900 mb-1">👤 Contact</p>
                            <p class="text-gray-700 text-sm">{{ $address->contact_name }}</p>
                        </div>
                        @endif
                    </div>

                    @if($address->phone)
                    <div>
                        <p class="text-sm font-medium text-gray-900 mb-1">📞 Téléphone</p>
                        <p class="text-gray-700 text-sm">{{ $address->phone }}</p>
                    </div>
                    @endif

                    @if($address->notes)
                    <div>
                        <p class="text-sm font-medium text-gray-900 mb-1">📝 Notes</p>
                        <p class="text-gray-600 text-sm bg-gray-50 p-2 rounded">{{ $address->notes }}</p>
                    </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
                    <span class="text-xs text-gray-500">
                        Créée le {{ $address->created_at->format('d/m/Y') }}
                    </span>
                    <div class="flex space-x-2">
                        <a href="{{ route('client.pickup-addresses.edit', $address) }}"
                           class="inline-flex items-center px-3 py-1 text-xs font-medium text-emerald-700 bg-emerald-100 rounded-full hover:bg-emerald-200 transition-colors duration-200">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Modifier
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <!-- État vide -->
        <div class="text-center py-16">
            <div class="w-24 h-24 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune adresse de collecte</h3>
            <p class="text-gray-600 mb-6">Ajoutez vos adresses de collecte pour créer des demandes plus rapidement.</p>
            <a href="{{ route('client.pickup-addresses.create') }}"
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 border border-transparent rounded-lg shadow-sm text-base font-medium text-white hover:from-emerald-700 hover:to-teal-700 transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Ajouter ma première adresse
            </a>
        </div>
    @endif
</div>

<script>
function setDefault(addressId) {
    if (confirm('Définir cette adresse comme adresse par défaut ?')) {
        fetch(`/client/pickup-addresses/${addressId}/set-default`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        }).then(response => {
            if (response.ok) {
                location.reload();
            }
        });
    }
}
</script>
@endsection