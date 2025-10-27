<x-layouts.supervisor-new>
    <x-slot name="title">Actions Critiques</x-slot>
    <x-slot name="subtitle">Surveillance des actions sensibles nécessitant une attention particulière</x-slot>

    <div class="space-y-6">
        <!-- Alert Banner -->
        <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg">
            <div class="flex items-start space-x-3">
                <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-red-900">⚠️ Zone de Surveillance Critique</h3>
                    <p class="text-red-700 mt-1">
                        Ces actions sont considérées comme critiques et ont déclenché une alerte automatique. 
                        Vérifiez régulièrement cette page pour détecter toute activité suspecte.
                    </p>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Total Actions Critiques</p>
                <p class="text-3xl font-bold text-red-600 mt-2">{{ $logs->total() }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Aujourd'hui</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">
                    {{ $logs->where('created_at', '>=', today())->count() }}
                </p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Cette Semaine</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">
                    {{ $logs->where('created_at', '>=', now()->startOfWeek())->count() }}
                </p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Types d'Actions</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">
                    {{ $logs->unique('action_type')->count() }}
                </p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Utilisateur</label>
                    <select name="user_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Tous</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type d'Action</label>
                    <select name="action_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Tous</option>
                        <option value="USER_ROLE_CHANGED" {{ request('action_type') === 'USER_ROLE_CHANGED' ? 'selected' : '' }}>
                            Changement Rôle
                        </option>
                        <option value="IMPERSONATION_START" {{ request('action_type') === 'IMPERSONATION_START' ? 'selected' : '' }}>
                            Impersonation Début
                        </option>
                        <option value="FINANCIAL_VALIDATION" {{ request('action_type') === 'FINANCIAL_VALIDATION' ? 'selected' : '' }}>
                            Validation Financière
                        </option>
                        <option value="SYSTEM_SETTING_CHANGED" {{ request('action_type') === 'SYSTEM_SETTING_CHANGED' ? 'selected' : '' }}>
                            Paramètre Système
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Début</label>
                    <input type="date" 
                           name="date_from" 
                           value="{{ request('date_from') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                        Filtrer
                    </button>
                    <a href="{{ route('supervisor.action-logs.critical') }}" 
                       class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Critical Logs Timeline -->
        <div class="space-y-4">
            @forelse($logs as $log)
            <div class="bg-white rounded-xl shadow hover:shadow-lg transition">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4 flex-1">
                            <!-- Icon -->
                            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>

                            <!-- Content -->
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">
                                        CRITIQUE
                                    </span>
                                    <h4 class="font-bold text-gray-900">{{ $log->action_type }}</h4>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                                    <div>
                                        <p class="text-xs text-gray-600">Utilisateur</p>
                                        <p class="font-semibold text-gray-900">
                                            {{ $log->user?->name ?? 'N/A' }}
                                        </p>
                                        <p class="text-xs text-gray-600">{{ $log->user_role }}</p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-gray-600">Cible</p>
                                        <p class="font-semibold text-gray-900">
                                            {{ $log->target_type ?? 'N/A' }} #{{ $log->target_id ?? '-' }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-gray-600">Date & Heure</p>
                                        <p class="font-semibold text-gray-900">
                                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                                        </p>
                                        <p class="text-xs text-gray-600">{{ $log->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>

                                @if($log->metadata)
                                <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                                    <p class="text-xs font-semibold text-gray-700 mb-2">Détails Supplémentaires:</p>
                                    <pre class="text-xs text-gray-600 overflow-x-auto">{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                                @endif

                                @if($log->old_values || $log->new_values)
                                <div class="mt-4 grid grid-cols-2 gap-4">
                                    @if($log->old_values)
                                    <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                                        <p class="text-xs font-semibold text-red-800 mb-2">Avant:</p>
                                        <pre class="text-xs text-red-700">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                    @endif

                                    @if($log->new_values)
                                    <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                                        <p class="text-xs font-semibold text-green-800 mb-2">Après:</p>
                                        <pre class="text-xs text-green-700">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                    @endif
                                </div>
                                @endif

                                <div class="mt-3 flex items-center space-x-4 text-xs text-gray-600">
                                    <span>IP: {{ $log->ip_address ?? 'N/A' }}</span>
                                    @if($log->user_agent)
                                    <span title="{{ $log->user_agent }}" class="truncate max-w-xs">
                                        User Agent: {{ Str::limit($log->user_agent, 50) }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="ml-4">
                            <a href="{{ route('supervisor.action-logs.show', $log) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Détails →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl shadow p-12 text-center">
                <svg class="w-20 h-20 mx-auto text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">✅ Aucune Action Critique</h3>
                <p class="text-gray-600">Tout semble normal, aucune action critique détectée récemment.</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($logs->hasPages())
        <div class="bg-white rounded-xl shadow px-6 py-4">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</x-layouts.supervisor-new>
