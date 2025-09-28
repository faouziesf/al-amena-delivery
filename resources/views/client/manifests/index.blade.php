@extends('layouts.client')

@section('title', 'Manifestes - Gestion des Colis')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- En-tête -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestion des Manifestes</h1>
            <p class="text-gray-600 mt-1">Créez des manifestes pour vos colis en attente de collecte</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="refreshPackages()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Actualiser
            </button>
        </div>
    </div>

    @if($packagesByPickup->count() == 0)
        <!-- Aucun colis disponible -->
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8l-4 4-4-4m-6 4l4 4 4-4"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Aucun colis disponible</h3>
            <p class="text-gray-600 mb-4">Vous n'avez actuellement aucun colis avec le statut "Disponible" ou "Créé"</p>
            <a href="{{ route('client.packages.create') }}" class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Créer un nouveau colis
            </a>
        </div>
    @else
        <!-- Liste des colis groupés par adresse -->
        <div class="space-y-6">
            @foreach($packagesByPickup as $pickupKey => $packages)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <!-- En-tête du groupe -->
                    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-indigo-100 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ explode(' | ', $pickupKey)[0] }}
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        {{ explode(' | ', $pickupKey)[1] ?? 'Téléphone non spécifié' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900">{{ $packages->count() }} colis</div>
                                    <div class="text-xs text-gray-500">{{ number_format($packages->sum('weight'), 1) }} kg</div>
                                </div>
                                <button onclick="createManifest('{{ $pickupKey }}', {{ $packages->pluck('id') }})"
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    Créer Manifeste
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau des colis -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <input type="checkbox"
                                               onchange="toggleGroupSelection('{{ $pickupKey }}', this.checked)"
                                               class="group-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Numéro de suivi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destinataire</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adresse de livraison</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Poids</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valeur</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">COD</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($packages as $package)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="checkbox"
                                                   data-group="{{ $pickupKey }}"
                                                   data-package-id="{{ $package->id }}"
                                                   class="package-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $package->tracking_number }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $package->recipient_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $package->recipient_phone }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ Str::limit($package->recipient_address, 40) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($package->weight, 1) }} kg
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($package->declared_value, 2) }} TND
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($package->cod_amount > 0)
                                                <span class="text-green-600 font-medium">{{ number_format($package->cod_amount, 2) }} TND</span>
                                            @else
                                                <span class="text-gray-400">Aucun</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                                {{ $package->status === 'AVAILABLE' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ $package->status === 'AVAILABLE' ? 'Disponible' : 'Créé' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Actions flottantes -->
        <div class="fixed bottom-6 right-6">
            <button onclick="createSelectedManifest()"
                    id="create-selected-btn"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-full shadow-lg transition-all duration-200 transform scale-0"
                    style="display: none;">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Créer Manifeste (<span id="selected-count">0</span>)
            </button>
        </div>
    @endif
</div>

<!-- Modal de création de manifeste -->
<div id="manifest-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Créer un Manifeste</h3>
                <button onclick="closeManifestModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="manifest-form" onsubmit="generateManifest(event)">
                <div class="space-y-4">
                    <!-- Informations de collecte -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900 mb-3">Informations de Collecte</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Adresse de collecte *</label>
                                <input type="text" name="pickup_address" id="pickup_address" required
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contact *</label>
                                <input type="text" name="pickup_contact" required
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone *</label>
                                <input type="text" name="pickup_phone" id="pickup_phone" required
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date de collecte</label>
                                <input type="date" name="delivery_date"
                                       min="{{ date('Y-m-d') }}"
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optionnel)</label>
                        <textarea name="notes" rows="3"
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                  placeholder="Instructions spéciales, horaires de collecte, etc."></textarea>
                    </div>

                    <!-- Résumé des colis -->
                    <div class="bg-indigo-50 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900 mb-3">Résumé des Colis</h4>
                        <div id="manifest-summary"></div>
                    </div>
                </div>

                <input type="hidden" name="package_ids" id="selected_package_ids">

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeManifestModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors duration-200">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors duration-200">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Générer le Manifeste
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let selectedPackages = new Set();

// Gestion des sélections
function toggleGroupSelection(groupKey, checked) {
    const checkboxes = document.querySelectorAll(`input[data-group="${groupKey}"]`);
    checkboxes.forEach(checkbox => {
        checkbox.checked = checked;
        if (checked) {
            selectedPackages.add(parseInt(checkbox.dataset.packageId));
        } else {
            selectedPackages.delete(parseInt(checkbox.dataset.packageId));
        }
    });
    updateSelectedCount();
}

// Écouter les changements de sélection individuelle
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('package-checkbox')) {
        const packageId = parseInt(e.target.dataset.packageId);
        if (e.target.checked) {
            selectedPackages.add(packageId);
        } else {
            selectedPackages.delete(packageId);
        }

        // Vérifier si tous les colis du groupe sont sélectionnés
        const groupKey = e.target.dataset.group;
        const groupCheckboxes = document.querySelectorAll(`input[data-group="${groupKey}"]`);
        const groupCheckbox = document.querySelector(`input[onchange*="${groupKey}"]`);

        if (groupCheckbox) {
            const allChecked = Array.from(groupCheckboxes).every(cb => cb.checked);
            const noneChecked = Array.from(groupCheckboxes).every(cb => !cb.checked);

            if (allChecked) {
                groupCheckbox.checked = true;
                groupCheckbox.indeterminate = false;
            } else if (noneChecked) {
                groupCheckbox.checked = false;
                groupCheckbox.indeterminate = false;
            } else {
                groupCheckbox.checked = false;
                groupCheckbox.indeterminate = true;
            }
        }

        updateSelectedCount();
    }
});

