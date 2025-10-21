<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bordereaux Retours Échanges</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page { margin: 0.5cm; }
            .no-print { display: none; }
            .page-break { page-break-after: always; }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Actions -->
    <div class="no-print fixed top-4 right-4 flex gap-2">
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium shadow-lg">
            🖨️ Imprimer
        </button>
        <a href="{{ route('depot-manager.exchanges.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium shadow-lg">
            ← Retour
        </a>
    </div>

    @foreach($returns as $index => $return)
    <div class="max-w-4xl mx-auto bg-white p-8 shadow-lg {{ $index < count($returns) - 1 ? 'page-break mb-8' : '' }}">
        <!-- Header -->
        <div class="border-b-4 border-red-500 pb-4 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-red-600">BORDEREAU RETOUR ÉCHANGE</h1>
                    <p class="text-gray-600 mt-2">{{ now()->format('d/m/Y à H:i') }}</p>
                </div>
                <div class="text-right">
                    <div class="bg-red-100 px-4 py-2 rounded-lg">
                        <p class="text-sm text-gray-600">Code Retour</p>
                        <p class="text-2xl font-bold text-red-700">{{ $return->package_code }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations Colis Original -->
        <div class="mb-6 bg-gray-50 p-4 rounded-lg">
            <h2 class="text-lg font-bold mb-3 text-gray-800">🔄 Colis Échange Original</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Code Colis</p>
                    <p class="font-semibold">{{ $return->originalPackage->package_code }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Livré le</p>
                    <p class="font-semibold">{{ $return->originalPackage->delivered_at ? $return->originalPackage->delivered_at->format('d/m/Y H:i') : 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Destinataire (Fournisseur) -->
        <div class="mb-6">
            <h2 class="text-lg font-bold mb-3 text-gray-800">📍 RETOUR VERS FOURNISSEUR</h2>
            <div class="border-2 border-gray-300 rounded-lg p-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Nom / Raison Sociale</p>
                        <p class="text-xl font-bold">{{ $return->recipient_data['name'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Téléphone</p>
                        <p class="text-xl font-bold">{{ $return->recipient_data['phone'] ?? 'N/A' }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-sm text-gray-600">Adresse Complète</p>
                        <p class="font-semibold text-lg">{{ $return->recipient_data['address'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Ville</p>
                        <p class="font-semibold">{{ $return->recipient_data['city'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Gouvernorat</p>
                        <p class="font-semibold">{{ $return->delegationTo->governorate ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations Retour -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-600 mb-1">Type</p>
                <p class="text-lg font-bold text-blue-700">RETOUR ÉCHANGE</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-600 mb-1">Montant COD</p>
                <p class="text-2xl font-bold text-green-700">0.000 DT</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-600 mb-1">Statut</p>
                <p class="text-lg font-bold text-purple-700">{{ $return->status }}</p>
            </div>
        </div>

        <!-- Instructions -->
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6">
            <h3 class="font-bold text-yellow-800 mb-2">⚠️ Instructions</h3>
            <ul class="text-sm text-yellow-700 space-y-1">
                <li>• Retourner le produit échangé au fournisseur</li>
                <li>• Vérifier l'état du produit avant remise</li>
                <li>• Obtenir signature du fournisseur</li>
                <li>• Aucun montant à collecter (COD = 0)</li>
            </ul>
        </div>

        <!-- Signatures -->
        <div class="grid grid-cols-2 gap-8 mt-8 pt-6 border-t-2 border-gray-300">
            <div>
                <p class="text-sm text-gray-600 mb-12">Signature Livreur</p>
                <div class="border-t-2 border-gray-400"></div>
            </div>
            <div>
                <p class="text-sm text-gray-600 mb-12">Signature Fournisseur</p>
                <div class="border-t-2 border-gray-400"></div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-6 pt-4 border-t border-gray-200 text-center text-xs text-gray-500">
            <p>Bordereau généré automatiquement le {{ now()->format('d/m/Y à H:i') }}</p>
            <p>Code: {{ $return->package_code }} | Original: {{ $return->originalPackage->package_code }}</p>
        </div>
    </div>
    @endforeach

    <script>
        // Auto-print option
        // window.addEventListener('load', function() {
        //     setTimeout(function() { window.print(); }, 500);
        // });
    </script>
</body>
</html>
