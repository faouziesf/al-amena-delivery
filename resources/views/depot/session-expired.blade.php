<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Terminée</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full text-center">
        
        <!-- Icône -->
        <div class="mb-6">
            <div class="w-24 h-24 bg-orange-100 rounded-full flex items-center justify-center mx-auto">
                <svg class="w-12 h-12 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>

        <!-- Message principal -->
        <h1 class="text-2xl font-bold text-gray-900 mb-3">
            {{ $message }}
        </h1>

        <!-- Raison -->
        <p class="text-gray-600 mb-6">
            {{ $reason }}
        </p>

        @if(isset($validated_count) && $validated_count > 0)
        <!-- Informations de validation -->
        <div class="bg-green-50 border-2 border-green-200 rounded-xl p-4 mb-6">
            <div class="text-green-800 font-bold text-lg mb-1">
                ✅ {{ $validated_count }} colis validés
            </div>
            @if(isset($validated_at))
            <div class="text-green-600 text-sm">
                Validé le {{ $validated_at->format('d/m/Y à H:i') }}
            </div>
            @endif
        </div>
        @endif

        <!-- Message d'instruction -->
        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4 mb-6">
            <p class="text-blue-800 text-sm font-medium">
                📱 Demandez au chef de dépôt de générer un nouveau QR code pour continuer le scan
            </p>
        </div>

        <!-- Boutons d'action -->
        <div class="space-y-3">
            <a href="{{ route('depot.enter.code') }}"
               class="inline-block w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-bold py-3 px-6 rounded-xl hover:from-purple-700 hover:to-indigo-700 transition-all shadow-lg">
                🔑 Saisir un Nouveau Code
            </a>

            <a href="{{ route('depot.scan.dashboard') }}"
               class="inline-block w-full bg-white text-gray-700 font-bold py-3 px-6 rounded-xl hover:bg-gray-50 transition-all border-2 border-gray-300">
                🏭 Retour au Dashboard PC
            </a>
        </div>

        <!-- Note -->
        <p class="text-gray-500 text-xs mt-6">
            Cette page s'affiche car la session de scan a été terminée ou a expiré
        </p>
    </div>

</body>
</html>
