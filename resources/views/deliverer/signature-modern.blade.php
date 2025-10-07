@extends('layouts.deliverer-modern')

@section('title', 'Signature')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="signatureApp({{ $package->id }})">
    
    <!-- Header -->
    <div class="bg-gradient-to-br from-indigo-600 to-purple-600 text-white safe-top">
        <div class="px-6 py-6">
            <h1 class="text-2xl font-bold mb-2">✍️ Signature de Livraison</h1>
            <p class="text-indigo-200 text-sm">Preuve de réception obligatoire</p>
        </div>
    </div>

    <div class="p-4 space-y-4 pb-32">
        
        <!-- Infos colis -->
        <div class="card p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div class="text-sm text-gray-500">Colis</div>
                    <div class="font-mono font-bold text-lg">{{ $package->tracking_number ?? $package->package_code }}</div>
                </div>
                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-semibold">
                    Livré
                </span>
            </div>

            <div class="space-y-2">
                <div class="flex items-center text-gray-700">
                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="font-medium">{{ $package->recipient_name }}</span>
                </div>

                @if($package->cod_amount > 0)
                <div class="flex items-center text-green-600 bg-green-50 p-3 rounded-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <div class="text-xs">COD Collecté</div>
                        <div class="font-bold text-lg">{{ number_format($package->cod_amount, 2) }} TND</div>
                    </div>
                </div>
                @endif

                @if($package->est_echange)
                <div class="flex items-center text-orange-600 bg-orange-50 p-3 rounded-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="text-sm font-semibold">Ancien colis échangé récupéré</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Zone signature -->
        <div class="card p-5">
            <h3 class="font-bold text-lg mb-2">Signature du Destinataire</h3>
            <p class="text-gray-600 text-sm mb-4">Le destinataire doit signer pour confirmer la réception</p>

            <div class="relative">
                <canvas id="signatureCanvas" 
                        class="w-full border-2 border-gray-300 rounded-xl bg-white"
                        style="height: 250px; touch-action: none;"
                        @touchstart="startDrawing($event)"
                        @touchmove="draw($event)"
                        @touchend="stopDrawing()"
                        @mousedown="startDrawing($event)"
                        @mousemove="draw($event)"
                        @mouseup="stopDrawing()"></canvas>
                
                <button @click="clearSignature()" 
                        class="absolute top-3 right-3 px-4 py-2 bg-red-500 text-white rounded-lg font-semibold hover:bg-red-600 active:scale-95 transition-all">
                    🗑️ Effacer
                </button>

                <div class="absolute bottom-3 left-3 text-gray-400 text-sm pointer-events-none">
                    Signez ici
                </div>
            </div>

            <div x-show="!hasSignature" class="mt-3 text-center text-sm text-gray-500">
                ⚠️ La signature est obligatoire pour valider la livraison
            </div>
        </div>

        <!-- Aperçu signature -->
        <div x-show="hasSignature" class="card p-4 bg-green-50 border-green-200">
            <div class="flex items-center text-green-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="font-semibold">Signature capturée</span>
            </div>
        </div>
    </div>

    <!-- Actions footer -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 safe-bottom z-20">
        <div class="space-y-3">
            <button @click="saveSignature()" 
                    :disabled="!hasSignature || processing"
                    :class="!hasSignature || processing ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700'"
                    class="w-full btn text-white">
                <span x-show="!processing">✅ Valider et Terminer</span>
                <span x-show="processing">
                    <div class="spinner mx-auto" style="width: 20px; height: 20px; border-width: 2px;"></div>
                </span>
            </button>

            <a href="{{ route('deliverer.tournee') }}" 
               class="block text-center py-3 text-gray-600 font-semibold hover:bg-gray-50 rounded-xl transition-colors">
                Retour à la tournée
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
function signatureApp(packageId) {
    return {
        processing: false,
        canvas: null,
        ctx: null,
        drawing: false,
        hasSignature: false,

        init() {
            // Init canvas
            this.canvas = document.getElementById('signatureCanvas');
            this.ctx = this.canvas.getContext('2d');
            this.canvas.width = this.canvas.offsetWidth;
            this.canvas.height = 250;
            
            // Style du tracé
            this.ctx.strokeStyle = '#000';
            this.ctx.lineWidth = 2;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
        },

        startDrawing(e) {
            e.preventDefault();
            this.drawing = true;
            this.hasSignature = true;
            
            const rect = this.canvas.getBoundingClientRect();
            const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
            const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
            
            this.ctx.beginPath();
            this.ctx.moveTo(x, y);
        },

        draw(e) {
            if (!this.drawing) return;
            e.preventDefault();
            
            const rect = this.canvas.getBoundingClientRect();
            const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
            const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
            
            this.ctx.lineTo(x, y);
            this.ctx.stroke();
        },

        stopDrawing() {
            this.drawing = false;
        },

        clearSignature() {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            this.hasSignature = false;
            vibrate([30]);
        },

        async saveSignature() {
            if (!this.hasSignature) {
                showToast('Veuillez capturer une signature', 'warning');
                return;
            }

            if (!confirm('Confirmer la signature et terminer la livraison ?')) {
                return;
            }

            try {
                this.processing = true;
                vibrate([50]);

                const signatureData = this.canvas.toDataURL('image/png');

                await apiRequest(`/deliverer/signature/${packageId}`, {
                    method: 'POST',
                    body: {
                        signature: signatureData
                    }
                });

                showToast('Signature enregistrée avec succès !', 'success');
                vibrate([100, 50, 100]);

                setTimeout(() => {
                    window.location.href = '/deliverer/tournee';
                }, 1500);
            } catch (error) {
                console.error('Erreur:', error);
                showToast(error.message || 'Erreur lors de l\'enregistrement', 'error');
                this.processing = false;
            }
        }
    }
}
</script>
@endpush

@endsection
