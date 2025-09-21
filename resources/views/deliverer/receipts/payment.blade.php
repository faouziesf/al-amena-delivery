@extends('layouts.deliverer')

@section('title', 'Reçu de paiement')

@section('content')
<div x-data="paymentReceipt({
    payment: {{ json_encode($payment ?? []) }},
    packages: {{ json_encode($packages ?? []) }},
    deliverer: {{ json_encode(auth()->user() ?? []) }}
})" class="max-w-2xl mx-auto p-4">

    <!-- Receipt Container -->
    <div id="receipt-content" class="bg-white rounded-xl shadow-lg border p-6 space-y-6">

        <!-- Header -->
        <div class="text-center border-b border-gray-200 pb-6">
            <div class="mb-4">
                <img src="{{ asset('images/logo.png') }}" alt="Al-Amena Delivery" class="h-16 mx-auto mb-2">
                <h1 class="text-2xl font-bold text-gray-900">Al-Amena Delivery</h1>
                <p class="text-gray-600">Service de livraison professionnel</p>
            </div>

            <div class="bg-green-50 rounded-lg p-4">
                <h2 class="text-xl font-semibold text-green-900 mb-2">Reçu de paiement COD</h2>
                <p class="text-sm text-green-700">
                    Date : <span x-text="formatDate(payment.created_at || new Date())"></span>
                </p>
                <p class="text-sm text-green-700">
                    Référence : <span x-text="payment.reference || generateReference()"></span>
                </p>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                Résumé du paiement
            </h3>

            <div class="bg-blue-50 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Montant total :</span>
                            <p class="text-2xl font-bold text-green-600" x-text="formatAmount(payment.total_amount)"></p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Nombre de colis :</span>
                            <p class="text-lg font-semibold text-gray-900" x-text="packages.length + ' colis'"></p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Mode de paiement :</span>
                            <p class="text-lg font-semibold text-gray-900" x-text="getPaymentMethod()"></p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Statut :</span>
                            <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                Payé
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deliverer Information -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                Informations du livreur
            </h3>

            <div class="bg-gray-50 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Nom :</span>
                        <p class="font-semibold text-gray-900" x-text="deliverer.name || 'N/A'"></p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Téléphone :</span>
                        <p class="font-semibold text-gray-900" x-text="deliverer.phone || 'N/A'"></p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">ID Livreur :</span>
                        <p class="font-mono text-gray-900" x-text="deliverer.deliverer_id || deliverer.id || 'N/A'"></p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Zone :</span>
                        <p class="font-semibold text-gray-900" x-text="deliverer.zone || 'N/A'"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Package Details -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                Détails des colis livrés
            </h3>

            <div class="space-y-3">
                <template x-for="(pkg, index) in packages" :key="index">
                    <div class="bg-white border rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
                            <div>
                                <span class="text-gray-500">Code colis :</span>
                                <p class="font-mono font-semibold text-gray-900" x-text="pkg.tracking_number"></p>
                            </div>
                            <div>
                                <span class="text-gray-500">Destinataire :</span>
                                <p class="font-semibold text-gray-900" x-text="pkg.recipient_name"></p>
                            </div>
                            <div>
                                <span class="text-gray-500">Montant COD :</span>
                                <p class="font-semibold text-green-600" x-text="formatAmount(pkg.cod_amount)"></p>
                            </div>
                            <div>
                                <span class="text-gray-500">Livré le :</span>
                                <p class="text-gray-900" x-text="formatDate(pkg.delivered_at)"></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Payment Breakdown -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                Détail des montants
            </h3>

            <div class="bg-yellow-50 rounded-lg p-4">
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total colis COD :</span>
                        <span class="font-semibold text-gray-900" x-text="formatAmount(calculateSubtotals().codTotal)"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total frais de livraison :</span>
                        <span class="font-semibold text-gray-900" x-text="formatAmount(calculateSubtotals().deliveryTotal)"></span>
                    </div>
                    <div class="border-t border-yellow-200 pt-2">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-gray-900">Montant total encaissé :</span>
                            <span class="text-xl font-bold text-green-600" x-text="formatAmount(payment.total_amount)"></span>
                        </div>
                    </div>
                </div>

                <!-- Commission Info -->
                <div class="mt-4 pt-4 border-t border-yellow-200">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600">Commission livreur :</span>
                        <span class="font-semibold text-blue-600" x-text="formatAmount(calculateCommission())"></span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600">Montant à reverser :</span>
                        <span class="font-semibold text-gray-900" x-text="formatAmount(payment.total_amount - calculateCommission())"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Info -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                Informations de transaction
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="space-y-2">
                    <div>
                        <span class="text-gray-500">ID Transaction :</span>
                        <p class="font-mono font-semibold text-gray-900" x-text="payment.id || generateTransactionId()"></p>
                    </div>
                    <div>
                        <span class="text-gray-500">Date de traitement :</span>
                        <p class="text-gray-900" x-text="formatDateTime(payment.processed_at || payment.created_at)"></p>
                    </div>
                </div>
                <div class="space-y-2">
                    <div>
                        <span class="text-gray-500">Méthode d'encaissement :</span>
                        <p class="text-gray-900" x-text="getPaymentMethod()"></p>
                    </div>
                    <div>
                        <span class="text-gray-500">Statut de vérification :</span>
                        <span class="inline-flex px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800">
                            Vérifié
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code for Verification -->
        <div class="text-center space-y-4">
            <h3 class="text-lg font-semibold text-gray-900">Code de vérification</h3>
            <div class="flex justify-center">
                <div id="qr-code" class="bg-white p-4 border rounded-lg"></div>
            </div>
            <p class="text-xs text-gray-500">
                Scannez ce code pour vérifier l'authenticité de ce reçu de paiement
            </p>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 pt-6 text-center text-sm text-gray-600 space-y-2">
            <p>
                <strong>Al-Amena Delivery</strong> - Service de livraison de confiance
            </p>
            <p>
                Email: finance@alamena.dz | Tel: +213 123 456 789
            </p>
            <p>
                Adresse: 123 Rue de la Livraison, Alger, Algérie
            </p>
            <p class="text-xs">
                Ce reçu constitue une preuve officielle de paiement COD.
                Conservez-le pour vos records comptables.
            </p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-6 space-y-3">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <button @click="printReceipt"
                    class="bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center space-x-2">
                <i class="fas fa-print"></i>
                <span>Imprimer</span>
            </button>

            <button @click="downloadPDF"
                    class="bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center space-x-2">
                <i class="fas fa-download"></i>
                <span>Télécharger PDF</span>
            </button>

            <button @click="shareReceipt"
                    class="bg-purple-600 text-white px-4 py-3 rounded-lg hover:bg-purple-700 transition-colors flex items-center justify-center space-x-2">
                <i class="fas fa-share"></i>
                <span>Partager</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <button @click="sendToSupervisor"
                    class="bg-orange-600 text-white px-4 py-3 rounded-lg hover:bg-orange-700 transition-colors flex items-center justify-center space-x-2">
                <i class="fas fa-paper-plane"></i>
                <span>Envoyer au superviseur</span>
            </button>

            <button @click="addToWallet"
                    class="bg-yellow-600 text-white px-4 py-3 rounded-lg hover:bg-yellow-700 transition-colors flex items-center justify-center space-x-2">
                <i class="fas fa-wallet"></i>
                <span>Voir dans le portefeuille</span>
            </button>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-6 text-center">
        <a href="{{ route('deliverer.wallet.index') }}"
           class="inline-flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors">
            <i class="fas fa-arrow-left"></i>
            <span>Retour au portefeuille</span>
        </a>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.3/qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('paymentReceipt', (data) => ({
        payment: data.payment || {},
        packages: data.packages || [],
        deliverer: data.deliverer || {},

        init() {
            this.generateQRCode();
        },

        formatDate(dateString) {
            if (!dateString) return new Date().toLocaleDateString('fr-DZ');
            return new Date(dateString).toLocaleDateString('fr-DZ');
        },

        formatDateTime(dateString) {
            if (!dateString) return new Date().toLocaleString('fr-DZ');
            return new Date(dateString).toLocaleString('fr-DZ');
        },

        formatAmount(amount) {
            if (!amount) return '0 DA';
            return new Intl.NumberFormat('fr-DZ', {
                style: 'currency',
                currency: 'DZD',
                minimumFractionDigits: 0
            }).format(amount).replace('DZD', 'DA');
        },

        generateReference() {
            const date = new Date();
            const timestamp = date.getTime().toString().slice(-6);
            return `PAY-${date.getFullYear()}${String(date.getMonth() + 1).padStart(2, '0')}${String(date.getDate()).padStart(2, '0')}-${timestamp}`;
        },

        generateTransactionId() {
            return `TXN-${Date.now().toString().slice(-10)}`;
        },

        getPaymentMethod() {
            const method = this.payment.payment_method || 'cash';
            const methods = {
                cash: 'Espèces',
                card: 'Carte bancaire',
                mobile: 'Paiement mobile',
                check: 'Chèque',
                mixed: 'Mixte'
            };
            return methods[method] || 'Espèces';
        },

        calculateSubtotals() {
            const codTotal = this.packages.reduce((sum, pkg) => sum + (pkg.cod_amount || 0), 0);
            const deliveryTotal = this.packages.reduce((sum, pkg) => sum + (pkg.delivery_fee || 0), 0);
            return { codTotal, deliveryTotal };
        },

        calculateCommission() {
            // Calculer la commission du livreur (exemple: 5% du total ou montant fixe par colis)
            const commissionRate = 0.05; // 5%
            const fixedCommissionPerPackage = 50; // 50 DA par colis

            // Utiliser la commission fixe par colis pour simplicité
            return this.packages.length * fixedCommissionPerPackage;
        },

        generateQRCode() {
            const qrData = {
                type: 'payment_receipt',
                payment_id: this.payment.id,
                reference: this.payment.reference || this.generateReference(),
                total_amount: this.payment.total_amount,
                deliverer_id: this.deliverer.id,
                packages_count: this.packages.length,
                created_at: this.payment.created_at,
                verification_url: `${window.location.origin}/verify-payment/${this.payment.id || 'unknown'}`
            };

            QRCode.toCanvas(document.getElementById('qr-code'), JSON.stringify(qrData), {
                width: 150,
                margin: 2,
                color: {
                    dark: '#000000',
                    light: '#FFFFFF'
                }
            }, (error) => {
                if (error) console.error('Erreur génération QR code:', error);
            });
        },

        printReceipt() {
            window.print();
        },

        async downloadPDF() {
            try {
                const { jsPDF } = window.jspdf;
                const element = document.getElementById('receipt-content');

                const canvas = await html2canvas(element, {
                    scale: 2,
                    useCORS: true,
                    allowTaint: true
                });

                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF();

                const imgWidth = 210;
                const pageHeight = 295;
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                let heightLeft = imgHeight;

                let position = 0;

                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;

                while (heightLeft >= 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }

                const filename = `Recu-Paiement-${this.payment.reference || 'Payment'}-${this.formatDate()}.pdf`;
                pdf.save(filename);

            } catch (error) {
                console.error('Erreur génération PDF:', error);
                alert('Erreur lors de la génération du PDF');
            }
        },

        shareReceipt() {
            if (navigator.share) {
                navigator.share({
                    title: 'Reçu de paiement COD Al-Amena',
                    text: `Reçu de paiement COD pour ${this.packages.length} colis - Montant: ${this.formatAmount(this.payment.total_amount)}`,
                    url: window.location.href
                }).catch(console.error);
            } else {
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Lien copié dans le presse-papiers');
                });
            }
        },

        sendToSupervisor() {
            const subject = `Reçu de paiement COD - ${this.payment.reference}`;
            const body = `Bonjour,

Veuillez trouver ci-joint le reçu de paiement COD.

Détails:
- Référence: ${this.payment.reference || this.generateReference()}
- Montant total: ${this.formatAmount(this.payment.total_amount)}
- Nombre de colis: ${this.packages.length}
- Date: ${this.formatDateTime(this.payment.created_at)}
- Livreur: ${this.deliverer.name}

Lien vers le reçu détaillé: ${window.location.href}

Cordialement,
${this.deliverer.name}`;

            const emailUrl = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
            window.location.href = emailUrl;
        },

        addToWallet() {
            // Redirect to wallet page
            window.location.href = '/deliverer/wallet';
        }
    }));
});
</script>

<style>
@media print {
    .max-w-2xl {
        max-width: none !important;
    }

    .mt-6, .space-y-3, .grid-cols-1, .md\\:grid-cols-3, .md\\:grid-cols-2 {
        display: none !important;
    }

    #receipt-content {
        box-shadow: none !important;
        border: none !important;
        border-radius: 0 !important;
    }
}
</style>
@endpush