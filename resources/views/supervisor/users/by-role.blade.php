<x-layouts.supervisor-new>
    <x-slot name="title">
        @if($role === 'CLIENT') Clients
        @elseif($role === 'DELIVERER') Livreurs
        @elseif($role === 'COMMERCIAL') Commerciaux
        @else Chefs Dépôt
        @endif
    </x-slot>
    <x-slot name="subtitle">Gestion des utilisateurs de type {{ $role }}</x-slot>

    <div class="space-y-6">
        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Total</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Actifs</p>
                <p class="text-3xl font-bold text-green-600 mt-2">{{ $stats['active'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">En Attente</p>
                <p class="text-3xl font-bold text-yellow-600 mt-2">{{ $stats['pending'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Suspendus</p>
                <p class="text-3xl font-bold text-red-600 mt-2">{{ $stats['suspended'] }}</p>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Colis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inscription</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                    <p class="text-sm text-gray-600">ID: #{{ $user->id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900">{{ $user->email }}</p>
                            <p class="text-sm text-gray-600">{{ $user->phone }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($user->account_status === 'ACTIVE')
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">Actif</span>
                            @elseif($user->account_status === 'PENDING')
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">En attente</span>
                            @else
                            <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">Suspendu</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-gray-900">{{ $user->sent_packages_count ?? 0 }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-600">{{ $user->created_at->format('d/m/Y') }}</p>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('supervisor.users.show', $user) }}" 
                               class="text-blue-600 hover:text-blue-900 text-sm">Voir</a>
                            <a href="{{ route('supervisor.users.activity', $user) }}" 
                               class="text-indigo-600 hover:text-indigo-900 text-sm">Activité</a>
                            @if($user->account_status === 'ACTIVE')
                            <form action="{{ route('supervisor.users.impersonate', $user) }}" 
                                  method="POST" 
                                  class="inline">
                                @csrf
                                <button type="submit" class="text-purple-600 hover:text-purple-900 text-sm">
                                    Se connecter
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            Aucun utilisateur de ce type
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
        <div class="bg-white rounded-xl shadow px-6 py-4">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</x-layouts.supervisor-new>
