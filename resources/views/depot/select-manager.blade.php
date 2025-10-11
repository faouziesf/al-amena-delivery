<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sélection Chef Dépôt</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-500 to-purple-600 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <div class="text-6xl mb-4">🏭</div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Scanner Dépôt</h1>
            <p class="text-gray-600">Identifiez-vous pour commencer</p>
        </div>

        <form action="{{ route('depot.scan.dashboard') }}" method="GET">
            <div class="mb-6">
                <label for="depot_manager_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Votre nom (Chef Dépôt)
                </label>
                <input
                    type="text"
                    id="depot_manager_name"
                    name="depot_manager_name"
                    required
                    autofocus
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 transition text-lg"
                    placeholder="Ex: Omar, Ahmed, Manager Depot..."
                >
            </div>

            <button
                type="submit"
                class="w-full bg-indigo-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-indigo-700 transition shadow-lg text-lg">
                Démarrer Scanner
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-500">
            <p>Le nom sera associé aux colis scannés</p>
        </div>
    </div>
</body>
</html>
