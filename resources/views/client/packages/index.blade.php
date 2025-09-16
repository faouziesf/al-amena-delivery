@extends('layouts.client')

@section('title', 'Mes Colis')
@section('page-title', 'Gestion des Colis')
@section('page-description', 'Interface centralisée pour tous vos envois')

@section('content')
{{-- Le CSS reste identique, car il gère les animations et les transitions --}}
<style>
/* Animations et Styles Modernes */
@keyframes slideInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
@keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-8px); } }
@keyframes shimmer { 0% { background-position: -200px 0; } 100% { background-position: calc(200px + 100%) 0; } }
.package-card { animation: slideInUp 0.4s ease-out; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); }
.package-card:hover { transform: translateY(-6px) scale(1.01); box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.1); }
.floating-actions { backdrop-filter: blur(15px); background: rgba(255, 255, 255, 0.9); border: 1px solid rgba(255, 255, 255, 0.2); }
.stat-card { animation: slideInUp 0.5s ease-out; transition: all 0.3s ease; }
.stat-card:hover { transform: translateY(-4px) scale(1.03); }
.tab-button.active { transform: translateY(-2px); }
.tab-button.active::after { content: ''; position: absolute; bottom: -2px; left: 50%; transform: translateX(-50%); width: 25px; height: 3px; background: currentColor; border-radius: 2px; }
.notification { animation: slideInRight 0.3s ease-out; }
@keyframes slideInRight { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
.loading-skeleton { background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200px 100%; animation: shimmer 1.5s infinite; }
</style>

<div class="max-w-7xl mx-auto" x-data="packagesTabsManager()" x-init="init()">
    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @php
            $statsCards = [
                ['color' => 'blue', 'label' => 'TOTAL COLIS', 'value' => $stats['total'] ?? 0, 'sub' => '📊 Tous vos envois', 'iconPath' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                ['color' => 'orange', 'label' => 'EN ATTENTE', 'value' => $stats['pending'] ?? 0, 'sub' => '⏳ Prêts pickup', 'iconPath' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['color' => 'indigo', 'label' => 'EN COURS', 'value' => $stats['in_progress'] ?? 0, 'sub' => '🚚 En livraison', 'iconPath' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                ['color' => 'green', 'label' => 'LIVRÉS', 'value' => $stats['delivered'] ?? 0, 'sub' => '✅ Succès', 'iconPath' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ];
        @endphp
        @foreach($statsCards as $card)
        <div class="stat-card bg-gradient-to-br from-{{$card['color']}}-50 to-{{$card['color']}}-100 rounded-2xl shadow-lg border border-{{$card['color']}}-200 p-4 hover:shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-{{$card['color']}}-700 mb-1 tracking-wide">{{$card['label']}}</p>
                    <p class="text-3xl font-extrabold text-{{$card['color']}}-900">{{$card['value']}}</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-{{$card['color']}}-400 to-{{$card['color']}}-600 rounded-xl flex items-center justify-center shadow-md" style="animation: float 3s ease-in-out infinite; animation-delay: {{ $loop->index * 0.3 }}s">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{$card['iconPath']}}"/></svg>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="bg-gray-50 border-b border-gray-200 p-1.5">
            <nav class="flex space-x-1.5" aria-label="Tabs">
                @php
                    $tabs = [
                        ['id' => 'create', 'label' => 'Créer Colis', 'color' => 'emerald', 'iconPath' => 'M12 6v6m0 0v6m0-6h6m-6 0H6'],
                        ['id' => 'import', 'label' => 'Importer CSV', 'color' => 'purple', 'iconPath' => 'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10'],
                        ['id' => 'pending', 'label' => 'En Attente', 'color' => 'orange', 'iconPath' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['id' => 'all', 'label' => 'Tous les Colis', 'color' => 'blue', 'iconPath' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                    ];
                @endphp
                @foreach($tabs as $tab)
                <button @click="setActiveTab('{{$tab['id']}}')" 
                        :class="activeTab === '{{$tab['id']}}' ? 'bg-white text-{{$tab['color']}}-600 shadow-lg border-{{$tab['color']}}-200 scale-105' : 'text-gray-500 hover:text-{{$tab['color']}}-600 hover:bg-{{$tab['color']}}-50'"
                        class="tab-button flex-1 py-3 px-4 font-bold text-xs rounded-xl transition-all duration-300 flex items-center justify-center space-x-2 border border-transparent">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{$tab['iconPath']}}"/></svg>
                    <span>{{$tab['label']}}</span>
                    @if($tab['id'] === 'pending')
                    <span x-show="stats.pending > 0" class="bg-orange-500 text-white text-xs px-2 py-0.5 rounded-full font-bold animate-pulse" x-text="stats.pending"></span>
                    @endif
                </button>
                @endforeach
            </nav>
        </div>

        <div class="bg-gray-50">
            <div x-show="activeTab === 'create'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
                <div class="bg-gradient-to-br from-emerald-50 to-green-100 p-10 text-center">
                    <div class="mx-auto w-24 h-24 bg-gradient-to-br from-emerald-400 to-green-500 rounded-full flex items-center justify-center mb-6 shadow-xl transform hover:scale-110 transition-transform duration-500" style="animation: float 4s ease-in-out infinite">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </div>
                    <h3 class="text-2xl font-extrabold text-gray-900 mb-3">Créer un Nouveau Colis</h3>
                    <p class="text-gray-600 mb-6 max-w-lg mx-auto">🚀 Prêt à expédier ? Cliquez ci-dessous pour démarrer un envoi simple ou rapide.</p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="{{ route('client.packages.create') }}" class="action-btn inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-green-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            Créer Maintenant
                        </a>
                        <a href="{{ route('client.packages.create') }}?fast=true" class="action-btn inline-flex items-center px-6 py-3 border-2 border-emerald-500 text-emerald-700 hover:bg-emerald-100 font-bold rounded-xl shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Mode Rapide ⚡
                        </a>
                    </div>
                </div>
            </div>

            <div x-show="activeTab === 'import'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
                <div class="bg-gradient-to-br from-purple-50 to-indigo-100 p-10 text-center">
                    <div class="mx-auto w-24 h-24 bg-gradient-to-br from-purple-400 to-indigo-500 rounded-full flex items-center justify-center mb-6 shadow-xl transform hover:scale-110 transition-transform duration-500" style="animation: float 4s ease-in-out infinite; animation-delay: 0.5s;">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                    </div>
                    <h3 class="text-2xl font-extrabold text-gray-900 mb-3">Import en Masse via CSV</h3>
                    <p class="text-gray-600 mb-6 max-w-lg mx-auto">📊 Gagnez du temps en important tous vos colis en une seule fois.</p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="{{ route('client.packages.import.csv') }}" class="action-btn inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                            Importer Fichier CSV
                        </a>
                        <a href="{{ route('client.packages.import.template') }}" class="action-btn inline-flex items-center px-6 py-3 border-2 border-purple-500 text-purple-700 hover:bg-purple-100 font-bold rounded-xl shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Télécharger Modèle
                        </a>
                    </div>
                </div>
            </div>
            
            <div x-show="activeTab === 'pending'">
                @if($activeTab === 'pending')
                    @include('client.packages.partials.packages-list', ['packages' => $packages, 'showBulkActions' => true, 'emptyMessage' => 'Aucun colis en attente de pickup', 'emptyIcon' => 'clock'])
                @endif
            </div>

            <div x-show="activeTab === 'all'">
                <div class="bg-white border-b border-gray-200 p-5">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <form method="GET" action="{{ route('client.packages.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                            <input type="hidden" name="tab" value="all">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-1">🔍 Recherche</label>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Code, destinataire..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-300 focus:border-blue-500 transition shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">📊 Statut</label>
                                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-300 focus:border-blue-500 transition shadow-sm">
                                    <option value="">Tous</option>
                                    <option value="CREATED" @selected(request('status') == 'CREATED')>Créés</option>
                                    <option value="PICKED_UP" @selected(request('status') == 'PICKED_UP')>Collectés</option>
                                    <option value="DELIVERED" @selected(request('status') == 'DELIVERED')>Livrés</option>
                                    <option value="RETURNED" @selected(request('status') == 'RETURNED')>Retournés</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">📅 Début</label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-300 focus:border-blue-500 transition shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">📅 Fin</label>
                                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-300 focus:border-blue-500 transition shadow-sm">
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="action-btn w-full px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-md transition">FILTRER</button>
                            </div>
                        </form>
                    </div>
                </div>

                @include('client.packages.partials.packages-list', ['packages' => $packages, 'showBulkActions' => true, 'emptyMessage' => 'Aucun colis trouvé', 'emptyIcon' => 'package'])
            </div>
        </div>
    </div>

    <div x-show="selectedPackages.length > 0" x-transition class="floating-actions fixed bottom-4 left-1/2 transform -translate-x-1/2 rounded-2xl p-3 flex items-center space-x-4 z-50 shadow-2xl">
        <div class="flex items-center text-blue-800 font-bold bg-blue-100 px-4 py-2 rounded-xl shadow-inner">
            <span class="text-lg" x-text="selectedPackages.length"></span> 
            <span class="ml-2 text-sm">sélectionné(s)</span>
        </div>
        <div class="flex items-center space-x-2">
            <button @click="printSelected()" class="action-btn inline-flex items-center px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl text-sm shadow-md">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimer
            </button>
            <button @click="deleteSelected()" :disabled="!canDeleteSelected" :class="canDeleteSelected ? 'bg-red-500 hover:bg-red-600 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed'" class="action-btn inline-flex items-center px-4 py-2 font-bold rounded-xl text-sm shadow-md">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Supprimer
            </button>
            <button @click="clearSelection()" class="action-btn p-2 text-gray-600 hover:bg-gray-200 rounded-xl shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
</div>

{{-- Le script AlpineJS reste identique car il gère la logique fonctionnelle --}}
<script>
function packagesTabsManager() {
    return {
        activeTab: '{{ $activeTab ?? "all" }}',
        selectedPackages: [],
        allSelected: false,
        stats: {
            total: {{ $stats['total'] ?? 0 }},
            pending: {{ $stats['pending'] ?? 0 }},
            in_progress: {{ $stats['in_progress'] ?? 0 }},
            delivered: {{ $stats['delivered'] ?? 0 }},
        },
        init() { /* ... (init logic) ... */ },
        setActiveTab(tab) {
            this.activeTab = tab;
            this.selectedPackages = [];
            this.allSelected = false;
            
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            history.pushState({}, '', url);

            if (tab === 'pending' || (tab === 'all' && '{{ !request()->has("search") && !request()->has("status") }}')) {
                window.location.href = url.toString();
            }
        },
        get canDeleteSelected() {
            if (this.selectedPackages.length === 0) return false;
            const checkboxes = document.querySelectorAll('input[type="checkbox"][x-model="selectedPackages"]:checked');
            return Array.from(checkboxes).every(cb => ['CREATED', 'AVAILABLE'].includes(cb.dataset.status));
        },
        toggleSelectAll() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"][x-model="selectedPackages"]');
            this.allSelected = !this.allSelected;
            if (this.allSelected) {
                this.selectedPackages = Array.from(checkboxes).map(cb => cb.value);
            } else {
                this.selectedPackages = [];
            }
        },
        clearSelection() { this.selectedPackages = []; this.allSelected = false; },
        printSelected() {
            if (this.selectedPackages.length === 0) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("client.packages.print.multiple") }}';
            form.target = '_blank';
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden'; csrfToken.name = '_token'; csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            this.selectedPackages.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = 'package_ids[]'; input.value = id;
                form.appendChild(input);
            });
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        },
        async deleteSelected() {
            if (this.selectedPackages.length === 0 || !this.canDeleteSelected) return;
            if (!confirm(`Confirmez-vous la suppression de ${this.selectedPackages.length} colis ?`)) return;
            try {
                const response = await fetch('{{ route("client.packages.bulk.destroy") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ package_ids: this.selectedPackages })
                });
                const data = await response.json();
                if (data.success) {
                    alert(`${data.deleted_count} colis supprimés !`);
                    window.location.reload();
                } else {
                    alert(`Erreur: ${data.message}`);
                }
            } catch (error) {
                alert('Erreur de connexion.');
            }
        },
    }
}
// Les fonctions globales (deletePackage, toggleSelectAll) peuvent être simplifiées ou gérées directement par Alpine
</script>
@endsection