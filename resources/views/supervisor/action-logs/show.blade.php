@extends('layouts.supervisor')

@section('title', 'Détails Action Log')

@section('content')
<div class="px-4 py-6 max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📋 Détails de l'Action</h1>
            <p class="text-gray-600">Log #{{ $actionLog->id }}</p>
        </div>
        <a href="{{ route('supervisor.action-logs.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg">
            ← Retour
        </a>
    </div>

    <div class="space-y-6">
        <!-- Informations Principales -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Informations Générales</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-gray-600">Date et Heure</label>
                    <p class="font-semibold">{{ $actionLog->created_at->format('d/m/Y à H:i:s') }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Utilisateur</label>
                    <p class="font-semibold">{{ $actionLog->user_name ?? 'Système' }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Rôle</label>
                    <p class="font-semibold">{{ $actionLog->user_role ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Action</label>
                    <p class="font-semibold">{{ $actionLog->action }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Type d'Entité</label>
                    <p class="font-semibold">{{ $actionLog->entity_type ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">ID Entité</label>
                    <p class="font-semibold">{{ $actionLog->entity_id ?? 'N/A' }}</p>
                </div>
                <div class="col-span-2">
                    <label class="text-sm text-gray-600">Description</label>
                    <p class="font-semibold">{{ $actionLog->description ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Valeurs Modifiées -->
        @if($actionLog->old_values || $actionLog->new_values)
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Modifications</h2>
            
            <div class="grid grid-cols-2 gap-6">
                @if($actionLog->old_values)
                <div>
                    <h3 class="text-sm font-semibold text-red-600 mb-2">❌ Anciennes Valeurs</h3>
                    <div class="bg-red-50 rounded p-3">
                        <pre class="text-xs">{{ json_encode($actionLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
                @endif

                @if($actionLog->new_values)
                <div>
                    <h3 class="text-sm font-semibold text-green-600 mb-2">✅ Nouvelles Valeurs</h3>
                    <div class="bg-green-50 rounded p-3">
                        <pre class="text-xs">{{ json_encode($actionLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Informations Techniques -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Informations Techniques</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-gray-600">Adresse IP</label>
                    <p class="font-mono text-sm">{{ $actionLog->ip_address ?? 'N/A' }}</p>
                </div>
                <div class="col-span-2">
                    <label class="text-sm text-gray-600">User Agent</label>
                    <p class="font-mono text-xs break-all">{{ $actionLog->user_agent ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Informations Utilisateur -->
        @if($actionLog->user)
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Détails Utilisateur</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-gray-600">Nom</label>
                    <p class="font-semibold">{{ $actionLog->user->name }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Email</label>
                    <p class="font-semibold">{{ $actionLog->user->email }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Rôle</label>
                    <p class="font-semibold">{{ $actionLog->user->role }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Téléphone</label>
                    <p class="font-semibold">{{ $actionLog->user->phone ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
