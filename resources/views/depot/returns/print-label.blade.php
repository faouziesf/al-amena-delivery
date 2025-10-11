<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bordereau Retour - {{ $returnPackage->return_package_code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>
    <style>
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none; }
            @page { margin: 1cm; }
        }
    </style>
</head>
<body class="bg-white p-8">
    <!-- Bouton d'impression -->
    <div class="no-print mb-4 text-center">
        <button onclick="window.print()" class="bg-orange-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-orange-700">
            🖨️ Imprimer
        </button>
        <button onclick="window.close()" class="bg-gray-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-gray-700 ml-2">
            ✕ Fermer
        </button>
    </div>

    <!-- Bordereau -->
    <div class="max-w-4xl mx-auto border-4 border-orange-600 rounded-lg p-8">
        <!-- En-tête -->
        <div class="text-center border-b-4 border-orange-600 pb-6 mb-6">
            <h1 class="text-4xl font-bold text-orange-600 mb-2">COLIS RETOUR</h1>
            <p class="text-xl text-gray-700">{{ config('app.name', 'Al-Amena Delivery') }}</p>
        </div>

        <div class="grid grid-cols-2 gap-8">
            <!-- Informations du colis -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b-2 pb-2">📦 Informations Colis</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-600">Code Retour</p>
                        <p class="text-3xl font-bold font-mono text-orange-900">{{ $returnPackage->return_package_code }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Colis Original</p>
                        <p class="text-xl font-mono font-semibold">{{ $returnPackage->originalPackage->package_code ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Date de création</p>
                        <p class="text-lg">{{ $returnPackage->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    @if($returnPackage->return_reason)
                    <div class="bg-orange-50 border-2 border-orange-300 rounded p-3 mt-4">
                        <p class="text-sm font-semibold text-orange-900 mb-1">Raison du retour:</p>
                        <p class="text-sm text-gray-700">{{ $returnPackage->return_reason }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- QR Code -->
            <div class="text-center">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b-2 pb-2">🔍 Code QR</h2>
                <div id="qrcode" class="mx-auto mb-4" style="width: 200px; height: 200px;"></div>
                <p class="text-sm text-gray-600">Scannez pour suivre</p>
            </div>
        </div>

        <!-- Destinataire -->
        <div class="mt-8 border-t-4 border-orange-600 pt-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">👤 DESTINATAIRE (Client Original)</h2>
            <div class="bg-gray-50 border-2 border-gray-300 rounded-lg p-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-600">Nom Complet</p>
                        <p class="text-xl font-bold text-gray-900">{{ $returnPackage->recipient_info['name'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Téléphone</p>
                        <p class="text-xl font-bold text-gray-900">{{ $returnPackage->recipient_info['phone'] ?? 'N/A' }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-sm text-gray-600">Adresse</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $returnPackage->recipient_info['address'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Ville</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $returnPackage->recipient_info['city'] ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expéditeur (Entreprise) -->
        <div class="mt-8 border-t-2 border-gray-300 pt-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">🏢 EXPÉDITEUR</h2>
            <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-6">
                <p class="text-xl font-bold text-gray-900">{{ $returnPackage->sender_info['name'] ?? config('app.name') }}</p>
                <p class="text-lg text-gray-700">{{ $returnPackage->sender_info['address'] ?? 'Adresse entreprise' }}</p>
                <p class="text-lg text-gray-700">Tél: {{ $returnPackage->sender_info['phone'] ?? 'N/A' }}</p>
            </div>
        </div>

        <!-- Signatures -->
        <div class="mt-8 grid grid-cols-2 gap-8 border-t-2 border-gray-300 pt-6">
            <div>
                <p class="text-sm text-gray-600 mb-2">Signature Chef Dépôt</p>
                <div class="border-2 border-gray-400 h-24 rounded"></div>
            </div>
            <div>
                <p class="text-sm text-gray-600 mb-2">Signature Destinataire</p>
                <div class="border-2 border-gray-400 h-24 rounded"></div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-gray-500 border-t-2 pt-4">
            <p>Bordereau imprimé le {{ now()->format('d/m/Y à H:i') }}</p>
            <p class="mt-1">{{ config('app.name') }} - Système de gestion des retours</p>
        </div>
    </div>

    <script>
        // Générer le QR Code
        var qr = qrcode(0, 'M');
        qr.addData('{{ $returnPackage->return_package_code }}');
        qr.make();
        document.getElementById('qrcode').innerHTML = qr.createImgTag(5, 10);

        // Auto-print après chargement
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
