@extends('layouts.deliverer')

@section('title', 'Signature de Livraison')

@section('content')
<div x-data="signatureApp()" x-init="init()" class="h-screen bg-white flex flex-col">

    <!-- Header Simple -->
    <div class="bg-green-600 text-white px-6 py-6 safe-top">
        <div class="text-center">
            <h1 class="text-2xl font-black mb-2">Preuve de Livraison</h1>
            <p class="text-lg font-semibold text-green-100">Veuillez signer ci-dessous :</p>
            <p class="text-xl font-bold text-white mt-2" x-text="clientName"></p>
        </div>
    </div>

    <!-- Zone de Signature Principale -->
    <div class="flex-1 flex flex-col p-6">

        <!-- Instructions -->
        <div class="text-center mb-6">
            <p class="text-lg text-gray-600 font-medium">Signature numérique du destinataire</p>
            <p class="text-sm text-gray-500 mt-2">Utilisez votre doigt pour signer dans la zone ci-dessous</p>
        </div>

        <!-- Canvas de Signature -->
        <div class="flex-1 bg-gray-50 border-4 border-dashed border-gray-300 rounded-2xl mb-6 relative overflow-hidden">
            <canvas id="signature-canvas"
                    class="w-full h-full cursor-crosshair touch-none"
                    style="touch-action: none;"
                    @touchstart="startDrawing($event)"
                    @touchmove="draw($event)"
                    @touchend="stopDrawing()"
                    @mousedown="startDrawing($event)"
                    @mousemove="draw($event)"
                    @mouseup="stopDrawing()"
                    @mouseleave="stopDrawing()">
            </canvas>

            <!-- Placeholder text quand vide -->
            <div x-show="!hasSignature"
                 class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="text-center text-gray-400">
                    <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    <p class="text-xl font-medium">Zone de signature</p>
                    <p class="text-sm mt-2">Signez ici avec votre doigt</p>
                </div>
            </div>
        </div>

        <!-- Informations de livraison -->
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-lg font-bold text-green-800">Livraison Confirmée</p>
                    <p class="text-sm text-green-600" x-text="deliveryDateTime"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Boutons d'Action -->
    <div class="bg-white border-t border-gray-200 p-6 safe-bottom">
        <div class="flex space-x-4">

            <!-- Bouton Effacer -->
            <button @click="clearSignature()"
                    :disabled="!hasSignature"
                    class="flex-1 bg-gray-600 text-white py-4 px-6 rounded-xl text-lg font-bold
                           hover:bg-gray-700 active:scale-98 transition-all
                           disabled:opacity-50 disabled:cursor-not-allowed
                           flex items-center justify-center space-x-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span>Effacer</span>
            </button>

            <!-- Bouton Valider -->
            <button @click="validateSignature()"
                    :disabled="!hasSignature || processing"
                    class="flex-2 bg-gradient-to-r from-green-600 to-green-700 text-white py-4 px-8 rounded-xl text-xl font-black
                           hover:from-green-700 hover:to-green-800 active:scale-98 transition-all
                           disabled:opacity-50 disabled:cursor-not-allowed
                           flex items-center justify-center space-x-4">
                <svg x-show="!processing" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                </svg>
                <div x-show="processing" class="animate-spin rounded-full h-6 w-6 border-b-2 border-white"></div>
                <span x-text="processing ? 'Enregistrement...' : 'Valider la Signature'"></span>
            </button>
        </div>

        <!-- Message d'état -->
        <div x-show="!hasSignature" class="text-center mt-4">
            <p class="text-sm text-gray-500">La signature est requise pour finaliser la livraison</p>
        </div>
    </div>

</div>

@push('scripts')
<script>
function signatureApp() {
    return {
        canvas: null,
        ctx: null,
        isDrawing: false,
        hasSignature: false,
        processing: false,
        clientName: 'Mohamed Salah',
        deliveryDateTime: new Date().toLocaleString('fr-FR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }),

        init() {
            this.setupCanvas();
            this.loadDeliveryInfo();
        },

        setupCanvas() {
            this.canvas = document.getElementById('signature-canvas');
            this.ctx = this.canvas.getContext('2d');

            // Configurer le canvas pour être responsive
            this.resizeCanvas();
            window.addEventListener('resize', () => this.resizeCanvas());

            // Configuration du style de trait
            this.ctx.strokeStyle = '#1f2937'; // Gris foncé
            this.ctx.lineWidth = 3;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';

            // Empêcher le défilement tactile sur le canvas
            this.canvas.addEventListener('touchstart', (e) => e.preventDefault());
            this.canvas.addEventListener('touchmove', (e) => e.preventDefault());
        },

        resizeCanvas() {
            const rect = this.canvas.getBoundingClientRect();
            const dpr = window.devicePixelRatio || 1;

            this.canvas.width = rect.width * dpr;
            this.canvas.height = rect.height * dpr;

            this.ctx.scale(dpr, dpr);

            // Restaurer les paramètres de style après redimensionnement
            this.ctx.strokeStyle = '#1f2937';
            this.ctx.lineWidth = 3;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
        },

        getEventPos(event) {
            const rect = this.canvas.getBoundingClientRect();
            const clientX = event.clientX || (event.touches && event.touches[0].clientX);
            const clientY = event.clientY || (event.touches && event.touches[0].clientY);

            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        },

        startDrawing(event) {
            event.preventDefault();
            this.isDrawing = true;

            const pos = this.getEventPos(event);
            this.ctx.beginPath();
            this.ctx.moveTo(pos.x, pos.y);
        },

        draw(event) {
            if (!this.isDrawing) return;

            event.preventDefault();
            const pos = this.getEventPos(event);

            this.ctx.lineTo(pos.x, pos.y);
            this.ctx.stroke();

            this.hasSignature = true;
        },

        stopDrawing() {
            if (this.isDrawing) {
                this.isDrawing = false;
                this.ctx.beginPath();
            }
        },

        clearSignature() {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            this.hasSignature = false;
        },

        async validateSignature() {
            if (!this.hasSignature) return;

            this.processing = true;

            try {
                // Convertir la signature en base64
                const signatureData = this.canvas.toDataURL('image/png');

                // Obtenir l'ID du package depuis l'URL
                const packageId = window.location.pathname.split('/').pop();

                const response = await fetch(`/deliverer/signature/${packageId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        signature: signatureData,
                        client_name: this.clientName,
                        delivery_time: new Date().toISOString()
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Succès - retourner à la tournée
                    window.location.href = '/deliverer/simple';
                } else {
                    alert(data.message || 'Erreur lors de l\'enregistrement de la signature');
                }

            } catch (error) {
                console.error('Erreur signature:', error);
                alert('Erreur de connexion. Veuillez réessayer.');
            } finally {
                this.processing = false;
            }
        },

        loadDeliveryInfo() {
            // Charger les informations de livraison depuis l'API si nécessaire
            // Pour l'instant on utilise des données simulées
        }
    }
}
</script>
@endpush

@push('styles')
<style>
    /* Assurer que le canvas utilise tout l'espace disponible */
    #signature-canvas {
        width: 100% !important;
        height: 100% !important;
    }

    /* Styles pour une expérience tactile optimale */
    .touch-none {
        touch-action: none;
    }

    /* Améliorer la responsivité sur mobile */
    @media (max-width: 640px) {
        .flex-2 {
            flex: 2;
        }
    }
</style>
@endpush

@endsection