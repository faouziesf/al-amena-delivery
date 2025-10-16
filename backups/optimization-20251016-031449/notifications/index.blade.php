@extends('layouts.client')

@section('title', 'Mes Notifications')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-purple-900">Mes Notifications</h1>
            <p class="mt-1 text-sm text-purple-600">
                Consultez toutes vos notifications
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <form method="POST" action="{{ route('client.notifications.mark.all.read') }}" class="inline">
                @csrf
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Marquer tout lu
                </button>
            </form>
            <a href="{{ route('client.notifications.settings') }}"
               class="inline-flex items-center px-4 py-2 bg-white border border-purple-300 rounded-xl font-semibold text-xs text-purple-700 uppercase tracking-widest hover:bg-purple-50 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Paramètres
            </a>
        </div>
    </div>
@endsection

@section('content')
    <!-- Filtres -->
    <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6 mb-6">
        <form method="GET" action="{{ route('client.notifications.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Filtre par statut -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                    <select name="status" id="status" class="w-full border border-gray-300 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Toutes les notifications</option>
                        <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Non lues</option>
                        <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Lues</option>
                    </select>
                </div>

                <!-- Filtre par type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                    <select name="type" id="type" class="w-full border border-gray-300 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Tous les types</option>
                        <option value="package" {{ request('type') === 'package' ? 'selected' : '' }}>Colis</option>
                        <option value="wallet" {{ request('type') === 'wallet' ? 'selected' : '' }}>Portefeuille</option>
                        <option value="complaint" {{ request('type') === 'complaint' ? 'selected' : '' }}>Réclamation</option>
                        <option value="pickup" {{ request('type') === 'pickup' ? 'selected' : '' }}>Collecte</option>
                    </select>
                </div>

                <!-- Boutons d'action -->
                <div class="flex items-end space-x-2">
                    <button type="submit"
                            class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-purple-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 transition ease-in-out duration-150">
                        Filtrer
                    </button>

                    @if(request()->hasAny(['status', 'type']))
                        <a href="{{ route('client.notifications.index') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-xl font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition ease-in-out duration-150">
                            Réinitialiser
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Liste des notifications -->
    @if($notifications->count() > 0)
        <div class="space-y-4">
            @foreach($notifications as $notification)
                <div class="bg-white rounded-2xl shadow-sm border {{ $notification->read_at ? 'border-gray-200' : 'border-purple-200 bg-purple-50' }} p-6 transition-all duration-200 hover:shadow-md">
                    <div class="flex items-start space-x-4">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-xl {{ $notification->read_at ? 'bg-gray-100' : 'bg-purple-100' }} flex items-center justify-center">
                                @switch($notification->data['type'] ?? 'general')
                                    @case('package')
                                        <svg class="w-5 h-5 {{ $notification->read_at ? 'text-gray-600' : 'text-purple-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                        @break
                                    @case('wallet')
                                        <svg class="w-5 h-5 {{ $notification->read_at ? 'text-gray-600' : 'text-purple-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                        @break
                                    @case('complaint')
                                        <svg class="w-5 h-5 {{ $notification->read_at ? 'text-gray-600' : 'text-purple-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                        </svg>
                                        @break
                                    @case('pickup')
                                        <svg class="w-5 h-5 {{ $notification->read_at ? 'text-gray-600' : 'text-purple-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                        </svg>
                                        @break
                                    @default
                                        <svg class="w-5 h-5 {{ $notification->read_at ? 'text-gray-600' : 'text-purple-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zm0-8h5l-5-5-5 5h5z"/>
                                        </svg>
                                @endswitch
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-sm font-semibold {{ $notification->read_at ? 'text-gray-900' : 'text-purple-900' }}">
                                        {{ $notification->data['title'] ?? 'Notification' }}
                                    </h3>
                                    <p class="mt-1 text-sm {{ $notification->read_at ? 'text-gray-600' : 'text-purple-700' }}">
                                        {{ $notification->data['message'] ?? $notification->data['body'] ?? 'Nouvelle notification' }}
                                    </p>
                                    <p class="mt-2 text-xs text-gray-500">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center space-x-2 ml-4">
                                    @if(!$notification->read_at)
                                        <form method="POST" action="{{ route('client.notifications.mark.read', $notification) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center px-3 py-1 bg-purple-100 border border-transparent rounded-lg font-medium text-xs text-purple-700 hover:bg-purple-200 transition ease-in-out duration-150">
                                                Marquer lu
                                            </button>
                                        </form>
                                    @endif

                                    <form method="DELETE" action="{{ route('client.notifications.delete', $notification) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')"
                                                class="inline-flex items-center px-3 py-1 bg-red-100 border border-transparent rounded-lg font-medium text-xs text-red-700 hover:bg-red-200 transition ease-in-out duration-150">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Action button if applicable -->
                            @if(isset($notification->data['action_url']))
                                <div class="mt-3">
                                    <a href="{{ $notification->data['action_url'] }}"
                                       class="inline-flex items-center px-3 py-2 bg-purple-600 border border-transparent rounded-lg font-medium text-xs text-white hover:bg-purple-700 transition ease-in-out duration-150">
                                        {{ $notification->data['action_text'] ?? 'Voir détails' }}
                                        <svg class="ml-1 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $notifications->appends(request()->query())->links() }}
        </div>

        <!-- Actions en lot -->
        @if($notifications->where('read_at', null)->count() > 0)
            <div class="mt-6 bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions en lot</h3>
                <div class="flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('client.notifications.bulk.delete') }}" class="inline">
                        @csrf
                        <input type="hidden" name="type" value="unread">
                        <button type="submit"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer toutes les notifications non lues ?')"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Supprimer toutes les non lues
                        </button>
                    </form>

                    <form method="POST" action="{{ route('client.notifications.bulk.delete') }}" class="inline">
                        @csrf
                        <input type="hidden" name="type" value="read">
                        <button type="submit"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer toutes les notifications lues ?')"
                                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Supprimer toutes les lues
                        </button>
                    </form>
                </div>
            </div>
        @endif

    @else
        <!-- État vide -->
        <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-12 text-center">
            <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zm0-8h5l-5-5-5 5h5z"/>
                </svg>
            </div>

            <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucune notification</h3>

            <p class="text-gray-500 mb-6">
                @if(request()->hasAny(['status', 'type']))
                    Aucune notification ne correspond à vos critères de recherche.
                    <a href="{{ route('client.notifications.index') }}" class="text-purple-600 hover:text-purple-800 font-medium">
                        Voir toutes les notifications
                    </a>
                @else
                    Vous n'avez aucune notification pour le moment. Les notifications apparaîtront ici pour vous tenir informé des mises à jour de vos colis, portefeuille et réclamations.
                @endif
            </p>

            <a href="{{ route('client.dashboard') }}"
               class="inline-flex items-center px-6 py-3 bg-purple-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:bg-purple-700 transition ease-in-out duration-150">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                </svg>
                Retour au Dashboard
            </a>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    // Auto-refresh notifications every 30 seconds
    setInterval(() => {
        fetch('{{ route("client.api.notifications.poll") }}')
            .then(response => response.json())
            .then(data => {
                if (data.has_new_notifications) {
                    // Optionally reload page or show indicator for new notifications
                    const indicator = document.createElement('div');
                    indicator.className = 'fixed top-20 right-4 bg-blue-500 text-white px-4 py-2 rounded-xl shadow-lg z-50';
                    indicator.innerHTML = 'Nouvelles notifications disponibles. <a href="#" onclick="location.reload()" class="underline">Actualiser</a>';
                    document.body.appendChild(indicator);

                    setTimeout(() => {
                        indicator.remove();
                    }, 5000);
                }
            })
            .catch(error => console.log('Erreur lors de la vérification des notifications:', error));
    }, 30000);
</script>
@endpush