@extends('layouts.deliverer')

@section('title', 'Reçu de livraison')

@section('content')
<div x-data="packageReceipt({
    package: {{ json_encode($package ?? []) }},
    transaction: {{ json_encode($transaction ?? []) }},
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

            <div class="bg-blue-50 rounded-lg p-4">
                <h2 class="text-xl font-semibold text-blue-900 mb-2">Reçu de livraison</h2>
                <p class="text-sm text-blue-700">
                    Date : <span x-text="formatDate(transaction.created_at || new Date())"></span>
                </p>
                <p class="text-sm text-blue-700">
                    Référence : <span x-text="transaction.reference || generateReference()"></span>
                </p>
            </div>
        </div>

        <!-- Package Information -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                Informations du colis
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Code colis :</span>
                        <p class="font-mono font-semibold text-gray-900" x-text="package.tracking_number || 'N/A'"></p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Expéditeur :</span>
                        <p class="text-gray-900" x-text="package.sender_name || 'N/A'"></p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Ville d'origine :</span>
                        <p class="text-gray-900" x-text="package.sender_city || 'N/A'"></p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Destinataire :</span>
                        <p class="text-gray-900" x-text="package.recipient_name || 'N/A'"></p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Téléphone :</span>
                        <p class="text-gray-900" x-text="package.recipient_phone || 'N/A'"></p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Adresse :</span>
                        <p class="text-gray-900 text-sm" x-text="formatAddress()"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery Information -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                Détails de la livraison
            </h3>

            <div class="bg-green-50 rounded-lg p-4">
                <div class="flex items-center space-x-2 mb-3">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <span class="font-semibold text-green-900">Livraison effectuée avec succès</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Date de livraison :</span>
                        <p class="font-semibold text-gray-900" x-text="formatDate(package.delivered_at)"></p>
                    </div>
                    <div>
                        <span class="text-gray-600">Heure de livraison :</span>
                        <p class="font-semibold text-gray-900" x-text="formatTime(package.delivered_at)"></p>
                    </div>
                    <div>
                        <span class="text-gray-600">Livreur :</span>
                        <p class="font-semibold text-gray-900" x-text="deliverer.name || 'N/A'"></p>
                    </div>
                    <div>
                        <span class="text-gray-600">Téléphone livreur :</span>
                        <p class="font-semibold text-gray-900" x-text="deliverer.phone || 'N/A'"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Information (if COD) -->
        <div x-show="package.is_cod && transaction.amount" class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                Détails du paiement
            </h3>

            <div class="bg-yellow-50 rounded-lg p-4">
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Montant du colis :</span>
                        <span class="font-semibold text-gray-900" x-text="formatAmount(package.cod_amount)"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Frais de livraison :</span>
                        <span class="font-semibold text-gray-900" x-text="formatAmount(package.delivery_fee)"></span>
                    </div>
                    <div class="border-t border-yellow-200 pt-2">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-gray-900">Total payé :</span>
                            <span class="text-xl font-bold text-green-600" x-text="formatAmount(transaction.amount)"></span>
                        </div>
                    </div>
                    <div class="text-sm text-gray-600">
                        <span>Mode de paiement : </span>
                        <span class="font-semibold" x-text="getPaymentMethod()"></span>
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
                Scannez ce code pour vérifier l'authenticité de ce reçu
            </p>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 pt-6 text-center text-sm text-gray-600 space-y-2">
            <p>
                <strong>Al-Amena Delivery</strong> - Service de livraison de confiance
            </p>
            <p>
                Email: support@alamena.dz | Tel: +213 123 456 789
            </p>
            <p>
                Adresse: 123 Rue de la Livraison, Alger, Algérie
            </p>
            <p class="text-xs">
                Ce reçu constitue une preuve officielle de livraison.
                Conservez-le pour vos records.
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

            <button @click="sendBySMS"
                    class="bg-yellow-600 text-white px-4 py-3 rounded-lg hover:bg-yellow-700 transition-colors flex items-center justify-center space-x-2">
                <i class="fas fa-sms"></i>
                <span>Envoyer par SMS</span>
            </button>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-6 text-center">
        <a href="{{ route('deliverer.packages.index') }}"
           class="inline-flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors">
            <i class="fas fa-arrow-left"></i>
            <span>Retour à la liste des colis</span>
        </a>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.3/qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('packageReceipt', (data) => ({
        package: data.package || {},
        transaction: data.transaction || {},
        deliverer: data.deliverer || {},

        init() {
            this.generateQRCode();
        },

        formatDate(dateString) {
            if (!dateString) return new Date().toLocaleDateString('fr-DZ');
            return new Date(dateString).toLocaleDateString('fr-DZ');
        },

        formatTime(dateString) {
            if (!dateString) return new Date().toLocaleTimeString('fr-DZ');
            return new Date(dateString).toLocaleTimeString('fr-DZ');
        },

        formatAmount(amount) {
            if (!amount) return '0 DA';
            return new Intl.NumberFormat('fr-DZ', {
                style: 'currency',
                currency: 'DZD',
                minimumFractionDigits: 0
            }).format(amount).replace('DZD', 'DA');
        },

        formatAddress() {
            const address = this.package.recipient_address || '';
            const city = this.package.recipient_city || '';
            const postalCode = this.package.recipient_postal_code || '';

            return [address, city, postalCode].filter(Boolean).join(', ') || 'N/A';
        },

        generateReference() {
            const date = new Date();
            const timestamp = date.getTime().toString().slice(-6);
            return `RCT-${date.getFullYear()}${String(date.getMonth() + 1).padStart(2, '0')}${String(date.getDate()).padStart(2, '0')}-${timestamp}`;
        },

        getPaymentMethod() {
            const method = this.transaction.payment_method || 'cash';
            const methods = {
                cash: 'Espèces',
                card: 'Carte bancaire',
                mobile: 'Paiement mobile',
                check: 'Chèque'
            };
            return methods[method] || 'Espèces';
        },

        generateQRCode() {
            const qrData = {
                type: 'package_receipt',
                package_id: this.package.id,
                tracking_number: this.package.tracking_number,
                delivered_at: this.package.delivered_at,
                deliverer_id: this.deliverer.id,
                reference: this.transaction.reference || this.generateReference(),
                verification_url: `${window.location.origin}/verify-receipt/${this.package.tracking_number}`
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

                const filename = `Recu-${this.package.tracking_number || 'Package'}-${this.formatDate()}.pdf`;
                pdf.save(filename);

            } catch (error) {
                console.error('Erreur génération PDF:', error);
                alert('Erreur lors de la génération du PDF');
            }
        },

        shareReceipt() {
            if (navigator.share) {
                navigator.share({
                    title: 'Reçu de livraison Al-Amena',
                    text: `Reçu de livraison pour le colis ${this.package.tracking_number}`,
                    url: window.location.href
                }).catch(console.error);
            } else {
                // Fallback: copy URL to clipboard
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Lien copié dans le presse-papiers');
                });
            }
        },

        sendByEmail() {
            const subject = `Reçu de livraison - Colis ${this.package.tracking_number}`;
            const body = `Bonjour,

Veuillez trouver ci-joint le reçu de livraison pour votre colis.

Détails:
- Code colis: ${this.package.tracking_number}
- Date de livraison: ${this.formatDate(this.package.delivered_at)}
- Destinataire: ${this.package.recipient_name}

Vous pouvez consulter le reçu détaillé sur: ${window.location.href}

Cordialement,
L'équipe Al-Amena Delivery`;

            const emailUrl = `mailto:${this.package.recipient_email || ''}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
            window.location.href = emailUrl;
        },

        sendBySMS() {
            const message = `Al-Amena Delivery: Votre colis ${this.package.tracking_number} a été livré le ${this.formatDate(this.package.delivered_at)}. Reçu: ${window.location.href}`;
            const smsUrl = `sms:${this.package.recipient_phone || ''}?body=${encodeURIComponent(message)}`;
            window.location.href = smsUrl;
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