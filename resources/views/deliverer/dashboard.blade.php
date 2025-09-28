@extends('layouts.deliverer')

@section('title', 'Dashboard Livreur')

@section('content')
<div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full text-center">
        <div class="mb-6">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Interface Simplifiée</h2>
            <p class="text-gray-600">Découvrez notre nouvelle interface PWA optimisée pour le terrain.</p>
        </div>

        <div class="space-y-4">
            <a href="{{ route('deliverer.simple.dashboard') }}"
               class="block w-full bg-blue-600 text-white py-4 px-6 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                Accéder à la Nouvelle Interface
            </a>

            <p class="text-sm text-gray-500">
                ✨ Ultra-rapide • 📱 Mobile-first • 🚀 PWA
            </p>
        </div>
    </div>
</div>
@endsection