@extends('layouts.deliverer-modern')

@section('title', 'Scanner')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="scannerApp()">
    
    <!-- Header -->
    <div class="bg-indigo-600 text-white safe-top px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('deliverer.tournee') }}" class="mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold">Scanner Colis</h1>
                    <p class="text-indigo-200 text-sm">Caméra ou Saisie</p>
                </div>
            </div>
            <button @click="toggleCamera()" 
                    class="p-2 bg-white/20 rounded-lg hover:bg-white/30">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="p-6">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-700 rounded-xl">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-700 rounded-xl">
                ❌ {{ session('error') }}
            </div>
        @endif

        <!-- Saisie manuelle (simple et rapide) -->
        <div class="card p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">📝 Saisie Manuelle</h2>
            
            <form action="{{ route('deliverer.scan.submit') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Numéro de Colis
                    </label>
                    <input 
                        type="text" 
                        name="code" 
                        autofocus
                        placeholder="Scannez ou saisissez le code..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl text-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        required>
                </div>
                
                <button type="submit" class="w-full btn btn-primary">
                    🔍 Rechercher Colis
                </button>
            </form>
        </div>

        <!-- Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
            <h3 class="font-semibold text-blue-900 mb-2">💡 Instructions</h3>
            <ul class="text-sm text-blue-700 space-y-1">
                <li>• <strong>Code-barres</strong>: Utilisez un lecteur USB ou Bluetooth</li>
                <li>• <strong>QR Code</strong>: Saisissez ou scannez avec lecteur</li>
                <li>• <strong>Code tracking</strong>: Saisissez directement</li>
                <li>• Le colis sera automatiquement assigné à vous</li>
            </ul>
        </div>

        <!-- Derniers scans (si en session) -->
        @if(session('last_scans'))
            <div class="mt-6">
                <h3 class="font-bold text-gray-900 mb-3">Derniers scans</h3>
                @foreach(session('last_scans') as $scan)
                    <div class="card p-3 mb-2 flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $scan['code'] }}</div>
                            <div class="text-xs text-gray-500">{{ $scan['time'] }}</div>
                        </div>
                        <a href="/deliverer/task/{{ $scan['package_id'] }}" class="text-indigo-600 font-medium">
                            Voir →
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// Focus automatique sur input
document.addEventListener('DOMContentLoaded', function() {
    const input = document.querySelector('input[name="code"]');
    if (input) {
        input.focus();
        
        // Support lecteur code-barres (auto-submit après scan)
        let buffer = '';
        let timeout;
        
        input.addEventListener('keypress', function(e) {
            clearTimeout(timeout);
            
            if (e.key === 'Enter') {
                // Submit immédiat
                this.form.submit();
            } else {
                // Accumuler caractères
                buffer += e.key;
                
                // Si > 8 caractères en moins de 100ms = lecteur code-barres
                timeout = setTimeout(() => {
                    if (buffer.length >= 8) {
                        input.value = buffer;
                        this.form.submit();
                    }
                    buffer = '';
                }, 100);
            }
        });
    }
});
</script>
@endpush

@endsection
