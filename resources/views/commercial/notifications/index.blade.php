@extends('layouts.commercial')

@section('title', 'Notifications')
@section('page-title', 'Centre de Notifications')
@section('page-description', 'Gestion des notifications et alertes')

@section('content')
<div class="space-y-6">

    <!-- Actions rapides -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <button onclick="markAllAsRead()"
                    class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors">
                Tout marquer comme lu
            </button>
            <button onclick="deleteOldNotifications()"
                    class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">
                Supprimer les anciennes
            </button>
        </div>
        <div class="text-sm text-gray-500">
            <span id="unread-count">-</span> notifications non lues
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="filter-status" class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                <select id="filter-status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">Toutes</option>
                    <option value="unread">Non lues</option>
                    <option value="read">Lues</option>
                </select>
            </div>

            <div>
                <label for="filter-type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                <select id="filter-type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">Tous les types</option>
                    <option value="complaint">Réclamations</option>
                    <option value="withdrawal">Retraits</option>
                    <option value="topup">Recharges</option>
                    <option value="package">Colis</option>
                    <option value="system">Système</option>
                </select>
            </div>

            <div>
                <label for="filter-date" class="block text-sm font-medium text-gray-700 mb-2">Période</label>
                <select id="filter-date" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">Toute période</option>
                    <option value="today">Aujourd'hui</option>
                    <option value="week">Cette semaine</option>
                    <option value="month">Ce mois</option>
                </select>
            </div>

            <div class="flex items-end">
                <button onclick="loadNotifications()" class="w-full bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-600 transition-colors">
                    Filtrer
                </button>
            </div>
        </div>
    </div>

    <!-- Liste des notifications -->
    <div class="bg-white rounded-xl shadow-sm border border-orange-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
        </div>

        <div id="notifications-list">
            <!-- Le contenu sera chargé ici via JavaScript -->
            <div class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500"></div>
                <p class="mt-2 text-gray-500">Chargement des notifications...</p>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadNotifications();
    loadUnreadCount();
});

function loadNotifications() {
    const status = document.getElementById('filter-status').value;
    const type = document.getElementById('filter-type').value;
    const date = document.getElementById('filter-date').value;

    let url = '{{ route("commercial.api.notifications.all") }}';
    const params = new URLSearchParams();

    if (status) params.append('status', status);
    if (type) params.append('type', type);
    if (date) params.append('date', date);

    if (params.toString()) {
        url += '?' + params.toString();
    }

    fetch(url)
        .then(response => response.json())
        .then(notifications => {
            const listContainer = document.getElementById('notifications-list');

            if (notifications.length === 0) {
                listContainer.innerHTML = `
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5l-5-5h5v-13h0v13z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune notification</h3>
                        <p class="mt-1 text-sm text-gray-500">Aucune notification ne correspond aux critères sélectionnés.</p>
                    </div>
                `;
                return;
            }

            const notificationsHtml = notifications.map(notification => {
                const typeIcons = {
                    'complaint': '📋',
                    'withdrawal': '💰',
                    'topup': '⬆️',
                    'package': '📦',
                    'system': '⚙️'
                };

                const typeColors = {
                    'complaint': 'bg-red-100 text-red-800',
                    'withdrawal': 'bg-green-100 text-green-800',
                    'topup': 'bg-blue-100 text-blue-800',
                    'package': 'bg-orange-100 text-orange-800',
                    'system': 'bg-gray-100 text-gray-800'
                };

                return `
                    <div class="border-b border-gray-200 p-6 hover:bg-gray-50 ${notification.read_at ? 'opacity-75' : 'bg-blue-50'}">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full ${typeColors[notification.type] || 'bg-gray-100 text-gray-800'} flex items-center justify-center">
                                    <span class="text-lg">${typeIcons[notification.type] || '📄'}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-900 truncate">
                                        ${notification.title}
                                    </h4>
                                    <div class="flex items-center space-x-2">
                                        ${!notification.read_at ? '<div class="w-2 h-2 bg-blue-600 rounded-full"></div>' : ''}
                                        <span class="text-xs text-gray-500">${notification.created_at}</span>
                                        <button onclick="deleteNotification('${notification.id}')"
                                                class="text-red-500 hover:text-red-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <p class="mt-1 text-sm text-gray-600">${notification.message}</p>
                                ${notification.action_url ? `
                                    <div class="mt-2">
                                        <a href="${notification.action_url}"
                                           onclick="markAsRead('${notification.id}')"
                                           class="text-orange-600 hover:text-orange-700 text-sm font-medium">
                                            ${notification.action_text || 'Voir les détails'} →
                                        </a>
                                    </div>
                                ` : ''}
                                ${!notification.read_at ? `
                                    <div class="mt-2">
                                        <button onclick="markAsRead('${notification.id}')"
                                                class="text-blue-600 hover:text-blue-700 text-sm">
                                            Marquer comme lu
                                        </button>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            listContainer.innerHTML = notificationsHtml;
        })
        .catch(error => {
            console.error('Erreur lors du chargement des notifications:', error);
            document.getElementById('notifications-list').innerHTML = `
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Erreur de chargement</h3>
                    <p class="mt-1 text-sm text-gray-500">Une erreur est survenue lors du chargement des notifications.</p>
                </div>
            `;
        });
}

function loadUnreadCount() {
    fetch('{{ route("commercial.api.notifications.unread.count") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('unread-count').textContent = data.count || 0;
        })
        .catch(error => {
            console.error('Erreur lors du chargement du compteur:', error);
        });
}

function markAsRead(notificationId) {
    fetch('{{ route("commercial.api.notifications.mark.read") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            notification_id: notificationId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotifications();
            loadUnreadCount();
        }
    })
    .catch(error => {
        console.error('Erreur lors du marquage:', error);
    });
}

function markAllAsRead() {
    fetch('{{ route("commercial.notifications.mark.read") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        loadNotifications();
        loadUnreadCount();
    })
    .catch(error => {
        console.error('Erreur lors du marquage global:', error);
    });
}

function deleteNotification(notificationId) {
    if (!confirm('Supprimer cette notification ?')) return;

    fetch(`{{ route("commercial.notifications.index") }}/${notificationId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        loadNotifications();
        loadUnreadCount();
    })
    .catch(error => {
        console.error('Erreur lors de la suppression:', error);
    });
}

function deleteOldNotifications() {
    if (!confirm('Supprimer toutes les notifications anciennes (plus de 30 jours) ?')) return;

    fetch('{{ route("commercial.notifications.delete.old") }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        loadNotifications();
        loadUnreadCount();
        alert('Notifications anciennes supprimées avec succès.');
    })
    .catch(error => {
        console.error('Erreur lors de la suppression:', error);
    });
}
</script>
@endsection