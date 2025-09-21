<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de Recharge - {{ $topup->request_code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .print-break { page-break-after: always; }
        }
        .receipt-paper {
            max-width: 80mm;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-4">
        <!-- Actions d'impression -->
        <div class="max-w-md mx-auto mb-4 no-print">
            <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Reçu de Recharge</h2>
                <div class="flex space-x-2">
                    <button onclick="window.print()" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        <span>Imprimer</span>
                    </button>
                    <button onclick="window.close()" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Fermer
                    </button>
                </div>
            </div>
        </div>

        <!-- Reçu principal -->
        <div class="max-w-md mx-auto bg-white shadow-lg">
            <div class="receipt-paper mx-auto p-4 text-sm">
                <!-- En-tête -->
                <div class="text-center mb-4 border-b border-dashed border-gray-400 pb-4">
                    <h1 class="text-lg font-bold">AL-AMENA DELIVERY</h1>
                    <p class="text-xs text-gray-600">Service de livraison</p>
                    <p class="text-xs text-gray-600">📞 +216 XX XXX XXX</p>
                    <p class="text-xs text-gray-600 mt-2 font-semibold">REÇU DE RECHARGE</p>
                </div>

                <!-- Informations transaction -->
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Code:</span>
                        <span class="font-mono font-semibold">{{ $topup->request_code }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Date:</span>
                        <span>{{ $topup->processed_at?->format('d/m/Y H:i') ?? $topup->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Statut:</span>
                        <span class="font-semibold {{ $topup->status === 'VALIDATED' ? 'text-green-600' : ($topup->status === 'PENDING' ? 'text-orange-600' : 'text-red-600') }}">
                            {{ $topup->status_display }}
                        </span>
                    </div>
                </div>

                <div class="border-t border-dashed border-gray-400 pt-2 mb-4"></div>

                <!-- Informations client -->
                <div class="mb-4">
                    <h3 class="font-semibold mb-2">👤 CLIENT</h3>
                    <div class="space-y-1 text-xs">
                        <div class="flex justify-between">
                            <span>Nom:</span>
                            <span class="font-semibold">{{ $topup->client->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Téléphone:</span>
                            <span>{{ $topup->client->phone }}</span>
                        </div>
                        @if($topup->client->email)
                        <div class="flex justify-between">
                            <span>Email:</span>
                            <span class="text-xs">{{ $topup->client->email }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="border-t border-dashed border-gray-400 pt-2 mb-4"></div>

                <!-- Détails recharge -->
                <div class="mb-4">
                    <h3 class="font-semibold mb-2">💰 RECHARGE</h3>
                    <div class="space-y-1">
                        <div class="flex justify-between">
                            <span>Méthode:</span>
                            <span>{{ $topup->method_display }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold">
                            <span>Montant:</span>
                            <span>{{ number_format($topup->amount, 3) }} DT</span>
                        </div>
                    </div>
                </div>

                @if($topup->notes)
                <div class="mb-4">
                    <h3 class="font-semibold mb-2">📝 NOTES</h3>
                    <p class="text-xs text-gray-700 break-words">{{ $topup->notes }}</p>
                </div>
                @endif

                <div class="border-t border-dashed border-gray-400 pt-2 mb-4"></div>

                <!-- Informations livreur -->
                <div class="mb-4">
                    <h3 class="font-semibold mb-2">🚚 LIVREUR</h3>
                    <div class="space-y-1 text-xs">
                        <div class="flex justify-between">
                            <span>Nom:</span>
                            <span>{{ $topup->processedBy->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Téléphone:</span>
                            <span>{{ $topup->processedBy->phone ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <div class="border-t border-dashed border-gray-400 pt-2 mb-4"></div>

                <!-- Métadonnées -->
                @if($topup->metadata && is_array($topup->metadata))
                <div class="mb-4">
                    <h3 class="font-semibold mb-2">ℹ️ DÉTAILS</h3>
                    <div class="space-y-1 text-xs">
                        @if(isset($topup->metadata['deliverer_name']))
                        <div class="flex justify-between">
                            <span>Traité par:</span>
                            <span>{{ $topup->metadata['deliverer_name'] }}</span>
                        </div>
                        @endif
                        @if(isset($topup->metadata['client_phone_verified']))
                        <div class="flex justify-between">
                            <span>Tél. vérifié:</span>
                            <span>{{ $topup->metadata['client_phone_verified'] }}</span>
                        </div>
                        @endif
                        @if(isset($topup->metadata['location']))
                        <div class="flex justify-between">
                            <span>Lieu:</span>
                            <span>{{ $topup->metadata['location'] === 'field_topup' ? 'Terrain' : $topup->metadata['location'] }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- QR Code (optionnel) -->
                <div class="text-center mb-4">
                    <div class="bg-gray-100 p-4 rounded border-2 border-dashed border-gray-300">
                        <p class="text-xs text-gray-600 mb-2">Code de vérification</p>
                        <p class="font-mono text-sm font-bold">{{ $topup->request_code }}</p>
                        <!-- Espace pour QR code si implémenté -->
                        <div class="w-16 h-16 bg-gray-200 mx-auto mt-2 flex items-center justify-center text-xs text-gray-500">
                            QR
                        </div>
                    </div>
                </div>

                <!-- Pied de page -->
                <div class="border-t border-dashed border-gray-400 pt-4 text-center">
                    <p class="text-xs text-gray-600 mb-2">
                        Merci de faire confiance à Al-Amena Delivery
                    </p>
                    <p class="text-xs text-gray-500">
                        Ce reçu fait foi de la transaction
                    </p>
                    <p class="text-xs text-gray-500 mt-2">
                        {{ now()->format('d/m/Y H:i:s') }}
                    </p>
                </div>

                @if($topup->status === 'VALIDATED')
                <div class="mt-4 p-2 bg-green-50 border border-green-200 rounded text-center">
                    <p class="text-xs text-green-700 font-semibold">✅ RECHARGE VALIDÉE</p>
                    <p class="text-xs text-green-600">Fonds ajoutés au wallet client</p>
                </div>
                @elseif($topup->status === 'PENDING')
                <div class="mt-4 p-2 bg-orange-50 border border-orange-200 rounded text-center">
                    <p class="text-xs text-orange-700 font-semibold">⏳ EN ATTENTE DE VALIDATION</p>
                    <p class="text-xs text-orange-600">La recharge sera validée prochainement</p>
                </div>
                @endif

                <!-- Signature client (espace) -->
                <div class="mt-6 border-t border-dashed border-gray-400 pt-4">
                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div>
                            <p class="text-gray-600 mb-2">Signature Client:</p>
                            <div class="h-12 border-b border-gray-300"></div>
                        </div>
                        <div>
                            <p class="text-gray-600 mb-2">Signature Livreur:</p>
                            <div class="h-12 border-b border-gray-300"></div>
                        </div>
                    </div>
                </div>

                <!-- Mentions légales -->
                <div class="mt-4 text-center">
                    <p class="text-xs text-gray-400">
                        Al-Amena Delivery - Tous droits réservés
                    </p>
                </div>
            </div>
        </div>

        <!-- Copie client (version simplifiée) -->
        <div class="print-break"></div>
        
        <div class="max-w-md mx-auto bg-white shadow-lg mt-8">
            <div class="receipt-paper mx-auto p-4 text-sm">
                <div class="text-center mb-4 border-b border-dashed border-gray-400 pb-2">
                    <h1 class="text-base font-bold">AL-AMENA DELIVERY</h1>
                    <p class="text-xs text-gray-600 font-semibold">COPIE CLIENT - REÇU DE RECHARGE</p>
                </div>

                <div class="space-y-2 mb-4 text-xs">
                    <div class="flex justify-between">
                        <span>Code:</span>
                        <span class="font-mono font-semibold">{{ $topup->request_code }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Date:</span>
                        <span>{{ $topup->processed_at?->format('d/m/Y H:i') ?? $topup->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Client:</span>
                        <span class="font-semibold">{{ $topup->client->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Téléphone:</span>
                        <span>{{ $topup->client->phone }}</span>
                    </div>
                    <div class="flex justify-between text-base font-bold">
                        <span>Montant rechargé:</span>
                        <span>{{ number_format($topup->amount, 3) }} DT</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Statut:</span>
                        <span class="font-semibold {{ $topup->status === 'VALIDATED' ? 'text-green-600' : ($topup->status === 'PENDING' ? 'text-orange-600' : 'text-red-600') }}">
                            {{ $topup->status_display }}
                        </span>
                    </div>
                </div>

                <div class="border-t border-dashed border-gray-400 pt-2 text-center">
                    <p class="text-xs text-gray-600">
                        Conservez ce reçu comme justificatif
                    </p>
                    <p class="text-xs text-gray-500">
                        Support: +216 XX XXX XXX
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-impression si demandée
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('auto_print') === '1') {
            window.onload = function() {
                setTimeout(() => {
                    window.print();
                }, 500);
            };
        }

        // Fermer après impression
        window.addEventListener('afterprint', function() {
            if (urlParams.get('auto_close') === '1') {
                window.close();
            }
        });
    </script>
</body>
</html>