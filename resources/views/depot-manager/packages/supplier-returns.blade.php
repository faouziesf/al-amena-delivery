@extends('layouts.depot-manager')

@section('title', 'Retours Fournisseur')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-red-50">
    <!-- Header moderne -->
    <div class="bg-white shadow-lg border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-red-600 to-red-700 rounded-2xl flex items-center justify-center text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Retours Fournisseur - Mon Dépôt</h1>
                        <p class="text-slate-500 text-sm">Colis de votre dépôt à retourner aux fournisseurs</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('depot-manager.packages.batch-scanner') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-xl transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h4"/>
                        </svg>
                        Scanner Lot
                    </a>
                    <button onclick="printSelectedReturns()"
                            class="inline-flex items-center px-4 py-2 bg-green-100 hover:bg-green-200 text-green-700 rounded-xl transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Imprimer Sélection
                    </button>
                    <button onclick="refreshPackages()"
                            class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Actualiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Dashboard de statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-red-100 text-sm font-medium">Total Retours</p>
                            <p class="text-white text-2xl font-bold">{{ number_format($stats['total_returns']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-orange-100 text-sm font-medium">Non Traités</p>
                            <p class="text-white text-2xl font-bold">{{ number_format($stats['unprocessed']) }}</p>
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
                            <p class="text-green-100 text-sm font-medium">Traités Aujourd'hui</p>
                            <p class="text-white text-2xl font-bold">{{ number_format($stats['processed_today']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-blue-100 text-sm font-medium">Retours Aujourd'hui</p>
                            <p class="text-white text-2xl font-bold">{{ number_format($stats['returns_today']) }}</p>
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
                    <select name="processed" class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        <option value="">Tous les états</option>
                        <option value="no" {{ request('processed') == 'no' ? 'selected' : '' }}>Non traités</option>
                        <option value="yes" {{ request('processed') == 'yes' ? 'selected' : '' }}>Traités</option>
                    </select>

                    <select name="gouvernorat" class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
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

                    <input type="text" name="search" placeholder="Rechercher code colis ou expéditeur..."
                           value="{{ request('search') }}"
                           class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">

                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">

                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">

                    <button type="submit"
                            class="inline-flex items-center px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Rechercher
                    </button>

                    @if(request()->hasAny(['processed', 'gouvernorat', 'search', 'date_from', 'date_to']))
                    <a href="{{ route('depot-manager.packages.supplier-returns') }}"
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

        <!-- Liste des colis retournés -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
            <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-4 border-b border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Colis à Retourner aux Fournisseurs</h3>
                        <p class="text-slate-500 text-sm">{{ $packages->total() }} colis retourné(s) au total</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" id="selectAll" class="rounded text-red-600 focus:ring-red-500">
                        <label for="selectAll" class="text-sm font-medium text-slate-700">Tout sélectionner</label>
                    </div>
                </div>
            </div>

            @if($packages->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAllHeader" class="rounded text-red-600 focus:ring-red-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Code Colis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Expéditeur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Adresse Pickup</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date Retour</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">État</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($packages as $package)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" name="selected_packages[]" value="{{ $package->id }}"
                                       class="package-checkbox rounded text-red-600 focus:ring-red-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-red-600">{{ $package->package_code }}</div>
                                @if($package->cod_amount > 0)
                                    <div class="text-xs text-slate-500">COD: {{ number_format($package->cod_amount, 3) }} DT</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($package->sender)
                                    <div class="text-sm font-medium text-slate-900">
                                        {{ $package->sender->first_name }} {{ $package->sender->last_name }}
                                    </div>
                                    <div class="text-sm text-slate-500">{{ $package->sender->phone ?? 'N/A' }}</div>
                                @else
                                    <span class="text-sm text-slate-400">Expéditeur non trouvé</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-900">{{ $package->pickup_address ?? 'N/A' }}</div>
                                @if($package->pickup_phone)
                                    <div class="text-xs text-slate-500">{{ $package->pickup_phone }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                {{ $package->returned_at ? $package->returned_at->format('d/m/Y H:i') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($package->return_processed_at)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Traité
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        En attente
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('depot-manager.packages.show', $package) }}"
                                       class="text-indigo-600 hover:text-indigo-900">Voir</a>
                                    <a href="{{ route('depot-manager.packages.return-receipt', $package) }}"
                                       class="text-green-600 hover:text-green-900"
                                       target="_blank">Bon</a>
                                    @if(!$package->return_processed_at)
                                        <button onclick="markAsProcessed({{ $package->id }})"
                                                class="text-orange-600 hover:text-orange-900">Traiter</button>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <h3 class="text-lg font-medium text-slate-900 mb-2">Aucun colis retourné trouvé</h3>
                <p class="text-slate-500">Il n'y a pas de colis retournés correspondant à vos critères actuels.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function refreshPackages() {
    window.location.reload();
}

// Gestion de la sélection multiple
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const selectAllHeader = document.getElementById('selectAllHeader');
    const packageCheckboxes = document.querySelectorAll('.package-checkbox');

    // Synchroniser les deux checkboxes "tout sélectionner"
    function syncSelectAll() {
        const checkedCount = document.querySelectorAll('.package-checkbox:checked').length;
        const allChecked = checkedCount === packageCheckboxes.length && packageCheckboxes.length > 0;

        selectAll.checked = allChecked;
        selectAllHeader.checked = allChecked;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < packageCheckboxes.length;
        selectAllHeader.indeterminate = checkedCount > 0 && checkedCount < packageCheckboxes.length;
    }

    // Événements pour "tout sélectionner"
    [selectAll, selectAllHeader].forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            packageCheckboxes.forEach(cb => cb.checked = this.checked);
            syncSelectAll();
        });
    });

    // Événements pour les checkboxes individuelles
    packageCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', syncSelectAll);
    });
});

function printSelectedReturns() {
    const selectedPackages = Array.from(document.querySelectorAll('.package-checkbox:checked'))
                                  .map(cb => cb.value);

    if (selectedPackages.length === 0) {
        alert('Veuillez sélectionner au moins un colis.');
        return;
    }

    // Créer un formulaire pour envoyer les IDs sélectionnés
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("depot-manager.packages.print-batch-returns") }}';
    form.target = '_blank';

    // Ajouter le token CSRF
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);

    // Ajouter les IDs des packages sélectionnés
    selectedPackages.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'package_ids[]';
        input.value = id;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function markAsProcessed(packageId) {
    if (!confirm('Marquer ce colis comme traité ?')) {
        return;
    }

    fetch(`/depot-manager/packages/${packageId}/process-return`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            return_reason: 'Marqué comme traité par le chef dépôt',
            return_action: 'return_to_sender'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success || response.ok) {
            showNotification('Colis marqué comme traité avec succès', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification('Erreur lors du traitement', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erreur lors du traitement', 'error');
    });
}

// Notifications
@if(session('success'))
    document.addEventListener('DOMContentLoaded', function() {
        showNotification("{{ session('success') }}", 'success');
    });
@endif

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