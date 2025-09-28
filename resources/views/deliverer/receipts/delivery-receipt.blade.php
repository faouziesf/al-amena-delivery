<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de Livraison - {{ $package->package_code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media print {
            body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .no-print { display: none !important; }
            @page { margin: 0; size: A4; }
            .receipt-container { border: none !important; box-shadow: none !important; }
        }
        .signature-box {
            border: 2px dashed #D1D5DB;
            min-height: 80px; /* Reduced height */
            background: linear-gradient(to bottom, transparent 95%, #F3F4F6 95%);
            background-size: 100% 20px;
        }
    </style>
</head>
<body class="bg-slate-100">

    <div class="no-print bg-white shadow-sm p-3 flex justify-between items-center sticky top-0 z-10">
        <a href="{{ url()->previous() }}" class="text-purple-600 font-semibold hover:bg-purple-50 px-4 py-2 rounded-lg transition-colors">← Retour</a>
        <h1 class="font-bold text-lg">Reçu de Livraison</h1>
        <button onclick="window.print()" class="bg-purple-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">Imprimer</button>
    </div>

    <div class="max-w-3xl mx-auto p-4 print:p-0">
        <div class="receipt-container bg-white rounded-2xl shadow-lg border border-purple-100 p-6 print:p-8 print:shadow-none print:border-none">

            <header class="flex justify-between items-center border-b-2 border-purple-100 pb-4 mb-4 print:pb-3 print:mb-3">
                <div>
                    <h2 class="text-2xl print:text-xl font-bold text-purple-900">AL-AMENA DELIVERY</h2>
                    <p class="text-purple-700 print:text-sm">Reçu de Livraison Officiel</p>
                </div>
                <div class="text-right">
                    <p class="font-mono text-sm print:text-xs">{{ $package->package_code }}</p>
                    <p class="text-sm print:text-xs text-gray-500">{{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </header>

            <section class="mb-4 print:mb-3 border-t border-b border-purple-100 py-3 print:py-2">
                <div class="space-y-2 text-sm print:text-xs">
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-gray-500">STATUT</span>
                        <span class="px-3 py-1 rounded-full font-semibold bg-green-100 text-green-800">LIVRÉ</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-gray-500">MONTANT COLLECTÉ</span>
                        <span class="text-base print:text-sm font-bold text-green-800">{{ number_format($package->cod_amount, 3) }} DT</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-gray-500">LIVRÉ PAR</span>
                        <span class="font-semibold text-gray-800">{{ Auth::check() ? Auth::user()->name : 'Livreur' }}</span>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-2 gap-4 mb-4 print:mb-3 print:gap-3">
                <div class="space-y-1 text-xs print:text-[10px]">
                    <h3 class="font-bold text-gray-800 mb-2 border-b border-gray-200 pb-1">EXPÉDITEUR</h3>
                    <p><strong>Nom:</strong> {{ $package->sender->name ?? 'N/A' }}</p>
                    <p><strong>Tél:</strong> {{ $package->sender->phone ?? 'N/A' }}</p>
                    <p><strong>Zone:</strong> {{ $package->delegationFrom->name ?? 'N/A' }}</p>
                </div>
                <div class="space-y-1 text-xs print:text-[10px]">
                    <h3 class="font-bold text-gray-800 mb-2 border-b border-gray-200 pb-1">DESTINATAIRE</h3>
                    <p><strong>Nom:</strong> {{ $recipientData['name'] ?? 'N/A' }}</p>
                    <p><strong>Tél:</strong> {{ $recipientData['phone'] ?? 'N/A' }}</p>
                    <p><strong>Adresse:</strong> {{ $recipientData['address'] ?? 'N/A' }}</p>
                    <p><strong>Zone:</strong> {{ $package->delegationTo->name ?? 'N/A' }}</p>
                </div>
            </section>

            @if($package->pickup_address || $package->pickup_phone || $package->pickup_notes)
            <section class="mb-4 print:mb-3">
                <h3 class="font-bold text-gray-800 mb-2 border-b border-gray-200 pb-1 text-xs">ADRESSE DE PICKUP</h3>
                <div class="text-xs print:text-[10px] bg-blue-50 rounded-lg p-2">
                    @if($package->pickup_address)
                        <p><strong>Adresse:</strong> {{ $package->pickup_address }}</p>
                    @endif
                    @if($package->pickup_phone)
                        <p><strong>Téléphone Contact:</strong> {{ $package->pickup_phone }}</p>
                    @endif
                    @if($package->pickup_notes)
                        <p><strong>Notes Pickup:</strong> {{ $package->pickup_notes }}</p>
                    @endif
                    @if($package->pickupDelegation)
                        <p><strong>Délégation:</strong> {{ $package->pickupDelegation->name }}</p>
                    @endif
                </div>
            </section>
            @endif

            <section class="mb-4 print:mb-3">
                <h3 class="font-bold text-gray-800 mb-2 border-b border-gray-200 pb-1 text-xs">DÉTAILS DU COLIS</h3>
                <div class="text-xs print:text-[10px] bg-slate-50 rounded-lg p-2">
                    <p><strong>Contenu:</strong> {{ $package->content_description ?? 'Non spécifié' }}</p>

                    @if($package->est_echange)
                        <p class="mt-1"><strong>Type:</strong> <span class="text-orange-600 font-semibold">COLIS D'ÉCHANGE</span></p>
                    @endif

                    @if($package->package_weight)
                        <p class="mt-1"><strong>Poids:</strong> {{ number_format($package->package_weight, 3) }} kg</p>
                    @endif

                    @if($package->package_value)
                        <p class="mt-1"><strong>Valeur déclarée:</strong> {{ number_format($package->package_value, 3) }} DT</p>
                    @endif

                    @if($package->package_dimensions)
                        @php $dims = json_decode($package->package_dimensions, true); @endphp
                        @if($dims && isset($dims['length'], $dims['width'], $dims['height']))
                            <p class="mt-1"><strong>Dimensions:</strong> {{ $dims['length'] }}×{{ $dims['width'] }}×{{ $dims['height'] }} cm</p>
                        @endif
                    @endif

                    @if($package->payment_method)
                        <p class="mt-1"><strong>Mode de paiement:</strong>
                            @switch($package->payment_method)
                                @case('cash_only') Espèces uniquement @break
                                @case('check_only') Chèque uniquement @break
                                @case('cash_and_check') Espèces et chèques @break
                                @default {{ $package->payment_method }} @break
                            @endswitch
                        </p>
                    @endif

                    @if($package->delivery_notes || $package->notes)
                        <p class="mt-1"><strong>Notes:</strong> {{ $package->delivery_notes ?? $package->notes }}</p>
                    @endif
                </div>
            </section>

            <!-- Options spéciales -->
            @if($package->is_fragile || $package->requires_signature || $package->allow_opening)
            <section class="mb-4 print:mb-3">
                <h3 class="font-bold text-gray-800 mb-2 border-b border-gray-200 pb-1 text-xs">OPTIONS SPÉCIALES</h3>
                <div class="text-xs print:text-[10px] bg-yellow-50 rounded-lg p-2">
                    @if($package->is_fragile)
                        <p class="flex items-center mb-1">
                            <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                            <strong>FRAGILE</strong> - Manipuler avec précaution
                        </p>
                    @endif
                    @if($package->requires_signature)
                        <p class="flex items-center mb-1">
                            <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                            <strong>SIGNATURE OBLIGATOIRE</strong> - Ne pas laisser sans signature
                        </p>
                    @endif
                    @if($package->allow_opening)
                        <p class="flex items-center">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            <strong>OUVERTURE AUTORISÉE</strong> - Le destinataire peut vérifier le contenu
                        </p>
                    @endif
                </div>
            </section>
            @endif

            <!-- Informations financières -->
            <section class="mb-4 print:mb-3">
                <h3 class="font-bold text-gray-800 mb-2 border-b border-gray-200 pb-1 text-xs">INFORMATIONS FINANCIÈRES</h3>
                <div class="text-xs print:text-[10px] bg-green-50 rounded-lg p-2">
                    <div class="grid grid-cols-2 gap-2">
                        <p><strong>Montant COD:</strong> {{ number_format($package->cod_amount, 3) }} DT</p>
                        <p><strong>Frais livraison:</strong> {{ number_format($package->delivery_fee ?? 0, 3) }} DT</p>
                        @if($package->return_fee)
                            <p><strong>Frais retour:</strong> {{ number_format($package->return_fee, 3) }} DT</p>
                        @endif
                        @if($package->delivered_at)
                            <p><strong>Livré le:</strong> {{ $package->delivered_at->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-2 gap-4 mb-4 print:mb-3 print:gap-3">
                <div>
                    <h3 class="font-bold text-gray-800 mb-2 text-xs">SIGNATURE DESTINATAIRE</h3>
                    <p class="text-[10px] text-gray-600 mb-2">Je certifie avoir reçu le colis en bon état.</p>
                    <div class="signature-box rounded-md"></div>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 mb-2 text-xs">SIGNATURE LIVREUR</h3>
                    <p class="text-[10px] text-gray-600 mb-2">Je certifie avoir remis le colis.</p>
                    <div class="signature-box rounded-md"></div>
                </div>
            </section>

            <footer class="border-t-2 border-purple-100 pt-3 text-center">
                <p class="text-[10px] text-gray-500">Merci d'avoir choisi Al-Amena Delivery.</p>
                <p class="text-[10px] text-gray-500">Reçu N°: REC-{{ str_pad($package->id, 6, '0', STR_PAD_LEFT) }} | Généré le: {{ now()->format('d/m/Y à H:i') }}</p>
            </footer>
        </div>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('print') === 'true') {
            setTimeout(() => window.print(), 500);
        }
    </script>
</body>
</html>