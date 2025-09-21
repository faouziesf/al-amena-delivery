@extends('layouts.deliverer')

@section('title', 'Reçu de recharge')

@section('content')
<div x-data="topupReceipt({
    topup: {{ json_encode($topup ?? []) }},
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

            <div class="bg-purple-50 rounded-lg p-4">
                <h2 class="text-xl font-semibold text-purple-900 mb-2">Reçu de recharge portefeuille</h2>
                <p class="text-sm text-purple-700">
                    Date : <span x-text="formatDate(topup.created_at || new Date())"></span>
                </p>
                <p class="text-sm text-purple-700">
                    Référence : <span x-text="topup.reference || generateReference()"></span>
                </p>
            </div>
        </div>

        <!-- Topup Summary -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                Détails de la recharge
            </h3>

            <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg p-6">
                <div class="text-center mb-4">
                    <div class="bg-white rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-3 shadow-lg">
                        <i class="fas fa-plus-circle text-green-500 text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Recharge effectuée</h3>
                    <p class="text-3xl font-bold text-green-600" x-text="formatAmount(topup.amount)"></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="bg-white rounded-lg p-3">
                        <span class="text-gray-500">Type de recharge :</span>
                        <p class="font-semibold text-gray-900" x-text="getTopupType()"></p>
                    </div>
                    <div class="bg-white rounded-lg p-3">
                        <span class="text-gray-500">Statut :</span>
                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold"
                              :class="getStatusColor()">
                            <span x-text="getStatusText()"></span>
                        </span>
                    </div>
                    <div class="bg-white rounded-lg p-3">
                        <span class="text-gray-500">Méthode :</span>
                        <p class="font-semibold text-gray-900" x-text="getPaymentMethod()"></p>
                    </div>
                    <div class="bg-white rounded-lg p-3">
                        <span class="text-gray-500">Frais :</span>
                        <p class="font-semibold text-gray-900" x-text="formatAmount(topup.fees || 0)"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deliverer Information -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                Informations du compte
            </h3>

            <div class="bg-gray-50 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Nom du livreur :</span>
                        <p class="font-semibold text-gray-900" x-text="deliverer.name || 'N/A'"></p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">ID Livreur :</span>
                        <p class="font-mono text-gray-900" x-text="deliverer.deliverer_id || deliverer.id || 'N/A'"></p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Téléphone :</span>
                        <p class="font-semibold text-gray-900" x-text="deliverer.phone || 'N/A'"></p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Email :</span>
                        <p class="text-gray-900" x-text="deliverer.email || 'N/A'"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Balance Information -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                Solde du portefeuille
            </h3>

            <div class="bg-blue-50 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center">
                        <span class="text-sm text-gray-500">Solde précédent</span>
                        <p class="text-lg font-semibold text-gray-900" x-text="formatAmount(calculatePreviousBalance())"></p>
                    </div>
                    <div class="text-center">
                        <span class="text-sm text-gray-500">Montant rechargé</span>
                        <p class="text-lg font-semibold text-green-600" x-text="'+ ' + formatAmount(topup.amount)"></p>
                    </div>
                    <div class="text-center">
                        <span class="text-sm text-gray-500">Nouveau solde</span>
                        <p class="text-xl font-bold text-blue-600" x-text="formatAmount(topup.new_balance || calculateNewBalance())"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Details -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                Détails de la transaction
            </h3>

            <div class="space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">ID Transaction :</span>
                        <p class="font-mono font-semibold text-gray-900" x-text="topup.transaction_id || generateTransactionId()"></p>
                    </div>
                    <div>
                        <span class="text-gray-500">Date/Heure :</span>
                        <p class="text-gray-900" x-text="formatDateTime(topup.created_at)"></p>
                    </div>
                    <div>
                        <span class="text-gray-500">Source :</span>
                        <p class="text-gray-900" x-text="getTopupSource()"></p>
                    </div>
                    <div>
                        <span class="text-gray-500">Canal :</span>
                        <p class="text-gray-900" x-text="topup.channel || 'Application mobile'"></p>
                    </div>
                </div>

                <!-- Payment Method Details -->
                <div x-show="topup.payment_method_details" class="bg-white border rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-3">Détails du mode de paiement</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm" x-show="topup.payment_method === 'card'">
                        <div>
                            <span class="text-gray-500">Numéro de carte :</span>
                            <p class="font-mono text-gray-900" x-text="topup.card_last_four ? '**** **** **** ' + topup.card_last_four : 'N/A'"></p>
                        </div>
                        <div>
                            <span class="text-gray-500">Type de carte :</span>
                            <p class="text-gray-900" x-text="topup.card_type || 'N/A'"></p>
                        </div>
                    </div>
                    <div class="text-sm" x-show="topup.payment_method === 'bank_transfer'">
                        <span class="text-gray-500">Référence bancaire :</span>
                        <p class="font-mono text-gray-900" x-text="topup.bank_reference || 'N/A'"></p>
                    </div>
                    <div class="text-sm" x-show="topup.payment_method === 'mobile'">
                        <span class="text-gray-500">Numéro de wallet :</span>
                        <p class="font-mono text-gray-900" x-text="topup.mobile_wallet_number || 'N/A'"></p>
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
                Scannez ce code pour vérifier l'authenticité de ce reçu de recharge
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
                Ce reçu constitue une preuve officielle de recharge de portefeuille.
                Conservez-le pour vos records financiers.
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
            <button @click="sendByEmail"
                    class="bg-orange-600 text-white px-4 py-3 rounded-lg hover:bg-orange-700 transition-colors flex items-center justify-center space-x-2">
                <i class="fas fa-envelope"></i>
                <span>Envoyer par email</span>
            </button>

            <button @click="viewWallet"
                    class="bg-yellow-600 text-white px-4 py-3 rounded-lg hover:bg-yellow-700 transition-colors flex items-center justify-center space-x-2">
                <i class="fas fa-wallet"></i>
                <span>Voir le portefeuille</span>
            </button>
        </div>

        <!-- Make Another Topup -->
        <div class="text-center pt-4">
            <button @click="makeAnotherTopup"
                    class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-3 rounded-lg hover:from-purple-700 hover:to-blue-700 transition-colors flex items-center justify-center space-x-2 mx-auto">
                <i class="fas fa-plus-circle"></i>
                <span>Effectuer une nouvelle recharge</span>
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
    Alpine.data('topupReceipt', (data) => ({
        topup: data.topup || {},
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
            return `TOP-${date.getFullYear()}${String(date.getMonth() + 1).padStart(2, '0')}${String(date.getDate()).padStart(2, '0')}-${timestamp}`;
        },

        generateTransactionId() {
            return `TXN-TOP-${Date.now().toString().slice(-10)}`;
        },

        getTopupType() {
            const type = this.topup.type || 'manual';
            const types = {
                manual: 'Recharge manuelle',
                automatic: 'Recharge automatique',
                bonus: 'Bonus/Promotion',
                commission: 'Commission livreur',
                refund: 'Remboursement',
                adjustment: 'Ajustement'
            };
            return types[type] || 'Recharge manuelle';
        },

        getPaymentMethod() {
            const method = this.topup.payment_method || 'cash';
            const methods = {
                cash: 'Espèces',
                card: 'Carte bancaire',
                bank_transfer: 'Virement bancaire',
                mobile: 'Paiement mobile',
                ccp: 'CCP',
                check: 'Chèque'
            };
            return methods[method] || 'Espèces';
        },

        getTopupSource() {
            const source = this.topup.source || 'self';
            const sources = {
                self: 'Livreur',
                admin: 'Administration',
                supervisor: 'Superviseur',
                system: 'Système automatique',
                third_party: 'Tiers'
            };
            return sources[source] || 'Livreur';
        },

        getStatusText() {
            const status = this.topup.status || 'completed';
            const statuses = {
                pending: 'En attente',
                processing: 'En cours',
                completed: 'Complété',
                failed: 'Échoué',
                cancelled: 'Annulé'
            };
            return statuses[status] || 'Complété';
        },

        getStatusColor() {
            const status = this.topup.status || 'completed';
            const colors = {
                pending: 'bg-yellow-100 text-yellow-800',
                processing: 'bg-blue-100 text-blue-800',
                completed: 'bg-green-100 text-green-800',
                failed: 'bg-red-100 text-red-800',
                cancelled: 'bg-gray-100 text-gray-800'
            };
            return colors[status] || 'bg-green-100 text-green-800';
        },

        calculatePreviousBalance() {
            return (this.topup.new_balance || 0) - (this.topup.amount || 0);
        },

        calculateNewBalance() {
            return this.calculatePreviousBalance() + (this.topup.amount || 0);
        },

        generateQRCode() {
            const qrData = {
                type: 'topup_receipt',
                topup_id: this.topup.id,
                reference: this.topup.reference || this.generateReference(),
                amount: this.topup.amount,
                deliverer_id: this.deliverer.id,
                created_at: this.topup.created_at,
                verification_url: `${window.location.origin}/verify-topup/${this.topup.id || 'unknown'}`
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

                const filename = `Recu-Recharge-${this.topup.reference || 'Topup'}-${this.formatDate()}.pdf`;
                pdf.save(filename);

            } catch (error) {
                console.error('Erreur génération PDF:', error);
                alert('Erreur lors de la génération du PDF');
            }
        },

        shareReceipt() {
            if (navigator.share) {
                navigator.share({
                    title: 'Reçu de recharge Al-Amena',
                    text: `Reçu de recharge portefeuille - Montant: ${this.formatAmount(this.topup.amount)}`,
                    url: window.location.href
                }).catch(console.error);
            } else {
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Lien copié dans le presse-papiers');
                });
            }
        },

        sendByEmail() {
            const subject = `Reçu de recharge portefeuille - ${this.topup.reference}`;
            const body = `Bonjour,

Votre recharge de portefeuille a été effectuée avec succès.

Détails:
- Référence: ${this.topup.reference || this.generateReference()}
- Montant rechargé: ${this.formatAmount(this.topup.amount)}
- Nouveau solde: ${this.formatAmount(this.topup.new_balance || this.calculateNewBalance())}
- Date: ${this.formatDateTime(this.topup.created_at)}
- Méthode: ${this.getPaymentMethod()}

Vous pouvez consulter le reçu détaillé sur: ${window.location.href}

Cordialement,
L'équipe Al-Amena Delivery`;

            const emailUrl = `mailto:${this.deliverer.email || ''}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
            window.location.href = emailUrl;
        },

        viewWallet() {
            window.location.href = '/deliverer/wallet';
        },

        makeAnotherTopup() {
            window.location.href = '/deliverer/wallet/topup';
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