function updateSelectedCount() {
    const count = selectedPackages.size;
    const btn = document.getElementById('create-selected-btn');
    const countSpan = document.getElementById('selected-count');

    if (count > 0) {
        btn.style.display = 'block';
        btn.classList.remove('scale-0');
        btn.classList.add('scale-100');
        countSpan.textContent = count;
    } else {
        btn.classList.remove('scale-100');
        btn.classList.add('scale-0');
        setTimeout(() => {
            if (selectedPackages.size === 0) {
                btn.style.display = 'none';
            }
        }, 200);
    }
}

function createManifest(pickupKey, packageIds) {
    const [address, phone] = pickupKey.split(' | ');

    document.getElementById('pickup_address').value = address;
    document.getElementById('pickup_phone').value = phone || '';
    document.getElementById('selected_package_ids').value = JSON.stringify(packageIds);

    // Charger l'aperçu
    loadManifestPreview(packageIds);

    document.getElementById('manifest-modal').classList.remove('hidden');
}

function createSelectedManifest() {
    if (selectedPackages.size === 0) {
        alert('Veuillez sélectionner au moins un colis');
        return;
    }

    const packageIds = Array.from(selectedPackages);
    document.getElementById('selected_package_ids').value = JSON.stringify(packageIds);

    // Réinitialiser les champs
    document.getElementById('pickup_address').value = '';
    document.getElementById('pickup_phone').value = '';

    // Charger l'aperçu
    loadManifestPreview(packageIds);

    document.getElementById('manifest-modal').classList.remove('hidden');
}

function closeManifestModal() {
    document.getElementById('manifest-modal').classList.add('hidden');
}

async function loadManifestPreview(packageIds) {
    try {
        const response = await fetch('{{ route("client.manifests.preview") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ package_ids: packageIds })
        });

        const data = await response.json();

        if (data.success) {
            updateManifestSummary(data.packages, data.summary);
        }
    } catch (error) {
        console.error('Erreur lors du chargement de l\'aperçu:', error);
    }
}

function updateManifestSummary(packages, summary) {
    const summaryDiv = document.getElementById('manifest-summary');

    summaryDiv.innerHTML = `
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-indigo-600">${summary.total_packages}</div>
                <div class="text-sm text-gray-600">Colis</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-indigo-600">${summary.total_weight} kg</div>
                <div class="text-sm text-gray-600">Poids Total</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-indigo-600">${summary.total_value.toFixed(2)} TND</div>
                <div class="text-sm text-gray-600">Valeur Déclarée</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-indigo-600">${summary.total_cod.toFixed(2)} TND</div>
                <div class="text-sm text-gray-600">COD Total</div>
            </div>
        </div>
        <div class="max-h-40 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-2 py-1 text-left">Numéro</th>
                        <th class="px-2 py-1 text-left">Destinataire</th>
                        <th class="px-2 py-1 text-right">Poids</th>
                        <th class="px-2 py-1 text-right">COD</th>
                    </tr>
                </thead>
                <tbody>
                    ${packages.map(pkg => `
                        <tr class="border-b">
                            <td class="px-2 py-1">${pkg.tracking_number}</td>
                            <td class="px-2 py-1">${pkg.recipient_name}</td>
                            <td class="px-2 py-1 text-right">${pkg.weight} kg</td>
                            <td class="px-2 py-1 text-right">${pkg.cod_amount > 0 ? pkg.cod_amount.toFixed(2) + ' TND' : '-'}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

async function generateManifest(event) {
    event.preventDefault();

    const formData = new FormData(event.target);

    try {
        const response = await fetch('{{ route("client.manifests.generate") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });

        if (response.ok) {
            // Télécharger le PDF
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `manifeste-${new Date().toISOString().slice(0, 10)}.pdf`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);

            // Fermer le modal et actualiser
            closeManifestModal();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            alert('Erreur lors de la génération du manifeste');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la génération du manifeste');
    }
}

function refreshPackages() {
    window.location.reload();
}
</script>
@endsection