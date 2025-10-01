@extends('layouts.deliverer')

@section('title', 'Scanner QR')

@section('content')
<div class="h-full bg-gray-50" x-data="{ showScanner: true }">

    <!-- Header avec retour -->
    <div class="bg-blue-600 text-white px-6 py-4">
        <div class="flex items-center space-x-4">
            <a href="{{ route('deliverer.run.sheet') }}" class="text-white hover:text-blue-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold">Scanner QR</h1>
                <p class="text-blue-200 text-sm">Scannez un colis pour continuer</p>
            </div>
        </div>
    </div>

    <!-- Scanner QR Final Component -->
    <div class="flex-1 flex items-center justify-center p-4">
        <button @click="$dispatch('open-scanner')"
                class="w-full max-w-md bg-gradient-to-r from-blue-600 to-blue-700 text-white py-8 px-6 rounded-2xl shadow-lg hover:from-blue-700 hover:to-blue-800 transition-all transform hover:scale-105">

            <div class="text-center space-y-4">
                <!-- Icône Scanner -->
                <div class="w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center mx-auto">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                </div>

                <!-- Titre et description -->
                <div>
                    <h2 class="text-2xl font-bold mb-2">Scanner un Colis</h2>
                    <p class="text-blue-100 text-lg">Appuyez pour ouvrir le scanner</p>
                </div>

                <!-- Indicateurs -->
                <div class="flex items-center justify-center space-x-6 text-blue-100">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="text-sm">Caméra</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span class="text-sm">Manuel</span>
                    </div>
                </div>
            </div>
        </button>
    </div>

    <!-- Instructions -->
    <div class="px-6 pb-8">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200">
            <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Instructions
            </h3>
            <div class="space-y-2 text-sm text-gray-600">
                <div class="flex items-start space-x-2">
                    <span class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-blue-600 font-bold text-xs">1</span>
                    <span>Appuyez sur le bouton pour ouvrir le scanner</span>
                </div>
                <div class="flex items-start space-x-2">
                    <span class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-blue-600 font-bold text-xs">2</span>
                    <span>Choisissez entre le mode caméra ou saisie manuelle</span>
                </div>
                <div class="flex items-start space-x-2">
                    <span class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-blue-600 font-bold text-xs">3</span>
                    <span>Scannez le QR code ou tapez le code du colis</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scanner QR Final Component -->
@include('components.scanner-qr-final')

<!-- Scripts nécessaires pour le scanner -->
<script src="https://unpkg.com/quagga@0.12.1/dist/quagga.min.js"></script>
<script src="https://unpkg.com/jsqr@1.4.0/dist/jsQR.js"></script>

@endsection