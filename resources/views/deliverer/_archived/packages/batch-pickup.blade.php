@extends('layouts.deliverer')

@section('title', 'Scan Par Lot - Pickups')

@section('content')
<div class="bg-gray-50" x-data="batchPickupApp()">

    <!-- Header Section -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="px-4 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <svg class="w-7 h-7 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                        </svg>
                        Scan Par Lot - Pickups
                    </h1>
                    <p class="text-gray-600 mt-1">Scannez plusieurs colis pour accepter en lot</p>
                </div>
                <div class="flex gap-3">
                    <button @click="openScanner()"
                            :disabled="processing"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 disabled:opacity-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                        </svg>
                        Scanner
                    </button>
                    <a href="{{ route('deliverer.packages.available') }}"
                       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                        Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructions -->
    <div class="p-4">
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4">
            <div class="flex items-start space-x-3">
                <svg class="w-6 h-6 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-blue-900 mb-2">Comment utiliser le scan par lot</h3>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>1. Cliquez sur "Scanner" pour ouvrir le scanner</li>
                        <li>2. Scannez successivement tous les codes QR ou codes-barres</li>
                        <li>3. Vérifiez la liste des colis scannés</li>
                        <li>4. Cliquez sur "Accepter tout" pour confirmer tous les pickups</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats du scan en cours -->
    <div class="px-4 mb-4">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Scannés</p>
                        <p class="text-2xl font-bold text-blue-600" x-text="scannedPackages.length"></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Valides</p>
                        <p class="text-2xl font-bold text-green-600" x-text="validPackages.length"></p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Erreurs</p>
                        <p class="text-2xl font-bold text-red-600" x-text="errorPackages.length"></p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.99-.833-2.732 0L4.08 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Valeur COD</p>
                        <p class="text-2xl font-bold text-purple-600" x-text="formatAmount(totalCodValue)"></p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="px-4 mb-4" x-show="scannedPackages.length > 0">
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="flex flex-wrap gap-3">
                <button @click="acceptAllValid()"
                        :disabled="validPackages.length === 0 || processing"
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold disabled:opacity-50 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-show="!processing">Accepter tout (<span x-text="validPackages.length"></span>)</span>
                    <span x-show="processing">Traitement...</span>
                </button>

                <button @click="clearAll()"
                        :disabled="processing"
                        class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold disabled:opacity-50 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Vider tout
                </button>

                <button @click="removeErrors()"
                        :disabled="errorPackages.length === 0 || processing"
                        class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-3 rounded-lg disabled:opacity-50 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Supprimer erreurs
                </button>

                <button @click="retryErrors()"
                        :disabled="errorPackages.length === 0 || processing"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg disabled:opacity-50 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Réessayer erreurs
                </button>
            </div>
        </div>
    </div>

    <!-- Liste des colis scannés -->
    <div class="px-4 mb-6" x-show="scannedPackages.length > 0">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Colis Scannés</h3>
            </div>

            <div class="divide-y divide-gray-100">
                <template x-for="(pkg, index) in scannedPackages" :key="index">
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div :class="getStatusIconClass(pkg.status)" class="p-2 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              :d="getStatusIconPath(pkg.status)"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900" x-text="pkg.code"></p>
                                    <p class="text-sm text-gray-600" x-text="pkg.message"></p>
                                    <div x-show="pkg.details" class="text-xs text-gray-500 mt-1">
                                        <span x-text="pkg.details?.destination"></span>
                                        <span x-show="pkg.details?.cod_amount"
                                              class="ml-2 text-green-600 font-medium"
                                              x-text="'COD: ' + formatAmount(pkg.details.cod_amount)"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span :class="getStatusBadgeClass(pkg.status)"
                                      class="px-2 py-1 rounded-full text-xs font-medium"
                                      x-text="getStatusText(pkg.status)"></span>
                                <button @click="removePackage(index)"
                                        class="text-red-500 hover:text-red-700 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Message vide -->
    <div x-show="scannedPackages.length === 0" class="px-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-8 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun colis scanné</h3>
            <p class="text-gray-600 mb-6">Cliquez sur "Scanner" pour commencer à scanner les codes QR ou codes-barres des colis à accepter.</p>
            <button @click="openScanner()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
                Commencer le scan
            </button>
        </div>
    </div>

    <!-- Scanner QR intégré en mode batch -->
    <x-deliverer.scanner.qr-scanner mode="batch" :autoRedirect="false" />

    <!-- Toast notifications -->
    <div x-show="toastMessage" x-transition
         class="fixed bottom-4 right-4 bg-white border border-gray-200 rounded-lg shadow-lg p-4 max-w-sm z-50">
        <div class="flex items-center space-x-3">
            <div :class="toastType === 'success' ? 'text-green-600' : 'text-red-600'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          :d="toastType === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.99-.833-2.732 0L4.08 16.5c-.77.833.192 2.5 1.732 2.5z'"/>
                </svg>
            </div>
            <p class="text-gray-900" x-text="toastMessage"></p>
        </div>
    </div>
</div>

