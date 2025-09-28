@extends('layouts.client')

@section('title', 'Créer un Manifeste')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- En-tête -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Créer un Manifeste</h1>
            <p class="text-gray-600 mt-1">Sélectionnez les colis pour créer un manifeste de collecte</p>
        </div>
        <a href="{{ route('client.manifests.index') }}"
           class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Retour
        </a>
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
            <p class="text-gray-600 mb-4">Vous n'avez actuellement aucun colis disponible pour créer un manifeste</p>
            <a href="{{ route('client.packages.create') }}" class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Créer un nouveau colis
            </a>
        </div>
    @else
        <!-- Formulaire de création -->
        <form id="manifest-creation-form">
            @csrf
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <!-- Sélection des colis -->
                <div class="xl:col-span-2">
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Colis Disponibles</h3>
                            <p class="text-sm text-gray-600 mt-1">Sélectionnez les colis à inclure dans le manifeste</p>
                        </div>
                        <div class="p-6">
                            @foreach($packagesByPickup as $pickupKey => $packages)
                                <div class="mb-8 last:mb-0">
                                    <!-- En-tête du groupe -->
                                    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg p-4 mb-4">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <input type="checkbox"
                                                       id="group_{{ $loop->index }}"
                                                       class="group-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3"
                                                       onchange="toggleGroup('{{ $pickupKey }}', this.checked)">
                                                <div>
                                                    <h4 class="font-medium text-gray-900">{{ explode(' | ', $pickupKey)[0] }}</h4>
                                                    <p class="text-sm text-gray-600">{{ explode(' | ', $pickupKey)[1] ?? 'Téléphone non spécifié' }}</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm font-medium text-gray-900">{{ $packages->count() }} colis</div>
                                                <div class="text-xs text-gray-500">{{ number_format($packages->sum('weight'), 1) }} kg</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Liste des colis -->
                                    <div class="grid grid-cols-1 gap-3">
                                        @foreach($packages as $package)
                                            <div class="package-item border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors duration-200"
                                                 data-group="{{ $pickupKey }}">
                                                <div class="flex items-center">
                                                    <input type="checkbox"
                                                           name="package_ids[]"
                                                           value="{{ $package->id }}"
                                                           data-group="{{ $pickupKey }}"
                                                           class="package-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-4"
                                                           onchange="updateSelection()">
                                                    <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-4">
                                                        <div>
                                                            <div class="font-medium text-gray-900">{{ $package->tracking_number }}</div>
                                                            <div class="text-sm text-gray-500">{{ $package->status === 'AVAILABLE' ? 'Disponible' : 'Créé' }}</div>
                                                        </div>
                                                        <div>
                                                            <div class="text-sm text-gray-900">{{ $package->recipient_name }}</div>
                                                            <div class="text-xs text-gray-500">{{ $package->recipient_phone }}</div>
                                                        </div>
                                                        <div>
                                                            <div class="text-sm text-gray-900">{{ number_format($package->weight, 1) }} kg</div>
                                                            <div class="text-xs text-gray-500">{{ number_format($package->declared_value, 2) }} TND</div>
                                                        </div>
                                                        <div class="text-right">
                                                            @if($package->cod_amount > 0)
                                                                <div class="text-sm font-medium text-green-600">COD: {{ number_format($package->cod_amount, 2) }} TND</div>
                                                            @else
                                                                <div class="text-sm text-gray-400">Pas de COD</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Panneau de création -->
                <div class="xl:col-span-1">
                    <div class="bg-white rounded-lg shadow-lg sticky top-6">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Informations du Manifeste</h3>
                        </div>
                        <div class="p-6">
                            <!-- Résumé -->
                            <div class="bg-indigo-50 rounded-lg p-4 mb-6">
                                <h4 class="font-medium text-indigo-900 mb-3">Résumé</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-indigo-700">Colis sélectionnés:</span>
                                        <span class="font-medium text-indigo-900" id="selected-count">0</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-indigo-700">Poids total:</span>
                                        <span class="font-medium text-indigo-900" id="total-weight">0 kg</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-indigo-700">COD total:</span>
                                        <span class="font-medium text-indigo-900" id="total-cod">0 TND</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Informations de collecte -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse de collecte *</label>
                                    <input type="text" name="pickup_address" id="pickup_address" required
                                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact *</label>
                                    <input type="text" name="pickup_contact" required
                                           value="{{ auth()->user()->name }}"
                                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone *</label>
                                    <input type="text" name="pickup_phone" id="pickup_phone" required
                                           value="{{ auth()->user()->phone ?? '' }}"
                                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de collecte</label>
                                    <input type="date" name="delivery_date"
                                           min="{{ date('Y-m-d') }}"
                                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                    <textarea name="notes" rows="3"
                                              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                              placeholder="Instructions spéciales..."></textarea>
                                </div>
                            </div>

                            <!-- Boutons -->
                            <div class="space-y-3 mt-6">
                                <button type="submit" id="generate-btn" disabled
                                        class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Générer le Manifeste
                                </button>
                                <button type="button" onclick="previewSelection()" id="preview-btn" disabled
                                        class="w-full bg-gray-600 hover:bg-gray-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                    👁️ Aperçu
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif
</div>

<script>
let selectedPackages = new Set();
let packageData = @json($packagesByPickup->flatten());

