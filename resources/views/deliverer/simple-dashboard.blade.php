@extends('layouts.deliverer')

@section('title', 'Dashboard Livreur')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
        <div class="px-6 py-6">
            <h1 class="text-2xl font-bold">Ma Tournée</h1>
            <p class="text-blue-100 mt-1">Dashboard simplifié - Version Legacy</p>
        </div>
    </div>

    <!-- Notice de redirection -->
    <div class="px-6 py-4">
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>Notice:</strong> Cette page est dépréciée. Vous serez automatiquement redirigé vers la nouvelle interface optimisée.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="px-6 pb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Accéder à la nouvelle interface -->
            <a href="{{ route('deliverer.run.sheet') }}"
               class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Run Sheet Optimisé</h3>
                        <p class="text-sm text-gray-600">Nouvelle interface PWA optimisée</p>
                    </div>
                </div>
            </a>

            <!-- Scanner QR -->
            <a href="{{ route('deliverer.scan.simple') }}"
               class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Scanner QR</h3>
                        <p class="text-sm text-gray-600">Scanner un colis rapidement</p>
                    </div>
                </div>
            </a>

            <!-- Wallet -->
            <a href="{{ route('deliverer.wallet.optimized') }}"
               class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Wallet</h3>
                        <p class="text-sm text-gray-600">Gérer mon portefeuille</p>
                    </div>
                </div>
            </a>

            <!-- Retraits espèces -->
            <a href="{{ route('deliverer.withdrawals.index') }}"
               class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Retraits Espèces</h3>
                        <p class="text-sm text-gray-600">Livraisons d'espèces</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Auto-redirection après 5 secondes -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        window.location.href = '{{ route("deliverer.run.sheet") }}';
    }, 5000);
});
</script>
@endsection