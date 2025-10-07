<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de Livraison - {{ $package->tracking_number }}</title>
    <style>
        @media print {
            body { margin: 0; padding: 20px; }
            .no-print { display: none; }
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            max-width: 800px;
            margin: 0 auto;
        }
        .receipt-header {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .receipt-section {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .receipt-section h3 {
            margin-top: 0;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dotted #ddd;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .cod-amount {
            background: #ffe0b2;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
            margin: 20px 0;
        }
        .cod-amount h2 {
            margin: 0;
            color: #e65100;
        }
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-around;
        }
        .signature-box {
            text-align: center;
            min-width: 200px;
        }
        .signature-line {
            border-top: 2px solid #000;
            margin-top: 60px;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; margin: 5px;">🖨️ Imprimer</button>
        <button onclick="window.close()" style="padding: 10px 20px; margin: 5px;">✖ Fermer</button>
    </div>

    <div class="receipt-header">
        <h1>📦 REÇU DE LIVRAISON</h1>
        <p><strong>Al-Amena Delivery</strong></p>
        <p>Date: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Informations Colis -->
    <div class="receipt-section">
        <h3>📦 Informations du Colis</h3>
        <div class="info-row">
            <span class="info-label">Code de suivi:</span>
            <span><strong>{{ $package->tracking_number ?? $package->package_code }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Statut:</span>
            <span><strong>{{ $package->status }}</strong></span>
        </div>
        @if($package->delivered_at)
            <div class="info-row">
                <span class="info-label">Date de livraison:</span>
                <span>{{ $package->delivered_at->format('d/m/Y H:i') }}</span>
            </div>
        @endif
    </div>

    <!-- Destinataire -->
    <div class="receipt-section">
        <h3>👤 Destinataire</h3>
        <div class="info-row">
            <span class="info-label">Nom:</span>
            <span>{{ $package->recipient_name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Téléphone:</span>
            <span>{{ $package->recipient_phone }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Adresse:</span>
            <span>{{ $package->recipient_address }}, {{ $package->recipient_city ?? '' }}</span>
        </div>
    </div>

    <!-- Montant COD -->
    @if($package->cod_amount > 0)
        <div class="cod-amount">
            <p style="margin: 0; color: #666;">Montant Collecté (COD)</p>
            <h2>{{ number_format($package->cod_amount, 3) }} DT</h2>
        </div>
    @endif

    <!-- Livreur -->
    <div class="receipt-section">
        <h3>🚚 Livreur</h3>
        <div class="info-row">
            <span class="info-label">Nom:</span>
            <span>{{ $package->assignedDeliverer->name ?? Auth::user()->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Téléphone:</span>
            <span>{{ $package->assignedDeliverer->phone ?? Auth::user()->phone }}</span>
        </div>
    </div>

    <!-- Signatures -->
    <div class="signature-section">
        <div class="signature-box">
            <p><strong>Signature du Destinataire</strong></p>
            <div class="signature-line">
                {{ $package->recipient_name }}
            </div>
        </div>
        <div class="signature-box">
            <p><strong>Signature du Livreur</strong></p>
            <div class="signature-line">
                {{ $package->assignedDeliverer->name ?? Auth::user()->name }}
            </div>
        </div>
    </div>

    <div style="text-align: center; margin-top: 40px; color: #999; font-size: 12px;">
        <p>Merci d'avoir choisi Al-Amena Delivery</p>
        <p>www.alamena-delivery.tn</p>
    </div>

    <script>
        // Auto-print on load (optionnel)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