function toggleGroup(groupKey, checked) {
    const checkboxes = document.querySelectorAll(`input[data-group="${groupKey}"].package-checkbox`);
    checkboxes.forEach(checkbox => {
        checkbox.checked = checked;
        const packageId = parseInt(checkbox.value);
        if (checked) {
            selectedPackages.add(packageId);
        } else {
            selectedPackages.delete(packageId);
        }
    });

    updateSelection();
    updatePickupInfo(groupKey);
}

function updateSelection() {
    // Mettre à jour l'ensemble des packages sélectionnés
    selectedPackages.clear();
    document.querySelectorAll('.package-checkbox:checked').forEach(checkbox => {
        selectedPackages.add(parseInt(checkbox.value));
    });

    // Calculer les statistiques
    let totalWeight = 0;
    let totalCod = 0;

    packageData.forEach(pkg => {
        if (selectedPackages.has(pkg.id)) {
            totalWeight += parseFloat(pkg.weight || 0);
            totalCod += parseFloat(pkg.cod_amount || 0);
        }
    });

    // Mettre à jour l'affichage
    document.getElementById('selected-count').textContent = selectedPackages.size;
    document.getElementById('total-weight').textContent = totalWeight.toFixed(1) + ' kg';
    document.getElementById('total-cod').textContent = totalCod.toFixed(2) + ' TND';

    // Activer/désactiver les boutons
    const hasSelection = selectedPackages.size > 0;
    document.getElementById('generate-btn').disabled = !hasSelection;
    document.getElementById('preview-btn').disabled = !hasSelection;

    // Mettre à jour les cases de groupe
    updateGroupCheckboxes();
}

function updateGroupCheckboxes() {
    document.querySelectorAll('.group-checkbox').forEach((groupCheckbox, index) => {
        const groupKey = Object.keys(@json($packagesByPickup))[index];
        const groupPackages = document.querySelectorAll(`input[data-group="${groupKey}"].package-checkbox`);

        const checkedCount = Array.from(groupPackages).filter(cb => cb.checked).length;
        const totalCount = groupPackages.length;

        if (checkedCount === 0) {
            groupCheckbox.checked = false;
            groupCheckbox.indeterminate = false;
        } else if (checkedCount === totalCount) {
            groupCheckbox.checked = true;
            groupCheckbox.indeterminate = false;
        } else {
            groupCheckbox.checked = false;
            groupCheckbox.indeterminate = true;
        }
    });
}

function updatePickupInfo(groupKey) {
    const [address, phone] = groupKey.split(' | ');
    if (address && address !== 'Adresse non définie') {
        document.getElementById('pickup_address').value = address;
    }
    if (phone) {
        document.getElementById('pickup_phone').value = phone;
    }
}

async function previewSelection() {
    if (selectedPackages.size === 0) {
        alert('Veuillez sélectionner au moins un colis');
        return;
    }

    try {
        const response = await fetch('{{ route("client.manifests.preview") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ package_ids: Array.from(selectedPackages) })
        });

        const data = await response.json();

        if (data.success) {
            // Afficher la modal d'aperçu
            showPreviewModal(data.packages, data.summary);
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors du chargement de l\'aperçu');
    }
}

function showPreviewModal(packages, summary) {
    // Créer et afficher une modal d'aperçu
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
    modal.innerHTML = `
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Aperçu du Manifeste</h3>
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="max-h-96 overflow-y-auto">
                <div class="grid grid-cols-4 gap-4 mb-4 p-4 bg-indigo-50 rounded">
                    <div class="text-center">
                        <div class="text-xl font-bold text-indigo-600">${summary.total_packages}</div>
                        <div class="text-sm text-gray-600">Colis</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-indigo-600">${summary.total_weight} kg</div>
                        <div class="text-sm text-gray-600">Poids</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-indigo-600">${summary.total_value.toFixed(2)} TND</div>
                        <div class="text-sm text-gray-600">Valeur</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-indigo-600">${summary.total_cod.toFixed(2)} TND</div>
                        <div class="text-sm text-gray-600">COD</div>
                    </div>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-2 py-2 text-left">Numéro</th>
                            <th class="px-2 py-2 text-left">Destinataire</th>
                            <th class="px-2 py-2 text-right">Poids</th>
                            <th class="px-2 py-2 text-right">COD</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${packages.map(pkg => `
                            <tr class="border-b">
                                <td class="px-2 py-2">${pkg.tracking_number}</td>
                                <td class="px-2 py-2">${pkg.recipient_name}</td>
                                <td class="px-2 py-2 text-right">${pkg.weight} kg</td>
                                <td class="px-2 py-2 text-right">${pkg.cod_amount > 0 ? pkg.cod_amount.toFixed(2) + ' TND' : '-'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

// Gestion du formulaire
document.getElementById('manifest-creation-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (selectedPackages.size === 0) {
        alert('Veuillez sélectionner au moins un colis');
        return;
    }

    const formData = new FormData(this);

    // Ajouter les IDs des packages sélectionnés
    selectedPackages.forEach(id => {
        formData.append('package_ids[]', id);
    });

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

            // Rediriger vers la liste
            setTimeout(() => {
                window.location.href = '{{ route("client.manifests.index") }}';
            }, 1000);
        } else {
            alert('Erreur lors de la génération du manifeste');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la génération du manifeste');
    }
});

// Écouter les changements de sélection
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('package-checkbox')) {
        updateSelection();
    }
});
</script>
@endsection