<script>
function batchPickupApp() {
    return {
        scannedPackages: [],
        processing: false,
        toastMessage: '',
        toastType: 'success',

        get validPackages() {
            return this.scannedPackages.filter(pkg => pkg.status === 'valid');
        },

        get errorPackages() {
            return this.scannedPackages.filter(pkg => pkg.status === 'error');
        },

        get totalCodValue() {
            return this.validPackages.reduce((sum, pkg) => {
                return sum + (pkg.details?.cod_amount || 0);
            }, 0);
        },

        init() {
            // Écouter les événements du scanner
            window.addEventListener('package-scanned', (event) => {
                this.handleScannedPackage(event.detail);
            });
        },

        openScanner() {
            window.dispatchEvent(new CustomEvent('open-scanner', {
                detail: { mode: 'batch' }
            }));
        },

        async handleScannedPackage(data) {
            const code = data.code;

            // Vérifier si le code est déjà scanné
            if (this.scannedPackages.find(pkg => pkg.code === code)) {
                this.showToast('Ce colis a déjà été scanné', 'error');
                return;
            }

            // Vérifier le colis via API
            try {
                const response = await fetch('/deliverer/packages/check-pickup', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ code: code })
                });

                const result = await response.json();

                const packageData = {
                    code: code,
                    status: result.success ? 'valid' : 'error',
                    message: result.message,
                    details: result.package || null,
                    timestamp: new Date().toISOString()
                };

                this.scannedPackages.push(packageData);

                // Vibration de retour
                if (navigator.vibrate) {
                    navigator.vibrate(result.success ? [100] : [100, 50, 100]);
                }

                this.showToast(
                    result.success ? 'Colis ajouté avec succès' : result.message,
                    result.success ? 'success' : 'error'
                );

            } catch (error) {
                console.error('Erreur lors de la vérification:', error);
                this.scannedPackages.push({
                    code: code,
                    status: 'error',
                    message: 'Erreur de connexion',
                    details: null,
                    timestamp: new Date().toISOString()
                });
                this.showToast('Erreur de connexion', 'error');
            }
        },

        async acceptAllValid() {
            if (this.validPackages.length === 0 || this.processing) return;

            this.processing = true;

            try {
                const codes = this.validPackages.map(pkg => pkg.code);

                const response = await fetch('/deliverer/packages/batch-accept', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ codes: codes })
                });

                const result = await response.json();

                if (result.success) {
                    this.showToast(`${result.accepted_count} colis acceptés avec succès!`, 'success');

                    // Supprimer les colis acceptés de la liste
                    this.scannedPackages = this.scannedPackages.filter(pkg =>
                        !codes.includes(pkg.code) || pkg.status === 'error'
                    );

                    // Rediriger vers la liste des pickups après un délai
                    setTimeout(() => {
                        window.location.href = '/deliverer/packages/my-pickups';
                    }, 2000);
                } else {
                    this.showToast(result.message || 'Erreur lors de l\'acceptation', 'error');
                }

            } catch (error) {
                console.error('Erreur:', error);
                this.showToast('Erreur lors de l\'acceptation en lot', 'error');
            }

            this.processing = false;
        },

        clearAll() {
            this.scannedPackages = [];
            this.showToast('Liste vidée', 'success');
        },

        removeErrors() {
            const errorCount = this.errorPackages.length;
            this.scannedPackages = this.scannedPackages.filter(pkg => pkg.status !== 'error');
            this.showToast(`${errorCount} erreurs supprimées`, 'success');
        },

        async retryErrors() {
            if (this.errorPackages.length === 0) return;

            this.processing = true;
            const errorCodes = this.errorPackages.map(pkg => pkg.code);

            // Relancer la vérification pour chaque code en erreur
            for (const code of errorCodes) {
                await this.handleScannedPackage({ code });
            }

            // Supprimer les anciennes erreurs
            this.scannedPackages = this.scannedPackages.filter(pkg =>
                !errorCodes.includes(pkg.code) || pkg.timestamp === Math.max(...this.scannedPackages.filter(p => p.code === pkg.code).map(p => p.timestamp))
            );

            this.processing = false;
        },

        removePackage(index) {
            this.scannedPackages.splice(index, 1);
        },

        getStatusIconClass(status) {
            switch (status) {
                case 'valid': return 'bg-green-100 text-green-600';
                case 'error': return 'bg-red-100 text-red-600';
                default: return 'bg-gray-100 text-gray-600';
            }
        },

        getStatusIconPath(status) {
            switch (status) {
                case 'valid': return 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
                case 'error': return 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.99-.833-2.732 0L4.08 16.5c-.77.833.192 2.5 1.732 2.5z';
                default: return 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10';
            }
        },

        getStatusBadgeClass(status) {
            switch (status) {
                case 'valid': return 'bg-green-100 text-green-800';
                case 'error': return 'bg-red-100 text-red-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        },

        getStatusText(status) {
            switch (status) {
                case 'valid': return 'Valide';
                case 'error': return 'Erreur';
                default: return 'Inconnu';
            }
        },

        showToast(message, type = 'success') {
            this.toastMessage = message;
            this.toastType = type;

            setTimeout(() => {
                this.toastMessage = '';
            }, 3000);
        },

        formatAmount(amount) {
            return new Intl.NumberFormat('fr-TN', {
                style: 'currency',
                currency: 'TND',
                minimumFractionDigits: 3
            }).format(amount);
        }
    }
}
</script>
@endsection