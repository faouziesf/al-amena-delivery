@extends('layouts.deliverer')

@section('title', 'Livraison Rapide')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header simple -->
    <div class="bg-white shadow-sm sticky top-0 z-10">
        <div class="px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <button onclick="window.history.back()"
                            class="p-2 hover:bg-gray-100 rounded-lg">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">🚚 Livraison Rapide</h1>
                        <p class="text-sm text-gray-600">{{ count($packages) }} colis à livrer</p>
                    </div>
                </div>

                @if(count($packages) > 1)
                <div class="flex space-x-2">
                    <button onclick="previousPackage()" id="prevBtn"
                            class="p-2 bg-gray-100 text-gray-600 rounded-lg disabled:opacity-40">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <span id="packageCounter" class="flex items-center text-sm text-gray-600 px-3">1 / {{ count($packages) }}</span>
                    <button onclick="nextPackage()" id="nextBtn"
                            class="p-2 bg-gray-100 text-gray-600 rounded-lg disabled:opacity-40">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
                @endif
            </div>

            @if(count($packages) > 1)
            <!-- Progress bar -->
            <div class="w-full bg-gray-200 rounded-full h-2 mt-4">
                <div id="progressBar" class="bg-green-500 h-2 rounded-full transition-all duration-300" style="width: {{ 100 / count($packages) }}%"></div>
            </div>
            @endif
        </div>
    </div>

    @if(count($packages) == 0)
    <!-- État vide -->
    <div class="text-center py-16 px-4">
        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-3">Toutes les livraisons terminées !</h3>
        <p class="text-gray-600 mb-6">Excellent travail ! Plus de colis à livrer.</p>
        <a href="{{ route('deliverer.dashboard') }}"
           class="bg-green-500 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-600">
            🏠 Retour au Dashboard
        </a>
    </div>
    @else

    <!-- Colis actuel -->
    <div class="p-4">
        @foreach($packages as $index => $package)
        <div class="package-card bg-white rounded-lg shadow-sm border p-4 mb-4 {{ $index === 0 ? '' : 'hidden' }}"
             data-package-index="{{ $index }}">

            <!-- Header du colis -->
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $package->package_code }}</h2>
                    <div class="flex items-center space-x-2 mt-1">
                        @if($package->delivery_attempts >= 3)
                            <span class="bg-red-100 text-red-700 px-2 py-1 text-xs rounded-full">
                                🚨 URGENT - Tentative {{ $package->delivery_attempts + 1 }}/3
                            </span>
                        @elseif($package->delivery_attempts > 0)
                            <span class="bg-yellow-100 text-yellow-700 px-2 py-1 text-xs rounded-full">
                                🔄 Tentative {{ $package->delivery_attempts + 1 }}/3
                            </span>
                        @endif

                        @if($package->cod_amount >= 100)
                            <span class="bg-green-100 text-green-700 px-2 py-1 text-xs rounded-full">
                                💰 Valeur élevée
                            </span>
                        @endif
                    </div>
                </div>

                <div class="text-right">
                    <div class="text-2xl font-bold text-green-600">
                        {{ number_format($package->cod_amount, 3) }} DT
                    </div>
                    <div class="text-xs text-gray-500">COD à collecter</div>
                </div>
            </div>

            <!-- Destinataire -->
            <div class="bg-blue-50 p-4 rounded-lg mb-4">
                <h3 class="font-semibold text-blue-900 mb-3">🎯 Destinataire</h3>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-blue-900">{{ $package->recipient_data['name'] ?? 'N/A' }}</p>
                            <p class="text-sm text-blue-700">📞 {{ $package->recipient_data['phone'] ?? 'N/A' }}</p>
                        </div>

                        @if($package->recipient_data['phone'] ?? null)
                        <a href="tel:{{ $package->recipient_data['phone'] }}"
                           class="bg-green-500 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-green-600">
                            📞 Appeler
                        </a>
                        @endif
                    </div>

                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm text-blue-700">📍 {{ $package->recipient_data['address'] ?? 'N/A' }}</p>
                            <p class="text-sm text-blue-600 font-medium mt-1">{{ $package->delegationTo->name ?? 'N/A' }}</p>
                        </div>

                        <button onclick="openGPS('{{ $package->recipient_data['address'] ?? '' }}', '{{ $package->delegationTo->name ?? '' }}')"
                                class="bg-blue-500 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-blue-600 ml-2">
                            🗺️ GPS
                        </button>
                    </div>
                </div>
            </div>

            <!-- Détails colis -->
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <h4 class="font-semibold text-gray-700 mb-2">📦 Détails du colis</h4>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="text-gray-500">Contenu:</span>
                        <p class="font-medium">{{ $package->content_description ?? 'Non spécifié' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Expéditeur:</span>
                        <p class="font-medium">{{ $package->sender->name ?? 'N/A' }}</p>
                    </div>
                </div>

                @if($package->delivery_attempts > 0 && $package->unavailable_notes)
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <p class="text-xs text-gray-600">
                        <strong>Dernière tentative:</strong> {{ $package->unavailable_notes }}
                    </p>
                </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="space-y-3">
                <!-- Livrer -->
                @if($package->cod_amount == 0)
                    <!-- Colis gratuit - livraison directe -->
                    <form method="POST" action="{{ route('deliverer.packages.deliver', $package) }}" class="free-delivery-form">
                        @csrf
                        <input type="hidden" name="cod_collected" value="0">
                        <input type="hidden" name="recipient_name" value="{{ $package->recipient_data['name'] ?? 'Destinataire' }}">
                        <input type="hidden" name="print_receipt" value="1">

                        <button type="submit"
                                onclick="return confirm('Confirmer la livraison de ce colis gratuit ? Vous serez redirigé vers le reçu d\'impression.')"
                                class="w-full bg-green-500 hover:bg-green-600 text-white py-4 px-4 rounded-lg font-bold text-lg">
                            ✅ LIVRER + IMPRIMER REÇU
                        </button>
                    </form>
                @else
                    <!-- Colis avec COD -->
                    <button onclick="showDeliveryModal({{ $package->id }}, '{{ $package->package_code }}', '{{ $package->recipient_data['name'] ?? 'Destinataire' }}', {{ $package->cod_amount }})"
                            class="w-full bg-green-500 hover:bg-green-600 text-white py-4 px-4 rounded-lg font-bold text-lg">
                        ✅ LIVRER - {{ number_format($package->cod_amount, 3) }} DT
                    </button>
                @endif

                <div class="grid grid-cols-2 gap-3">
                    <!-- Indisponible -->
                    <button onclick="markUnavailable({{ $package->id }}, '{{ $package->package_code }}')"
                            class="bg-orange-500 hover:bg-orange-600 text-white py-3 px-4 rounded-lg font-semibold">
                        ⏰ INDISPONIBLE
                    </button>

                    <!-- Voir détails -->
                    <a href="{{ route('deliverer.packages.show', $package) }}"
                       class="bg-gray-600 hover:bg-gray-700 text-white py-3 px-4 rounded-lg font-semibold text-center">
                        👁️ DÉTAILS
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<!-- Modal Livraison Simplifiée -->
<div id="deliveryModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Confirmer la Livraison</h3>
                <button onclick="closeDeliveryModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="deliveryForm" method="POST">
                @csrf

                <div id="deliveryInfo" class="mb-4 bg-green-50 p-4 rounded-lg">
                    <!-- Info dynamique du colis -->
                </div>

                <!-- Le COD est prérempli automatiquement, pas de saisie manuelle -->
                <input type="hidden" id="codCollected" name="cod_collected">
                <input type="hidden" id="recipientName" name="recipient_name">

                <!-- Option d'impression du reçu -->
                <div class="mb-4">
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="print_receipt" value="1" checked
                               class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <span class="text-sm font-medium text-gray-700">📄 Ouvrir le reçu de livraison après confirmation</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1">Le reçu s'ouvrira automatiquement dans un nouvel onglet pour impression</p>
                </div>

                <!-- Notes optionnelles -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes de livraison (optionnel)</label>
                    <textarea name="delivery_notes" rows="3"
                              placeholder="Commentaires sur la livraison..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"></textarea>
                </div>

                <div class="flex space-x-3">
                    <button type="submit"
                            class="flex-1 bg-green-500 hover:bg-green-600 text-white py-3 px-4 rounded-lg font-semibold">
                        ✅ Confirmer la Livraison
                    </button>
                    <button type="button" onclick="closeDeliveryModal()"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 px-4 rounded-lg font-semibold">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Indisponible -->
<div id="unavailableModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <h3 class="text-lg font-bold mb-4">Client Indisponible</h3>

            <form id="unavailableForm" method="POST">
                @csrf

                <div id="unavailableInfo" class="mb-4 bg-orange-50 p-4 rounded-lg">
                    <!-- Info dynamique du colis -->
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Raison *</label>
                    <select name="reason" required class="w-full p-2 border rounded-lg">
                        <option value="">Sélectionner...</option>
                        <option value="CLIENT_ABSENT">Client absent</option>
                        <option value="ADDRESS_NOT_FOUND">Adresse introuvable</option>
                        <option value="CLIENT_REFUSES">Client refuse</option>
                        <option value="PHONE_OFF">Téléphone éteint</option>
                        <option value="OTHER">Autre</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Détails *</label>
                    <textarea name="attempt_notes" required rows="3"
                              placeholder="Décrivez ce qui s'est passé..."
                              class="w-full p-2 border rounded-lg"></textarea>
                </div>

                <div class="flex space-x-3">
                    <button type="submit"
                            class="flex-1 bg-orange-500 hover:bg-orange-600 text-white py-3 px-4 rounded-lg font-semibold">
                        ⏰ Enregistrer Tentative
                    </button>
                    <button type="button" onclick="closeUnavailableModal()"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 px-4 rounded-lg font-semibold">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let packages = @json($packages);
let currentIndex = 0;

function updatePackageDisplay() {
    // Masquer tous les colis
    document.querySelectorAll('.package-card').forEach(card => {
        card.classList.add('hidden');
    });

    // Afficher le colis actuel
    if (packages.length > 0) {
        const currentCard = document.querySelector(`[data-package-index="${currentIndex}"]`);
        if (currentCard) {
            currentCard.classList.remove('hidden');
        }

        // Mettre à jour le compteur
        const counter = document.getElementById('packageCounter');
        if (counter) {
            counter.textContent = `${currentIndex + 1} / ${packages.length}`;
        }

        // Mettre à jour la barre de progression
        const progressBar = document.getElementById('progressBar');
        if (progressBar) {
            const progress = ((currentIndex + 1) / packages.length) * 100;
            progressBar.style.width = progress + '%';
        }

        // Mettre à jour les boutons
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        if (prevBtn) prevBtn.disabled = currentIndex === 0;
        if (nextBtn) nextBtn.disabled = currentIndex >= packages.length - 1;
    }
}

function nextPackage() {
    if (currentIndex < packages.length - 1) {
        currentIndex++;
        updatePackageDisplay();
    }
}

function previousPackage() {
    if (currentIndex > 0) {
        currentIndex--;
        updatePackageDisplay();
    }
}

function showDeliveryModal(packageId, packageCode, recipientName, codAmount) {
    const modal = document.getElementById('deliveryModal');
    const form = document.getElementById('deliveryForm');
    const info = document.getElementById('deliveryInfo');

    // Définir l'action du formulaire
    form.action = `/deliverer/packages/${packageId}/deliver`;

    // Préremplir les champs cachés - COD automatique
    document.getElementById('codCollected').value = codAmount;
    document.getElementById('recipientName').value = recipientName;

    // Afficher les infos
    info.innerHTML = `
        <h4 class="font-semibold text-green-800 mb-2">📦 ${packageCode}</h4>
        <p class="text-sm text-green-700">
            <strong>Destinataire:</strong> ${recipientName}<br>
            <strong>COD à collecter:</strong> ${parseFloat(codAmount).toFixed(3)} DT
        </p>
        <p class="text-xs text-green-600 mt-2">💡 Le montant COD sera automatiquement collecté</p>
    `;

    modal.classList.remove('hidden');
}

function closeDeliveryModal() {
    document.getElementById('deliveryModal').classList.add('hidden');
}

function markUnavailable(packageId, packageCode) {
    const modal = document.getElementById('unavailableModal');
    const form = document.getElementById('unavailableForm');
    const info = document.getElementById('unavailableInfo');

    form.action = `/deliverer/packages/${packageId}/unavailable`;

    info.innerHTML = `
        <h4 class="font-semibold text-orange-800 mb-2">📦 ${packageCode}</h4>
        <p class="text-sm text-orange-700">Marquer ce colis comme indisponible</p>
    `;

    modal.classList.remove('hidden');
}

function closeUnavailableModal() {
    document.getElementById('unavailableModal').classList.add('hidden');
}

function openGPS(address, delegation) {
    if (!address || address === 'N/A') {
        alert('Adresse non disponible');
        return;
    }

    const fullAddress = delegation ? `${address}, ${delegation}, Tunisie` : `${address}, Tunisie`;
    const encodedAddress = encodeURIComponent(fullAddress);
    window.open(`https://maps.google.com/maps?q=${encodedAddress}`, '_blank');
}

// Gestion de la soumission des formulaires de livraison avec ouverture du reçu
document.addEventListener('DOMContentLoaded', function() {
    // Pour les formulaires de colis gratuits avec requête AJAX
    document.querySelectorAll('.free-delivery-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Empêcher la soumission normale

            const packageId = form.action.match(/\/packages\/(\d+)\/deliver/)[1];
            const printCheckbox = form.querySelector('input[name="print_receipt"]');
            const formData = new FormData(form);

            // Soumettre via AJAX
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');

                    // Rediriger vers le reçu si demandé
                    if (printCheckbox && printCheckbox.checked) {
                        // Redirection directe vers le reçu
                        setTimeout(() => {
                            window.location.href = `/test-receipt/${packageId}?print=true`;
                        }, 500);
                    } else {
                        // Recharger la page si pas de reçu demandé
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                } else {
                    showToast(data.message || 'Erreur lors de la livraison', 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showToast('Erreur de connexion', 'error');
            });
        });
    });

    // Amélioration pour le modal de livraison
    const deliveryForm = document.getElementById('deliveryForm');
    if (deliveryForm) {
        deliveryForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Empêcher la soumission normale

            const packageId = deliveryForm.action.match(/\/packages\/(\d+)\/deliver/)[1];
            const printCheckbox = deliveryForm.querySelector('input[name="print_receipt"]');
            const formData = new FormData(deliveryForm);

            // Soumettre via AJAX
            fetch(deliveryForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    closeDeliveryModal();

                    // Rediriger vers le reçu si demandé
                    if (printCheckbox && printCheckbox.checked) {
                        // Redirection directe vers le reçu
                        setTimeout(() => {
                            window.location.href = `/test-receipt/${packageId}?print=true`;
                        }, 500);
                    } else {
                        // Recharger la page si pas de reçu demandé
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                } else {
                    showToast(data.message || 'Erreur lors de la livraison', 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showToast('Erreur de connexion', 'error');
            });
        });
    }
});

// Toast notifications
@if(session('success'))
    showToast("{{ session('success') }}", 'success');
@endif

@if(session('error'))
    showToast("{{ session('error') }}", 'error');
@endif

function showToast(message, type) {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';

    toast.className = `fixed top-4 right-4 ${bgColor} text-white px-4 py-2 rounded-lg shadow-lg z-50 max-w-sm`;
    toast.textContent = message;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Support pour les gestes tactiles
let startX = null;

document.addEventListener('touchstart', function(e) {
    startX = e.touches[0].clientX;
});

document.addEventListener('touchend', function(e) {
    if (!startX) return;

    let endX = e.changedTouches[0].clientX;
    let diffX = startX - endX;

    if (Math.abs(diffX) > 50) {
        if (diffX > 0) {
            nextPackage(); // Swipe gauche = suivant
        } else {
            previousPackage(); // Swipe droite = précédent
        }
    }

    startX = null;
});

// Initialiser l'affichage
document.addEventListener('DOMContentLoaded', function() {
    updatePackageDisplay();
});
</script>

@endsection