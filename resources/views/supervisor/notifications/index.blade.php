@extends('layouts.supervisor')

@section('title', 'Notifications')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 to-pink-50 py-6 px-4">
    <div class="max-w-4xl mx-auto">
        <!-- En-tête -->
        <div class="bg-white/90 backdrop-blur-lg rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">🔔 Notifications</h1>
                    <p class="text-gray-600 mt-1">Gérez vos notifications système</p>
                </div>
                
                @if($stats['unread'] > 0)
                <form method="POST" action="{{ route('supervisor.notifications.mark.all.read') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 transition-all shadow-md">
                        ✓ Tout marquer lu
                    </button>
                </form>
                @endif
            </div>

            <!-- Statistiques -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4">
                    <div class="text-sm text-blue-600 font-medium">Total</div>
                    <div class="text-2xl font-bold text-blue-700">{{ $stats['total'] }}</div>
                </div>
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4">
                    <div class="text-sm text-purple-600 font-medium">Non lues</div>
                    <div class="text-2xl font-bold text-purple-700">{{ $stats['unread'] }}</div>
                </div>
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4">
                    <div class="text-sm text-orange-600 font-medium">Prioritaires</div>
                    <div class="text-2xl font-bold text-orange-700">{{ $stats['high_priority'] }}</div>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4">
                    <div class="text-sm text-green-600 font-medium">Aujourd'hui</div>
                    <div class="text-2xl font-bold text-green-700">{{ $stats['today'] }}</div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="bg-white/90 backdrop-blur-lg rounded-xl shadow-lg p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Priorité</label>
                    <select name="priority" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="">Toutes</option>
                        <option value="LOW" {{ request('priority') === 'LOW' ? 'selected' : '' }}>Faible</option>
                        <option value="NORMAL" {{ request('priority') === 'NORMAL' ? 'selected' : '' }}>Normal</option>
                        <option value="HIGH" {{ request('priority') === 'HIGH' ? 'selected' : '' }}>Haute</option>
                        <option value="URGENT" {{ request('priority') === 'URGENT' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                    <select name="read" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="">Toutes</option>
                        <option value="unread" {{ request('read') === 'unread' ? 'selected' : '' }}>Non lues</option>
                        <option value="read" {{ request('read') === 'read' ? 'selected' : '' }}>Lues</option>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 transition-all">
                        🔍 Filtrer
                    </button>
                </div>
            </form>
        </div>

        <!-- Liste des notifications -->
        <div class="space-y-4">
            @forelse($notifications as $notification)
            <div class="bg-white/90 backdrop-blur-lg rounded-xl shadow-md p-4 hover:shadow-xl transition-all {{ !$notification->read_at ? 'border-l-4 border-purple-500' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <h3 class="font-semibold text-gray-900">{{ $notification->title }}</h3>
                            
                            @php
                                $priorityColors = [
                                    'LOW' => 'bg-gray-100 text-gray-700',
                                    'NORMAL' => 'bg-blue-100 text-blue-700',
                                    'HIGH' => 'bg-orange-100 text-orange-700',
                                    'URGENT' => 'bg-red-100 text-red-700',
                                ];
                                $priorityLabels = [
                                    'LOW' => 'Faible',
                                    'NORMAL' => 'Normal',
                                    'HIGH' => 'Haute',
                                    'URGENT' => 'Urgent',
                                ];
                            @endphp
                            
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $priorityColors[$notification->priority] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $priorityLabels[$notification->priority] ?? $notification->priority }}
                            </span>
                            
                            @if(!$notification->read_at)
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gradient-to-r from-purple-100 to-pink-100 text-purple-700">
                                Nouveau
                            </span>
                            @endif
                        </div>
                        
                        <p class="text-gray-600 mb-2">{{ $notification->message }}</p>
                        
                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                            <span>📅 {{ $notification->created_at->format('d/m/Y H:i') }}</span>
                            <span>⏰ {{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    
                    <div class="flex space-x-2 ml-4">
                        @if(!$notification->read_at)
                        <form method="POST" action="{{ route('supervisor.notifications.mark.read', $notification) }}">
                            @csrf
                            <button type="submit" class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors" title="Marquer comme lu">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        </form>
                        @endif
                        
                        <form method="POST" action="{{ route('supervisor.notifications.delete', $notification) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Supprimer" onclick="return confirm('Supprimer cette notification ?')">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white/90 backdrop-blur-lg rounded-xl shadow-lg p-12 text-center">
                <div class="text-6xl mb-4">🔕</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucune notification</h3>
                <p class="text-gray-600">Vous n'avez aucune notification pour le moment</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($notifications->hasPages())
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
