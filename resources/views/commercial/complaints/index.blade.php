@extends('layouts.commercial')

@section('title', 'Gestion des Réclamations')
@section('page-title', 'Gestion des Réclamations')
@section('page-description', 'Traitez et résolvez les réclamations clients en temps réel')

@section('header-actions')
<div class="flex items-center space-x-3">
    <div class="hidden lg:flex items-center space-x-4 text-sm">
        <div class="flex items-center space-x-2" x-show="stats.urgent > 0">
            <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
            <span class="text-red-600 font-medium" x-text="stats.urgent + ' urgentes'"></span>
        </div>
        <div class="text-gray-500">
            <span x-text="stats.resolved_today || 0"></span> résolues aujourd'hui
        </div>
    </div>
    <button onclick="generateComplaintsReport()" 
            class="px-4 py-2 text-purple-600 border border-purple-600 rounded-lg hover:bg-purple-50 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        Rapport
    </button>
    <button onclick="openBulkActionsModal()"
            class="px-4 py-2 bg-purple-300 text-purple-800 rounded-lg hover:bg-purple-400 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
        </svg>
        Actions Groupées
    </button>
</div>
@endsection

@section('content')
<div x-data="complaintsApp()" x-init="init()">
    <!-- Priority Alert Banner -->
    <div x-show="stats.urgent > 0" 
         class="bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl p-4 mb-6 shadow-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Attention Urgente Requise !</h3>
                    <p x-text="`${stats.urgent} réclamations urgentes nécessitent votre attention immédiate`"></p>
                </div>
            </div>
            <button onclick="showUrgentOnly()" 
                    class="bg-white bg-opacity-20 hover:bg-opacity-30 px-4 py-2 rounded-lg font-medium transition-colors">
                Traiter Maintenant
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-red-500 to-red-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Urgentes</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.urgent || {{ $stats['urgent'] ?? 0 }}"></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">En Attente</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.pending || {{ $stats['pending'] ?? 0 }}"></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">En Cours</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.in_progress || {{ $stats['in_progress'] ?? 0 }}"></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Résolues Aujourd'hui</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.resolved_today || {{ $stats['resolved_today'] ?? 0 }}"></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Mes Assignations</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="myStats.assigned_to_me || {{ $myStats['assigned_to_me'] ?? 0 }}"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Filters Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-purple-100 mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button @click="setActiveTab('all')" 
                        :class="activeTab === 'all' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Toutes les Réclamations
                    <span class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2.5 rounded-full text-xs" x-text="stats.total || 0"></span>
                </button>
                <button @click="setActiveTab('urgent')" 
                        :class="activeTab === 'urgent' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Urgentes
                    <span class="ml-2 bg-red-100 text-red-900 py-0.5 px-2.5 rounded-full text-xs" x-text="stats.urgent || 0"></span>
                </button>
                <button @click="setActiveTab('pending')" 
                        :class="activeTab === 'pending' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    En Attente
                    <span class="ml-2 bg-orange-100 text-orange-900 py-0.5 px-2.5 rounded-full text-xs" x-text="stats.pending || 0"></span>
                </button>
                <button @click="setActiveTab('mine')" 
                        :class="activeTab === 'mine' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Mes Assignations
                    <span class="ml-2 bg-purple-100 text-purple-900 py-0.5 px-2.5 rounded-full text-xs" x-text="myStats.assigned_to_me || 0"></span>
                </button>
                <button @click="setActiveTab('cod_changes')" 
                        :class="activeTab === 'cod_changes' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Changements COD
                    <span class="ml-2 bg-blue-100 text-blue-900 py-0.5 px-2.5 rounded-full text-xs" x-text="stats.cod_changes || 0"></span>
                </button>
            </nav>
        </div>

        <!-- Advanced Filters -->
        <div class="p-6">
            <form method="GET" action="{{ route('commercial.complaints.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Code réclamation, colis, client..."
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                            <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Tous les types</option>
                            <option value="CHANGE_COD" {{ request('type') == 'CHANGE_COD' ? 'selected' : '' }}>Changement COD</option>
                            <option value="DELIVERY_DELAY" {{ request('type') == 'DELIVERY_DELAY' ? 'selected' : '' }}>Retard livraison</option>
                            <option value="REQUEST_RETURN" {{ request('type') == 'REQUEST_RETURN' ? 'selected' : '' }}>Demande retour</option>
                            <option value="RETURN_DELAY" {{ request('type') == 'RETURN_DELAY' ? 'selected' : '' }}>Retard retour</option>
                            <option value="RESCHEDULE_TODAY" {{ request('type') == 'RESCHEDULE_TODAY' ? 'selected' : '' }}>Report aujourd'hui</option>
                            <option value="FOURTH_ATTEMPT" {{ request('type') == 'FOURTH_ATTEMPT' ? 'selected' : '' }}>4ème tentative</option>
                            <option value="CUSTOM" {{ request('type') == 'CUSTOM' ? 'selected' : '' }}>Personnalisé</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priorité</label>
                        <select name="priority" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Toutes priorités</option>
                            <option value="URGENT" {{ request('priority') == 'URGENT' ? 'selected' : '' }}>Urgente</option>
                            <option value="HIGH" {{ request('priority') == 'HIGH' ? 'selected' : '' }}>Haute</option>
                            <option value="NORMAL" {{ request('priority') == 'NORMAL' ? 'selected' : '' }}>Normale</option>
                            <option value="LOW" {{ request('priority') == 'LOW' ? 'selected' : '' }}>Basse</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end space-x-2">
                        <button type="submit"
                                class="px-4 py-2 bg-purple-300 text-purple-800 rounded-lg hover:bg-purple-400 focus:ring-2 focus:ring-purple-500 transition-colors">
                            Filtrer
                        </button>
                        <a href="{{ route('commercial.complaints.index') }}" 
                           class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Reset
                        </a>
                    </div>
                </div>
                
                <!-- Quick Action Checkboxes -->
                <div class="flex items-center space-x-6 pt-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="unassigned" value="1" 
                               {{ request('unassigned') ? 'checked' : '' }}
                               class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <span class="ml-2 text-sm text-gray-700">Non assignées uniquement</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="assigned_to_me" value="1" 
                               {{ request('assigned_to_me') ? 'checked' : '' }}
                               class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <span class="ml-2 text-sm text-gray-700">Mes assignations</span>
                    </label>
                </div>
            </form>
        </div>
    </div>

    <!-- Complaints List -->
    <div class="bg-white rounded-xl shadow-sm border border-purple-100 overflow-hidden">
        @if($complaints->count() > 0)
            <!-- Selection Header -->
            <div class="px-6 py-3 bg-gray-50 border-b border-gray-200" 
                 x-show="selectedComplaints.length > 0" x-transition>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <span class="text-sm font-medium text-gray-900" 
                              x-text="`${selectedComplaints.length} réclamation(s) sélectionnée(s)`"></span>
                        <button @click="selectedComplaints = []" 
                                class="text-sm text-gray-500 hover:text-gray-700">
                            Désélectionner tout
                        </button>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="bulkAssignToMe()" 
                                class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors">
                            M'assigner
                        </button>
                        <button onclick="bulkMarkUrgent()" 
                                class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition-colors">
                            Marquer urgent
                        </button>
                        <button onclick="openBulkResolveModal()" 
                                class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition-colors">
                            Résoudre
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input type="checkbox" @change="toggleAllComplaints($event)" 
                                       class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Réclamation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client & Colis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type & Priorité</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assignée à</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Créée</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($complaints as $complaint)
                        <tr class="hover:bg-gray-50 transition-colors group 
                                   {{ $complaint->priority === 'URGENT' ? 'bg-red-50 border-l-4 border-red-500' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" :value="{{ $complaint->id }}" 
                                       x-model="selectedComplaints"
                                       class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-start space-x-3">
                                    @if($complaint->priority === 'URGENT')
                                        <div class="flex-shrink-0 mt-1">
                                            <div class="w-6 h-6 bg-red-500 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-white animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/>
                                                </svg>
                                            </div>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $complaint->complaint_code }}</div>
                                        <div class="text-sm text-gray-500">{{ $complaint->created_at->diffForHumans() }}</div>
                                        @if($complaint->priority === 'URGENT')
                                            <div class="text-xs text-red-600 font-medium mt-1">
                                                <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                </svg>
                                                Action urgente requise
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $complaint->client->name }}</div>
                                    <div class="text-sm text-gray-600">{{ $complaint->client->email }}</div>
                                    <div class="text-sm text-blue-600 hover:text-blue-800">
                                        <a href="{{ route('commercial.packages.show', $complaint->package) }}">
                                            {{ $complaint->package->package_code }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    
                                    <div>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $complaint->priority_color }}">
                                            {{ $complaint->priority_display }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($complaint->status === 'PENDING')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                        En attente
                                    </span>
                                @elseif($complaint->status === 'IN_PROGRESS')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        En cours
                                    </span>
                                @elseif($complaint->status === 'RESOLVED')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Résolue
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        Rejetée
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($complaint->assignedCommercial)
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center">
                                            <span class="text-xs font-medium text-purple-600">
                                                {{ substr($complaint->assignedCommercial->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <span class="text-sm text-gray-900">{{ $complaint->assignedCommercial->name }}</span>
                                    </div>
                                @else
                                    <button onclick="assignToMe({{ $complaint->id }})" 
                                            class="text-sm text-purple-600 hover:text-purple-800 font-medium">
                                        M'assigner
                                    </button>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div>{{ $complaint->created_at->format('d/m/Y H:i') }}</div>
                                <div class="text-xs text-gray-400">{{ $complaint->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <!-- Quick Actions based on type -->
                                    @if($complaint->type === 'CHANGE_COD' && $complaint->canBeResolved())
                                        <button onclick="quickCodChange({{ $complaint->id }}, {{ $complaint->package->id }}, {{ $complaint->package->cod_amount }})" 
                                                class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-100 transition-colors"
                                                title="Modifier COD">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                    @endif

                                    <!-- View Details -->
                                    <a href="{{ route('commercial.complaints.show', $complaint) }}" 
                                       class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-100 transition-colors"
                                       title="Voir détails">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    <!-- Quick Resolve -->
                                    @if($complaint->canBeResolved())
                                        <button onclick="quickResolve({{ $complaint->id }})" 
                                                class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-100 transition-colors"
                                                title="Résolution rapide">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </button>
                                    @endif

                                    <!-- Mark as Urgent -->
                                    @if($complaint->priority !== 'URGENT' && $complaint->canBeResolved())
                                        <button onclick="markAsUrgent({{ $complaint->id }})" 
                                                class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-100 transition-colors"
                                                title="Marquer urgent">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                            </svg>
                                        </button>
                                    @endif

                                    <!-- More Actions -->
                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open" 
                                                class="text-gray-400 hover:text-gray-600 p-1 rounded hover:bg-gray-100 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"/>
                                            </svg>
                                        </button>
                                        
                                        <div x-show="open" @click.away="open = false" x-transition
                                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border z-10">
                                            <div class="py-1">
                                                @if($complaint->canBeResolved())
                                                    <button onclick="openResolveModal({{ $complaint->id }})" 
                                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        Résoudre avancée
                                                    </button>
                                                    <button onclick="rejectComplaint({{ $complaint->id }})" 
                                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        Rejeter
                                                    </button>
                                                    <hr class="my-1">
                                                @endif
                                                <button onclick="duplicateComplaint({{ $complaint->id }})" 
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    Dupliquer
                                                </button>
                                                <button onclick="exportComplaintData({{ $complaint->id }})" 
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    Exporter
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $complaints->appends(request()->query())->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune réclamation trouvée</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(request()->hasAny(['search', 'type', 'priority', 'status']))
                        Aucune réclamation ne correspond à vos critères.
                    @else
                        Toutes les réclamations sont traitées !
                    @endif
                </p>
                <div class="mt-6">
                    @if(request()->hasAny(['search', 'type', 'priority', 'status']))
                        <a href="{{ route('commercial.complaints.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Voir toutes les réclamations
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('modals')
<!-- Quick COD Change Modal -->
<div id="cod-change-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-bold text-gray-900">Modification COD Rapide</h3>
                <button onclick="closeCodChangeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="cod-change-form" class="p-6 space-y-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="text-sm text-blue-700">
                        <div>Colis: <span id="cod-package-code" class="font-medium"></span></div>
                        <div>COD actuel: <span id="cod-current-amount" class="font-bold"></span> DT</div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau montant COD (DT)</label>
                    <input type="number" id="cod-new-amount" step="0.001" min="0" required 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motif de la modification</label>
                    <textarea id="cod-reason" rows="3" required
                              placeholder="Expliquez pourquoi le COD est modifié..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                </div>
                
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="cod-emergency" 
                           class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                    <label for="cod-emergency" class="text-sm text-gray-700">
                        Modification d'urgence (priorité maximale)
                    </label>
                </div>
                
                <div class="flex space-x-3 pt-4">
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">
                        Modifier COD
                    </button>
                    <button type="button" onclick="closeCodChangeModal()" 
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick Resolve Modal -->
<div id="quick-resolve-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-bold text-gray-900">Résolution Rapide</h3>
                <button onclick="closeQuickResolveModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="quick-resolve-form" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Action de résolution</label>
                    <select id="resolve-action" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="simple_resolve">Résolution simple</option>
                        <option value="reschedule">Reprogrammer livraison</option>
                        <option value="return_package">Retourner le colis</option>
                        <option value="fourth_attempt">Programmer 4ème tentative</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes de résolution</label>
                    <textarea id="resolve-notes" rows="4" required
                              placeholder="Décrivez comment la réclamation a été résolue..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                </div>
                
                <div id="reschedule-date-field" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de reprogrammation</label>
                    <input type="date" id="reschedule-date" min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                
                <div class="flex space-x-3 pt-4">
                    <button type="submit" class="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700">
                        Résoudre
                    </button>
                    <button type="button" onclick="closeQuickResolveModal()" 
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
function complaintsApp() {
    return {
        activeTab: 'all',
        selectedComplaints: [],
        stats: {
            total: {{ $stats['total'] ?? 0 }},
            pending: {{ $stats['pending'] ?? 0 }},
            urgent: {{ $stats['urgent'] ?? 0 }},
            in_progress: {{ $stats['in_progress'] ?? 0 }},
            resolved_today: {{ $stats['resolved_today'] ?? 0 }},
            cod_changes: 0
        },
        myStats: {
            assigned_to_me: {{ $myStats['assigned_to_me'] ?? 0 }},
            resolved_by_me_today: {{ $myStats['resolved_by_me_today'] ?? 0 }}
        },

        init() {
            this.loadStats();
            
            // Auto-refresh every 30 seconds
            setInterval(() => {
                this.loadStats();
            }, 30000);
        },

        async loadStats() {
            try {
                const response = await fetch('/commercial/complaints/api/stats');
                if (response.ok) {
                    const data = await response.json();
                    this.stats = { ...this.stats, ...data };
                    this.myStats = { 
                        assigned_to_me: data.assigned_to_me || 0,
                        resolved_by_me_today: data.resolved_by_me_today || 0
                    };
                }
            } catch (error) {
                console.error('Erreur chargement stats réclamations:', error);
            }
        },

        setActiveTab(tab) {
            this.activeTab = tab;
            const params = new URLSearchParams();
            
            switch (tab) {
                case 'urgent':
                    params.set('priority', 'URGENT');
                    break;
                case 'pending':
                    params.set('status', 'PENDING');
                    break;
                case 'mine':
                    params.set('assigned_to_me', '1');
                    break;
                case 'cod_changes':
                    params.set('type', 'CHANGE_COD');
                    break;
            }
            
            if (params.toString()) {
                window.location.href = `${window.location.pathname}?${params.toString()}`;
            } else {
                window.location.href = window.location.pathname;
            }
        },

        toggleAllComplaints(event) {
            const complaintIds = Array.from(document.querySelectorAll('tbody input[type="checkbox"]'))
                                     .map(cb => parseInt(cb.value));
            
            if (event.target.checked) {
                this.selectedComplaints = complaintIds;
            } else {
                this.selectedComplaints = [];
            }
        }
    }
}

// Global variables
let currentComplaintId = null;
let currentPackageId = null;

// Quick COD Change
function quickCodChange(complaintId, packageId, currentAmount) {
    currentComplaintId = complaintId;
    currentPackageId = packageId;
    
    document.getElementById('cod-current-amount').textContent = parseFloat(currentAmount).toFixed(3);
    document.getElementById('cod-new-amount').value = currentAmount;
    document.getElementById('cod-change-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCodChangeModal() {
    document.getElementById('cod-change-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('cod-change-form').reset();
    currentComplaintId = null;
    currentPackageId = null;
}

// Quick Resolve
function quickResolve(complaintId) {
    currentComplaintId = complaintId;
    document.getElementById('quick-resolve-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeQuickResolveModal() {
    document.getElementById('quick-resolve-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('quick-resolve-form').reset();
    currentComplaintId = null;
}

// Action Functions
async function assignToMe(complaintId) {
    try {
        const response = await fetch(`/commercial/complaints/${complaintId}/assign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (response.ok) {
            showToast('Réclamation assignée avec succès', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            const data = await response.json();
            showToast(data.message || 'Erreur lors de l\'assignation', 'error');
        }
    } catch (error) {
        showToast('Erreur de connexion', 'error');
    }
}

async function markAsUrgent(complaintId) {
    try {
        const response = await fetch(`/commercial/complaints/${complaintId}/urgent`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (response.ok) {
            showToast('Réclamation marquée comme urgente', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Erreur lors du marquage', 'error');
        }
    } catch (error) {
        showToast('Erreur de connexion', 'error');
    }
}

function showUrgentOnly() {
    window.location.href = '{{ route("commercial.complaints.index") }}?priority=URGENT';
}

function generateComplaintsReport() {
    showToast('Génération du rapport en cours...', 'info');
    // TODO: Implement report generation
}

function openBulkActionsModal() {
    showToast('Actions groupées à implémenter', 'info');
    // TODO: Implement bulk actions modal
}

// Form Handlers
document.addEventListener('DOMContentLoaded', function() {
    // COD Change Form
    const codForm = document.getElementById('cod-change-form');
    if (codForm) {
        codForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                new_cod_amount: parseFloat(document.getElementById('cod-new-amount').value),
                reason: document.getElementById('cod-reason').value,
                emergency: document.getElementById('cod-emergency').checked,
                complaint_id: currentComplaintId
            };
            
            try {
                const response = await fetch(`/commercial/complaints/packages/${currentPackageId}/modify-cod`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                });

                if (response.ok) {
                    showToast('COD modifié avec succès', 'success');
                    closeCodChangeModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    const data = await response.json();
                    showToast(data.message || 'Erreur lors de la modification', 'error');
                }
            } catch (error) {
                showToast('Erreur de connexion', 'error');
            }
        });
    }
    
    // Quick Resolve Form
    const resolveForm = document.getElementById('quick-resolve-form');
    if (resolveForm) {
        const actionSelect = document.getElementById('resolve-action');
        const rescheduleField = document.getElementById('reschedule-date-field');
        
        actionSelect.addEventListener('change', function() {
            if (this.value === 'reschedule') {
                rescheduleField.classList.remove('hidden');
                document.getElementById('reschedule-date').required = true;
            } else {
                rescheduleField.classList.add('hidden');
                document.getElementById('reschedule-date').required = false;
            }
        });
        
        resolveForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                action: document.getElementById('resolve-action').value,
                resolution_notes: document.getElementById('resolve-notes').value,
                reschedule_date: document.getElementById('reschedule-date').value,
                emergency: false
            };
            
            try {
                const response = await fetch(`/commercial/complaints/${currentComplaintId}/resolve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                });

                if (response.ok) {
                    showToast('Réclamation résolue avec succès', 'success');
                    closeQuickResolveModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    const data = await response.json();
                    showToast(data.message || 'Erreur lors de la résolution', 'error');
                }
            } catch (error) {
                showToast('Erreur de connexion', 'error');
            }
        });
    }
});
</script>
@endpush