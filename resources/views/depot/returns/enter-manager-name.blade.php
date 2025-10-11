@extends('layouts.depot-manager')

@section('title', 'Scanner Retours - Identification')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 to-red-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full">
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">📦 Scanner Retours</h1>
            <p class="text-gray-600">Identification du chef de dépôt</p>
        </div>

        <form action="{{ route('depot.returns.dashboard') }}" method="GET" class="space-y-6">
            <div>
                <label for="depot_manager_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nom du Chef de Dépôt
                </label>
                <input type="text"
                       name="depot_manager_name"
                       id="depot_manager_name"
                       required
                       autofocus
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-lg"
                       placeholder="Entrez votre nom">
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-orange-600 to-red-600 text-white py-4 rounded-lg font-bold text-lg shadow-lg hover:shadow-xl transition-all">
                Démarrer Session de Scan
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="/" class="text-gray-600 hover:text-gray-800 text-sm">
                ← Retour à l'accueil
            </a>
        </div>
    </div>
</div>
@endsection
