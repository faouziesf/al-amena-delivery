@extends('layouts.depot-manager')

@section('title', 'Détails Colis Retour')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-orange-900">Colis Retour: {{ $returnPackage->return_package_code }}</h1>
                    <p class="text-gray-600 mt-1">Détails complets du colis retour</p>
                </div>
                <a href="{{ route('depot.returns.manage') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    ← Retour
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Informations Générales -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">📋 Informations Générales</h2>
                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Code Retour:</span>
                        <span class="font-mono font-bold text-orange-900">{{ $returnPackage->return_package_code }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Colis Original:</span>
                        <span class="font-mono font-semibold">{{ $returnPackage->originalPackage->package_code ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Statut:</span>
                        <span class="px-2 py-1 rounded-full text-sm font-semibold
                            @if($returnPackage->status === 'AT_DEPOT') bg-blue-100 text-blue-800
                            @elseif($returnPackage->status === 'DELIVERED') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $returnPackage->status }}
                        </span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">COD:</span>
                        <span class="font-bold">{{ number_format($returnPackage->cod, 2) }} TND</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Créé par:</span>
                        <span>{{ $returnPackage->createdBy->name ?? 'Système' }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Date création:</span>
                        <span>{{ $returnPackage->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($returnPackage->printed_at)
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Imprimé le:</span>
                        <span class="text-green-600">{{ $returnPackage->printed_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                    @if($returnPackage->delivered_at)
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Livré le:</span>
                        <span class="text-green-600">{{ $returnPackage->delivered_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Destinataire (Client Original) -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">👤 Destinataire</h2>
                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Nom:</span>
                        <span class="font-semibold">{{ $returnPackage->recipient_info['name'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Téléphone:</span>
                        <span>{{ $returnPackage->recipient_info['phone'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Adresse:</span>
                        <span class="text-right">{{ $returnPackage->recipient_info['address'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Ville:</span>
                        <span>{{ $returnPackage->recipient_info['city'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Raison du Retour -->
            @if($returnPackage->return_reason)
            <div class="bg-orange-50 border-2 border-orange-200 rounded-xl p-6 md:col-span-2">
                <h2 class="text-xl font-bold text-orange-900 mb-3">📝 Raison du Retour</h2>
                <p class="text-gray-700">{{ $returnPackage->return_reason }}</p>
            </div>
            @endif

            <!-- Commentaires -->
            @if($returnPackage->comment)
            <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-6 md:col-span-2">
                <h2 class="text-xl font-bold text-blue-900 mb-3">💬 Commentaire</h2>
                <p class="text-gray-700">{{ $returnPackage->comment }}</p>
            </div>
            @endif

            <!-- Actions -->
            <div class="md:col-span-2 bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">⚡ Actions</h2>
                <div class="flex space-x-3">
                    @if(!$returnPackage->printed_at)
                    <a href="{{ route('depot.returns.print', $returnPackage) }}" target="_blank"
                       class="px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-semibold">
                        🖨️ Imprimer Bordereau
                    </a>
                    @else
                    <a href="{{ route('depot.returns.print', $returnPackage) }}" target="_blank"
                       class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold">
                        🖨️ Ré-imprimer Bordereau
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
