@extends('layouts.commercial')

@section('title', 'Réclamations')
@section('page-title', 'Gestion des Réclamations')
@section('page-description', 'Traitement des réclamations clients')

@section('content')
<div class="space-y-6">

    <!-- Statistiques rapides -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">En attente</p>
                    <p class="text-2xl font-bold text-gray-900" id="pending-count">-</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Résolues aujourd'hui</p>
                    <p class="text-2xl font-bold text-gray-900" id="resolved-today">-</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Urgentes</p>
                    <p class="text-2xl font-bold text-gray-900" id="urgent-count">-</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total</p>
                    <p class="text-2xl font-bold text-gray-900" id="total-count">-</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                <select name="status" id="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">Tous les statuts</option>
                    <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>En attente</option>
                    <option value="IN_PROGRESS" {{ request('status') === 'IN_PROGRESS' ? 'selected' : '' }}>En cours</option>
                    <option value="RESOLVED" {{ request('status') === 'RESOLVED' ? 'selected' : '' }}>Résolue</option>
                    <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>Rejetée</option>
                </select>
            </div>

            <div>
                <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">Priorité</label>
                <select name="priority" id="priority" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">Toutes les priorités</option>
                    <option value="LOW" {{ request('priority') === 'LOW' ? 'selected' : '' }}>Faible</option>
                    <option value="MEDIUM" {{ request('priority') === 'MEDIUM' ? 'selected' : '' }}>Moyenne</option>
                    <option value="HIGH" {{ request('priority') === 'HIGH' ? 'selected' : '' }}>Élevée</option>
                    <option value="URGENT" {{ request('priority') === 'URGENT' ? 'selected' : '' }}>Urgente</option>
                </select>
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                <select name="type" id="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">Tous les types</option>
                    <option value="DELIVERY_ISSUE" {{ request('type') === 'DELIVERY_ISSUE' ? 'selected' : '' }}>Problème livraison</option>
                    <option value="PACKAGE_DAMAGED" {{ request('type') === 'PACKAGE_DAMAGED' ? 'selected' : '' }}>Colis endommagé</option>
                    <option value="PACKAGE_LOST" {{ request('type') === 'PACKAGE_LOST' ? 'selected' : '' }}>Colis perdu</option>
                    <option value="WRONG_ADDRESS" {{ request('type') === 'WRONG_ADDRESS' ? 'selected' : '' }}>Mauvaise adresse</option>
                    <option value="PAYMENT_ISSUE" {{ request('type') === 'PAYMENT_ISSUE' ? 'selected' : '' }}>Problème paiement</option>
                    <option value="OTHER" {{ request('type') === 'OTHER' ? 'selected' : '' }}>Autre</option>
                </select>
            </div>

            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Date début</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            </div>

            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       placeholder="Code colis, client..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-600 transition-colors">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Liste des réclamations -->
    <div class="bg-white rounded-xl shadow-sm border border-orange-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Réclamations</h3>
        </div>

        <div id="complaints-list">
            <!-- Le contenu sera chargé ici via JavaScript -->
            <div class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500"></div>
                <p class="mt-2 text-gray-500">Chargement des réclamations...</p>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadComplaintsStats();
    loadComplaintsList();
});

function loadComplaintsStats() {
    fetch('{{ route("commercial.api.complaints.stats") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('pending-count').textContent = data.pending || 0;
            document.getElementById('resolved-today').textContent = data.resolved_today || 0;
            document.getElementById('urgent-count').textContent = data.urgent || 0;
            document.getElementById('total-count').textContent = data.total || 0;
        })
        .catch(error => {
            console.error('Erreur lors du chargement des statistiques:', error);
        });
}

function loadComplaintsList() {
    const urlParams = new URLSearchParams(window.location.search);
    const filters = Object.fromEntries(urlParams.entries());

    let url = '{{ route("commercial.api.complaints.pending") }}';
    if (Object.keys(filters).length > 0) {
        url += '?' + urlParams.toString();
    }

    fetch(url)
        .then(response => response.json())
        .then(complaints => {
            const listContainer = document.getElementById('complaints-list');

            if (complaints.length === 0) {
                listContainer.innerHTML = `
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune réclamation trouvée</h3>
                        <p class="mt-1 text-sm text-gray-500">Aucune réclamation ne correspond aux critères sélectionnés.</p>
                    </div>
                `;
                return;
            }

            const complaintsHtml = complaints.map(complaint => {
                const priorityClass = {
                    'LOW': 'bg-gray-100 text-gray-800',
                    'MEDIUM': 'bg-blue-100 text-blue-800',
                    'HIGH': 'bg-orange-100 text-orange-800',
                    'URGENT': 'bg-red-100 text-red-800'
                };

                const statusClass = {
                    'PENDING': 'bg-yellow-100 text-yellow-800',
                    'IN_PROGRESS': 'bg-blue-100 text-blue-800',
                    'RESOLVED': 'bg-green-100 text-green-800',
                    'REJECTED': 'bg-red-100 text-red-800'
                };

                return `
                    <div class="border-b border-gray-200 p-6 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h4 class="text-sm font-medium text-gray-900">#${complaint.id}</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${priorityClass[complaint.priority] || 'bg-gray-100 text-gray-800'}">
                                        ${complaint.priority}
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass[complaint.status] || 'bg-gray-100 text-gray-800'}">
                                        ${complaint.status}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600">${complaint.subject}</p>
                                <div class="mt-2 text-xs text-gray-500">
                                    <span>Colis: ${complaint.package_code}</span> •
                                    <span>Client: ${complaint.client_name}</span> •
                                    <span>${complaint.created_at}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <a href="${complaint.url}" class="text-orange-600 hover:text-orange-900 text-sm font-medium">
                                    Voir →
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            listContainer.innerHTML = complaintsHtml;
        })
        .catch(error => {
            console.error('Erreur lors du chargement des réclamations:', error);
            document.getElementById('complaints-list').innerHTML = `
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Erreur de chargement</h3>
                    <p class="mt-1 text-sm text-gray-500">Une erreur est survenue lors du chargement des réclamations.</p>
                </div>
            `;
        });
}
</script>
@endsection