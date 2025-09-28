@extends('layouts.deliverer')

@section('title', 'Reçu de paiement')

@section('content')
<div x-data="paymentReceipt({
    payment: {{ json_encode($payment ?? []) }},
    packages: {{ json_encode($packages ?? []) }},
    deliverer: {{ json_encode(auth()->user() ?? []) }}
})" class="max-w-3xl mx-auto p-4 sm:p-6 lg:p-8">

    <!-- Receipt Container -->
    <div id="receipt-content" class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6 sm:p-8 space-y-8">

        <!-- Header -->
        <div class="text-center border-b border-purple-100 pb-6">
            <h1 class="text-2xl font-bold text-purple-900">Reçu de Paiement</h1>
            <p class="text-sm text-gray-500">Date: <span x-text="formatDate(payment.created_at || new Date())"></span></p>
            <p class="text-sm text-gray-500">Référence: <span class="font-mono" x-text="payment.reference || generateReference()"></span></p>
        </div>

        <!-- Payment Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
            <div>
                <p class="text-sm text-gray-500">Montant Total Payé</p>
                <p class="text-3xl font-bold text-purple-800" x-text="formatAmount(payment.total_amount)"></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Nombre de Colis</p>
                <p class="text-3xl font-bold text-purple-800" x-text="packages.length"></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Statut</p>
                <p class="inline-flex px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800 mt-2">Payé</p>
            </div>
        </div>

        <!-- Deliverer Information -->
        <div class="border-t border-purple-100 pt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informations du Livreur</h3>
            <div class="bg-slate-50 rounded-xl p-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div><strong class="font-medium text-gray-500">Nom:</strong> <span x-text="deliverer.name || 'N/A'"></span></div>
                <div><strong class="font-medium text-gray-500">Téléphone:</strong> <span x-text="deliverer.phone || 'N/A'"></span></div>
                <div><strong class="font-medium text-gray-500">ID Livreur:</strong> <span class="font-mono" x-text="deliverer.deliverer_id || deliverer.id || 'N/A'"></span></div>
                <div><strong class="font-medium text-gray-500">Zone:</strong> <span x-text="deliverer.zone || 'N/A'"></span></div>
            </div>
        </div>

        <!-- Package Details -->
        <div>
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Détails des Colis Payés</h3>
            <div class="space-y-3">
                <template x-for="(pkg, index) in packages" :key="index">
                    <div class="bg-white border border-purple-100 rounded-xl p-4">
                        <div class="grid grid-cols-6 gap-4 text-sm items-center">
                            <div class="col-span-6 sm:col-span-3">
                                <p class="text-gray-500">Code Colis</p>
                                <p class="font-mono font-semibold text-gray-800 truncate" x-text="pkg.tracking_number"></p>
                            </div>
                            <div class="col-span-4 sm:col-span-2">
                                <p class="text-gray-500">Destinataire</p>
                                <p class="font-semibold text-gray-800 truncate" x-text="pkg.recipient_name"></p>
                            </div>
                            <div class="col-span-2 sm:col-span-1 text-right">
                                <p class="text-gray-500">Montant COD</p>
                                <p class="font-semibold text-green-700" x-text="formatAmount(pkg.cod_amount)"></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- QR Code & Footer -->
        <div class="border-t border-purple-100 pt-6 flex flex-col sm:flex-row items-center gap-6">
            <div class="flex-shrink-0">
                 <div id="qr-code" class="bg-white p-2 border rounded-lg"></div>
            </div>
            <div class="text-center sm:text-left">
                <h3 class="font-semibold text-gray-800">Vérification de Reçu</h3>
                <p class="text-xs text-gray-500 mt-1">Scannez ce code pour vérifier l'authenticité de ce reçu de paiement. Ce reçu constitue une preuve officielle de paiement COD.</p>
                <p class="text-xs text-gray-500 mt-2"><strong>Al-Amena Delivery</strong> - finance@alamena.dz</p>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-8">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <button @click="printReceipt" class="action-button-primary flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimer
            </button>
            <button @click="downloadPDF" class="action-button-secondary flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Télécharger PDF
            </button>
            <button @click="shareReceipt" class="action-button-secondary flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12s-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/></svg>
                Partager
            </button>
        </div>
    </div>

    <div class="mt-6 text-center">
        <a href="{{ route('deliverer.wallet.index') }}" class="text-sm font-medium text-purple-600 hover:text-purple-800">← Retour au portefeuille</a>
    </div>
</div>

@push('styles')
<style>
    .action-button-primary { @apply w-full text-center bg-purple-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-purple-700 transition-colors disabled:opacity-50; }
    .action-button-secondary { @apply w-full text-center bg-purple-100 text-purple-700 py-3 px-4 rounded-xl font-semibold hover:bg-purple-200 transition-colors; }
    @media print { 
        body * { visibility: hidden; } 
        #receipt-content, #receipt-content * { visibility: visible; } 
        #receipt-content { position: absolute; left: 0; top: 0; width: 100%; margin: 0; padding: 0; border: none; box-shadow: none; } 
        .no-print { display: none !important; } 
    }
</style>
@endpush

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
        init() { this.generateQRCode(); },
        formatDate(d) { return d ? new Date(d).toLocaleDateString('fr-DZ') : new Date().toLocaleDateString('fr-DZ'); },
        formatAmount(a) { return new Intl.NumberFormat('fr-DZ', { style: 'currency', currency: 'DZD', minimumFractionDigits: 2 }).format(a || 0); },
        generateReference() { return `PAY-${Date.now().toString().slice(-8)}`; },
        generateQRCode() {
            const qrData = JSON.stringify({ ref: this.payment.reference, amount: this.payment.total_amount, date: this.payment.created_at });
            QRCode.toCanvas(document.getElementById('qr-code'), qrData, { width: 128, margin: 1 }, err => {
                if (err) console.error(err);
            });
        },
        printReceipt() { window.print(); },
        async downloadPDF() {
            const { jsPDF } = window.jspdf;
            const canvas = await html2canvas(document.getElementById('receipt-content'), { scale: 2 });
            const pdf = new jsPDF();
            pdf.addImage(canvas.toDataURL('image/png'), 'PNG', 0, 0, 210, canvas.height * 210 / canvas.width);
            pdf.save(`Recu-${this.payment.reference}.pdf`);
        },
        shareReceipt() {
            if (navigator.share) {
                navigator.share({ title: 'Reçu de paiement', text: `Reçu pour ${this.formatAmount(this.payment.total_amount)}`, url: window.location.href });
            } else {
                navigator.clipboard.writeText(window.location.href).then(() => alert('Lien copié!'));
            }
        }
    }));
});
</script>
@endpush
@endsection
