@extends('layouts.supervisor')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- En-tête -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestion des Utilisateurs</h1>
            <p class="text-gray-600">Gérer tous les utilisateurs du système</p>
        </div>
        <a href="{{ route('supervisor.users.create') }}" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Nouvel Utilisateur
        </a>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase">Total</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-2.239"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase">Actifs</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['active_users'] }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase">En Attente</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['pending_users'] }}</p>
                </div>
                <div class="bg-yellow-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase">Suspendus</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['suspended_users'] }}</p>
                </div>
                <div class="bg-red-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Nom, email, téléphone..."
                       class="w-full border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Rôle</label>
                <select name="role" class="w-full border-gray-300 rounded-lg">
                    <option value="">Tous les rôles</option>
                    <option value="CLIENT" {{ request('role') == 'CLIENT' ? 'selected' : '' }}>Client</option>
                    <option value="DELIVERER" {{ request('role') == 'DELIVERER' ? 'selected' : '' }}>Livreur</option>
                    <option value="COMMERCIAL" {{ request('role') == 'COMMERCIAL' ? 'selected' : '' }}>Commercial</option>
                    <option value="SUPERVISOR" {{ request('role') == 'SUPERVISOR' ? 'selected' : '' }}>Superviseur</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                <select name="status" class="w-full border-gray-300 rounded-lg">
                    <option value="">Tous les statuts</option>
                    <option value="ACTIVE" {{ request('status') == 'ACTIVE' ? 'selected' : '' }}>Actif</option>
                    <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>En attente</option>
                    <option value="SUSPENDED" {{ request('status') == 'SUSPENDED' ? 'selected' : '' }}>Suspendu</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Délégation</label>
                <select name="delegation_id" class="w-full border-gray-300 rounded-lg">
                    <option value="">Toutes</option>
                    @foreach(\App\Models\Delegation::where('status', 'ACTIVE')->get() as $delegation)
                    <option value="{{ $delegation->id }}" {{ request('delegation_id') == $delegation->id ? 'selected' : '' }}>
                        {{ $delegation->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Actions groupées -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6" x-data="{ selectedUsers: [] }">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">Actions groupées:</span>
                <button @click="if(selectedUsers.length > 0 && confirm('Activer les utilisateurs sélectionnés?')) { document.getElementById('bulkActivateForm').submit(); }"
                        class="px-3 py-1 bg-green-200 text-green-800 rounded-lg text-sm hover:bg-green-300">
                    Activer
                </button>
                <button @click="if(selectedUsers.length > 0 && confirm('Désactiver les utilisateurs sélectionnés?')) { document.getElementById('bulkDeactivateForm').submit(); }"
                        class="px-3 py-1 bg-yellow-200 text-yellow-800 rounded-lg text-sm hover:bg-yellow-300">
                    Désactiver
                </button>
                <button @click="if(selectedUsers.length > 0 && confirm('ATTENTION: Supprimer définitivement les utilisateurs sélectionnés?')) { document.getElementById('bulkDeleteForm').submit(); }"
                        class="px-3 py-1 bg-red-200 text-red-800 rounded-lg text-sm hover:bg-red-300">
                    Supprimer
                </button>
            </div>
            <span x-text="selectedUsers.length + ' utilisateur(s) sélectionné(s)'" class="text-sm text-gray-600"></span>
        </div>

        <!-- Forms cachés pour actions groupées -->
        <form id="bulkActivateForm" method="POST" action="{{ route('supervisor.users.bulk.activate') }}" class="hidden">
            @csrf
            <template x-for="userId in selectedUsers">
                <input type="hidden" name="user_ids[]" :value="userId">
            </template>
        </form>

        <form id="bulkDeactivateForm" method="POST" action="{{ route('supervisor.users.bulk.deactivate') }}" class="hidden">
            @csrf
            <template x-for="userId in selectedUsers">
                <input type="hidden" name="user_ids[]" :value="userId">
            </template>
        </form>

        <form id="bulkDeleteForm" method="POST" action="{{ route('supervisor.users.bulk.delete') }}" class="hidden">
            @csrf
            <template x-for="userId in selectedUsers">
                <input type="hidden" name="user_ids[]" :value="userId">
            </template>
        </form>
    </div>

    <!-- Tableau des utilisateurs -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" x-on:change="selectedUsers = $event.target.checked ? users.map(u => u.id) : []"
                                   class="rounded border-gray-300">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rôle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Délégation</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Créé le</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" x-data="{ users: {{ $users->items() ? json_encode($users->items()) : '[]' }} }">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" :value="{{ $user->id }}"
                                   x-model="selectedUsers"
                                   class="rounded border-gray-300">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mr-4">
                                    <span class="text-sm font-medium text-red-600">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    <div class="text-xs text-gray-400">{{ $user->phone }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($user->role === 'CLIENT') bg-green-100 text-green-800
                                @elseif($user->role === 'DELIVERER') bg-blue-100 text-blue-800
                                @elseif($user->role === 'COMMERCIAL') bg-purple-100 text-purple-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($user->status === 'ACTIVE') bg-green-100 text-green-800
                                @elseif($user->status === 'PENDING') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $user->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $user->delegation->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('supervisor.users.show', $user) }}"
                                   class="text-red-600 hover:text-red-900">Voir</a>
                                <a href="{{ route('supervisor.users.edit', $user) }}"
                                   class="text-blue-600 hover:text-blue-900">Modifier</a>

                                @if($user->status === 'ACTIVE')
                                <form method="POST" action="{{ route('supervisor.users.deactivate', $user) }}" class="inline">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Désactiver cet utilisateur?')"
                                            class="text-yellow-600 hover:text-yellow-900">Désactiver</button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('supervisor.users.activate', $user) }}" class="inline">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Activer cet utilisateur?')"
                                            class="text-green-600 hover:text-green-900">Activer</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-2.239"></path>
                                </svg>
                                <p class="text-lg font-medium">Aucun utilisateur trouvé</p>
                                <p class="text-sm text-gray-400 mt-1">Essayez de modifier vos filtres</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
        <div class="bg-white px-6 py-3 border-t border-gray-200">
            {{ $users->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection