@extends('layouts.deliverer')

@section('content')
<div class="flex items-center justify-center bg-gray-50 py-20">
    <div class="text-center">
        <div class="text-6xl mb-4">📱</div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Mode hors ligne</h1>
        <p class="text-gray-600 mb-6">Vos actions sont sauvegardées et seront synchronisées dès que vous serez reconnecté.</p>
        
        <div class="space-y-3">
            <button onclick="location.reload()" class="bg-purple-600 text-white px-6 py-2 rounded-lg">
                🔄 Réessayer la connexion
            </button>
            <br>
            <a href="/deliverer/dashboard" class="text-purple-600 hover:underline">
                ← Retour au dashboard
            </a>
        </div>
        
        <div class="mt-8 p-4 bg-blue-50 rounded-lg">
            <h3 class="font-semibold text-blue-900">Fonctionnalités disponibles hors ligne :</h3>
            <ul class="text-sm text-blue-700 mt-2">
                <li>✅ Scanner des colis</li>
                <li>✅ Marquer comme livré</li>
                <li>✅ Consulter le wallet</li>
                <li>✅ Voir l'historique</li>
            </ul>
        </div>
    </div>
</div>
@endsection