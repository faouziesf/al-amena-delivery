@extends('layouts.deliverer-modern')

@section('title', 'Menu')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="menuApp()">
    
    <!-- Header Profile -->
    <div class="bg-gradient-to-br from-indigo-600 to-purple-600 text-white safe-top">
        <div class="px-6 py-8">
            <div class="flex items-center space-x-4 mb-6">
                <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-bold">{{ auth()->user()->name }}</h2>
                    <p class="text-indigo-200 text-sm">Livreur</p>
                </div>
            </div>

            <!-- Stats rapides -->
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 text-center">
                    <div class="text-2xl font-bold" x-text="stats.deliveries"></div>
                    <div class="text-xs text-indigo-200">Livraisons</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 text-center">
                    <div class="text-2xl font-bold" x-text="stats.pickups"></div>
                    <div class="text-xs text-indigo-200">Ramassages</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 text-center">
                    <div class="text-2xl font-bold" x-text="formatMoney(stats.cod)"></div>
                    <div class="text-xs text-indigo-200">COD</div>
                </div>
            </div>
        </div>
    </div>

    <div class="p-4 space-y-6 pb-24">
        
        <!-- Actions Principales -->
        <div>
            <h3 class="font-bold text-gray-900 mb-3">Actions Rapides</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('deliverer.scan.simple') }}" 
                   class="card p-4 text-center hover:shadow-md transition-all active:scale-95">
                    <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                        <span class="text-2xl">📷</span>
                    </div>
                    <div class="font-semibold text-gray-900 text-sm">Scanner Unique</div>
                </a>

                <a href="{{ route('deliverer.scan.multi') }}" 
                   class="card p-4 text-center hover:shadow-md transition-all active:scale-95">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                        <span class="text-2xl">📦📦</span>
                    </div>
                    <div class="font-semibold text-gray-900 text-sm">Scanner Multiple</div>
                </a>

                <a href="{{ route('deliverer.recharge') }}" 
                   class="card p-4 text-center hover:shadow-md transition-all active:scale-95">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                        <span class="text-2xl">💳</span>
                    </div>
                    <div class="font-semibold text-gray-900 text-sm">Recharger Client</div>
                </a>

                <a href="{{ route('deliverer.wallet') }}" 
                   class="card p-4 text-center hover:shadow-md transition-all active:scale-95">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                        <span class="text-2xl">💵</span>
                    </div>
                    <div class="font-semibold text-gray-900 text-sm">Mon Wallet</div>
                </a>

                <a href="{{ route('deliverer.pickups.available') }}" 
                   class="card p-4 text-center hover:shadow-md transition-all active:scale-95">
                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                        <span class="text-2xl">📬</span>
                    </div>
                    <div class="font-semibold text-gray-900 text-sm">Pickups Disponibles</div>
                </a>
            </div>
        </div>

        <!-- Documents & Impression -->
        <div>
            <h3 class="font-bold text-gray-900 mb-3">Documents</h3>
            <div class="space-y-2">
                <a href="/deliverer/print/run-sheet" 
                   target="_blank"
                   class="card p-4 flex items-center justify-between hover:shadow-md transition-all">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">Imprimer Run Sheet</div>
                            <div class="text-xs text-gray-500">Liste de toutes vos tâches</div>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Paramètres & Aide -->
        <div>
            <h3 class="font-bold text-gray-900 mb-3">Paramètres & Aide</h3>
            <div class="space-y-2">
                <button @click="showProfile()" 
                        class="w-full card p-4 flex items-center justify-between hover:shadow-md transition-all">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="text-left">
                            <div class="font-semibold text-gray-900">Mon Profil</div>
                            <div class="text-xs text-gray-500">Informations personnelles</div>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <a href="tel:+21612345678" 
                   class="block card p-4 flex items-center justify-between hover:shadow-md transition-all">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <div class="text-left">
                            <div class="font-semibold text-gray-900">Support</div>
                            <div class="text-xs text-gray-500">Contactez-nous</div>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <button @click="showHelp()" 
                        class="w-full card p-4 flex items-center justify-between hover:shadow-md transition-all">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="text-left">
                            <div class="font-semibold text-gray-900">Aide & Guide</div>
                            <div class="text-xs text-gray-500">Comment utiliser l'app</div>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Déconnexion -->
        <div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        class="w-full card p-4 flex items-center justify-center space-x-3 hover:shadow-md transition-all text-red-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="font-semibold">Déconnexion</span>
                </button>
            </form>
        </div>

        <!-- Version -->
        <div class="text-center text-xs text-gray-400 pb-4">
            Al-Amena Delivery v2.0.0<br>
            © 2025 - Production Ready
        </div>
    </div>
</div>

@push('scripts')
<script>
function menuApp() {
    return {
        stats: {
            deliveries: 0,
            pickups: 0,
            cod: 0
        },

        async init() {
            await this.loadStats();
        },

        async loadStats() {
            try {
                const data = await apiRequest('/deliverer/api/packages/active');
                
                if (Array.isArray(data)) {
                    this.stats.deliveries = data.filter(p => p.type === 'livraison').length;
                    this.stats.pickups = data.filter(p => p.type === 'pickup').length;
                    this.stats.cod = data.reduce((sum, p) => sum + (p.cod_amount || 0), 0);
                }
            } catch (error) {
                console.error('Erreur chargement stats:', error);
            }
        },

        showProfile() {
            showToast('Profil - Fonctionnalité à venir', 'info');
        },

        showHelp() {
            const help = `
📱 Guide Rapide Al-Amena Delivery

🚚 MA TOURNÉE
- Voir toutes vos tâches du jour
- Filtrer par type (livraison/pickup)
- Cliquer sur une tâche pour les détails

📦 PICKUPS DISPONIBLES
- Voir les ramassages dans votre zone
- Accepter un pickup l'ajoute à votre tournée

📷 SCANNER
- Scanner Unique: Pour un colis
- Scanner Multiple: Pour plusieurs colis
- Obligatoire avant chaque action

💵 WALLET
- Voir votre caisse
- Demander un vidage
- Recharger un client

✍️ SIGNATURE
- Obligatoire pour certaines livraisons
- COD, échanges nécessitent signature

📞 SUPPORT: +216 12 345 678
            `;
            
            alert(help);
        },

        formatMoney(amount) {
            return (amount || 0).toFixed(0);
        }
    }
}
</script>
@endpush

@endsection
