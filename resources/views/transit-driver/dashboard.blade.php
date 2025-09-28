@extends('layouts.app')

@section('title', 'Tableau de Bord - Livreur Transit')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Tableau de Bord Transit</h1>
            <p class="text-gray-600">Bonjour {{ auth()->user()->name }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-500">{{ now()->format('l d F Y') }}</p>
            <p class="text-lg font-semibold text-gray-700">{{ now()->format('H:i') }}</p>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"></path>
                        <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V8a1 1 0 00-1-1h-3z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Tournée d'aujourd'hui</p>
                    <p class="text-2xl font-bold text-gray-900" id="today-route-status">En attente...</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Boîtes à transporter</p>
                    <p class="text-2xl font-bold text-gray-900" id="boxes-count">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Statut</p>
                    <p class="text-2xl font-bold text-gray-900" id="route-status">Prêt</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Dernière connexion</p>
                    <p class="text-lg font-bold text-gray-900">{{ now()->format('H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Tournée du jour -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Tournée du Jour</h3>
            </div>
            <div class="p-6">
                <div id="route-info">
                    <div class="text-center py-8">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                        <p class="mt-2 text-gray-600">Chargement...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Actions Rapides</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <a href="{{ route('transit-driver.app') }}"
                       class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg text-center block transition duration-200">
                        📱 Ouvrir l'App Mobile
                    </a>

                    <button onclick="refreshData()"
                            class="w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                        🔄 Actualiser les Données
                    </button>

                    <a href="{{ route('transit-driver.reports') }}"
                       class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg text-center block transition duration-200">
                        📊 Voir les Rapports
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Historique récent -->
    <div class="mt-8 bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Historique Récent</h3>
        </div>
        <div class="p-6">
            <div id="recent-history">
                <div class="text-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="mt-2 text-gray-600">Chargement de l'historique...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();

    // Actualiser toutes les 30 secondes
    setInterval(loadDashboardData, 30000);
});

async function loadDashboardData() {
    try {
        // Charger la tournée du jour
        const token = localStorage.getItem('transit_token');

        if (!token) {
            window.location.href = '/login';
            return;
        }

        const response = await fetch('/api/transit-driver/ma-tournee', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            updateRouteInfo(data.route);
        } else {
            showError('Erreur lors du chargement des données');
        }

        // Charger l'historique
        loadRecentHistory();

    } catch (error) {
        console.error('Erreur:', error);
        showError('Erreur de connexion');
    }
}

function updateRouteInfo(route) {
    const routeInfoDiv = document.getElementById('route-info');
    const todayRouteStatus = document.getElementById('today-route-status');
    const boxesCount = document.getElementById('boxes-count');
    const routeStatus = document.getElementById('route-status');

    if (route) {
        todayRouteStatus.textContent = `${route.from} → ${route.to}`;
        boxesCount.textContent = route.boxes_count;
        routeStatus.textContent = getStatusText(route.status);

        routeInfoDiv.innerHTML = `
            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                </div>
                <h4 class="text-xl font-bold text-gray-800 mb-2">Tournée Assignée</h4>
                <div class="text-3xl font-black text-blue-600 mb-4">
                    ${route.from} → ${route.to}
                </div>
                <p class="text-gray-600 mb-4">${route.boxes_count} boîtes à transporter</p>
                <p class="text-sm text-gray-500">Statut: ${getStatusText(route.status)}</p>
            </div>
        `;
    } else {
        todayRouteStatus.textContent = 'Aucune';
        boxesCount.textContent = '0';
        routeStatus.textContent = 'En attente';

        routeInfoDiv.innerHTML = `
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <h4 class="text-xl font-bold text-gray-800 mb-2">Aucune Tournée</h4>
                <p class="text-gray-600">Aucune tournée assignée pour aujourd'hui</p>
            </div>
        `;
    }
}

async function loadRecentHistory() {
    try {
        const token = localStorage.getItem('transit_token');
        const response = await fetch('/api/transit-driver/historique', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            updateRecentHistory(data.history.slice(0, 5)); // Dernières 5 tournées
        }
    } catch (error) {
        console.error('Erreur historique:', error);
    }
}

function updateRecentHistory(history) {
    const historyDiv = document.getElementById('recent-history');

    if (history.length === 0) {
        historyDiv.innerHTML = `
            <div class="text-center py-8 text-gray-500">
                <p>Aucun historique disponible</p>
            </div>
        `;
        return;
    }

    historyDiv.innerHTML = `
        <div class="space-y-4">
            ${history.map(route => `
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <div class="font-semibold text-gray-800">${route.from} → ${route.to}</div>
                        <div class="text-sm text-gray-600">${route.date} • ${route.boxes_count} boîtes</div>
                        ${route.duration ? `<div class="text-xs text-gray-500">Durée: ${route.duration}</div>` : ''}
                    </div>
                    <span class="px-3 py-1 text-xs font-medium rounded-full ${getStatusBadgeClass(route.status)}">
                        ${getStatusText(route.status)}
                    </span>
                </div>
            `).join('')}
        </div>
    `;
}

function getStatusText(status) {
    const statusMap = {
        'ASSIGNED': 'Assignée',
        'IN_PROGRESS': 'En cours',
        'COMPLETED': 'Terminée',
        'CANCELLED': 'Annulée'
    };
    return statusMap[status] || status;
}

function getStatusBadgeClass(status) {
    const classMap = {
        'ASSIGNED': 'bg-yellow-100 text-yellow-800',
        'IN_PROGRESS': 'bg-blue-100 text-blue-800',
        'COMPLETED': 'bg-green-100 text-green-800',
        'CANCELLED': 'bg-red-100 text-red-800'
    };
    return classMap[status] || 'bg-gray-100 text-gray-800';
}

function refreshData() {
    showNotification('Actualisation des données...', 'info');
    loadDashboardData();
}

function showError(message) {
    showNotification(message, 'error');
}

function showNotification(message, type = 'info') {
    // Création simple d'une notification
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'error' ? 'bg-red-100 text-red-800 border border-red-200' :
        type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' :
        'bg-blue-100 text-blue-800 border border-blue-200'
    }`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 4000);
}
</script>
@endsection