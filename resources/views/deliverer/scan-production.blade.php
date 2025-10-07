@extends('layouts.deliverer-modern')

@section('title', 'Scanner Colis')

@section('content')
<div class="bg-gradient-to-br from-indigo-500 via-purple-600 to-purple-700 px-4 pb-4">
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-3xl shadow-xl p-6">
            <div class="text-center mb-6">
                <div class="text-6xl mb-3">📷</div>
                <h4 class="text-xl font-bold text-gray-800">Scanner un Colis</h4>
                <p class="text-gray-500 text-sm">Entrez ou scannez le code du colis</p>
            </div>

            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Scan Form -->
            <form action="{{ route('deliverer.scan.submit') }}" method="POST" class="mb-6">
                @csrf
                <div class="mb-6">
                    <input type="text" 
                           name="code" 
                           class="w-full text-2xl font-bold text-center tracking-wider px-6 py-4 border-3 border-indigo-600 rounded-2xl focus:ring-4 focus:ring-indigo-200 focus:border-indigo-600 outline-none transition-all" 
                           placeholder="CODE COLIS"
                           autofocus
                           required>
                </div>

                <button type="submit" 
                        class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all active:scale-95">
                    🔍 Rechercher
                </button>
            </form>

            <!-- Scan History -->
            @if(session('last_scans'))
                <div class="mb-6">
                    <h6 class="text-sm font-semibold text-gray-600 mb-3">Derniers scans</h6>
                    <div class="space-y-2">
                        @foreach(session('last_scans') as $scan)
                            <div class="bg-gray-50 rounded-xl p-3">
                                <div class="flex justify-between items-center">
                                    <div class="font-semibold text-gray-800">{{ $scan['code'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $scan['time'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="space-y-2">
                <a href="{{ route('deliverer.scan.multi') }}" 
                   class="block w-full bg-indigo-50 text-indigo-700 text-center py-3 rounded-xl font-semibold hover:bg-indigo-100 transition-colors">
                    📸 Scanner Multiple
                </a>
                <a href="{{ route('deliverer.menu') }}" 
                   class="block w-full bg-gray-100 text-gray-700 text-center py-3 rounded-xl font-semibold hover:bg-gray-200 transition-colors">
                    ← Retour au menu
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
