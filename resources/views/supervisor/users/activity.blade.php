<x-layouts.supervisor-new>
    <x-slot name="title">Activité - {{ $user->name }}</x-slot>
    <x-slot name="subtitle">{{ $user->role }} • {{ $user->email }}</x-slot>

    <div class="space-y-6">
        <!-- User Info Card -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center space-x-4">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-gray-600">{{ $user->email }} • {{ $user->phone }}</p>
                    <div class="mt-2 flex items-center space-x-2">
                        @if($user->account_status === 'ACTIVE')
                        <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">Actif</span>
                        @else
                        <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">{{ $user->account_status }}</span>
                        @endif
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">{{ $user->role }}</span>
                    </div>
                </div>
                <div class="space-x-2">
                    <a href="{{ route('supervisor.users.show', $user) }}" 
                       class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                        Voir Profil
                    </a>
                    @if($user->account_status === 'ACTIVE')
                    <form action="{{ route('supervisor.users.impersonate', $user) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg">
                            Se Connecter
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Total Actions</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $logs->total() }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Aujourd'hui</p>
                <p class="text-3xl font-bold text-blue-600 mt-2">{{ $logs->where('created_at', '>=', today())->count() }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Cette Semaine</p>
                <p class="text-3xl font-bold text-green-600 mt-2">{{ $logs->where('created_at', '>=', now()->startOfWeek())->count() }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Ce Mois</p>
                <p class="text-3xl font-bold text-purple-600 mt-2">{{ $logs->where('created_at', '>=', now()->startOfMonth())->count() }}</p>
            </div>
        </div>

        <!-- Timeline -->
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Timeline d'Activité</h3>
            
            <div class="space-y-4">
                @forelse($logs as $log)
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 mt-1">
                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                    </div>
                    <div class="flex-1 min-w-0 pb-4 border-l-2 border-gray-200 pl-6 ml-1.5">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="font-semibold text-gray-900">{{ $log->action_type }}</h4>
                            <time class="text-sm text-gray-500">{{ $log->created_at->diffForHumans() }}</time>
                        </div>
                        
                        @if($log->target_type)
                        <p class="text-sm text-gray-600">
                            Cible: {{ $log->target_type }} #{{ $log->target_id }}
                        </p>
                        @endif

                        @if($log->metadata)
                        <div class="mt-2 p-3 bg-gray-50 rounded-lg">
                            <pre class="text-xs text-gray-600 overflow-x-auto">{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        @endif

                        <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500">
                            <span>IP: {{ $log->ip_address ?? 'N/A' }}</span>
                            <span>{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12 text-gray-500">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <p>Aucune activité enregistrée</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Pagination -->
        @if($logs->hasPages())
        <div class="bg-white rounded-xl shadow px-6 py-4">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</x-layouts.supervisor-new>
