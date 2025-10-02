<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de Livraison - {{ $package->package_code }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
            color: #333;
            font-size: 11px;
            line-height: 1.3;
        }
        .receipt {
            max-width: 100%;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 12px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #d97706;
            margin-bottom: 3px;
        }
        .receipt-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 8px;
        }
        .package-code {
            font-size: 18px;
            font-weight: bold;
            color: #059669;
            border: 2px solid #059669;
            padding: 6px 12px;
            display: inline-block;
            margin-top: 8px;
        }

        /* Layout en deux colonnes pour optimiser l'espace */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .section {
            border: 1px solid #ccc;
            padding: 10px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            background-color: #f3f4f6;
            padding: 5px;
            margin: -10px -10px 10px -10px;
            border-bottom: 1px solid #ccc;
        }
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            width: 90px;
            flex-shrink: 0;
            font-size: 10px;
        }
        .info-value {
            flex: 1;
            border-bottom: 1px dotted #ccc;
            min-height: 16px;
            font-size: 11px;
        }

        /* Section COD mise en évidence */
        .cod-section {
            background-color: #fef3c7;
            border: 2px solid #f59e0b;
            border-radius: 5px;
            padding: 10px;
            margin: 15px 0;
            text-align: center;
            grid-column: 1 / -1;
        }
        .cod-amount {
            font-size: 20px;
            font-weight: bold;
            color: #92400e;
        }

        /* Instructions compactes */
        .instructions {
            background-color: #f0f9ff;
            border: 1px solid #0284c7;
            border-radius: 5px;
            padding: 8px;
            margin: 10px 0;
            font-size: 10px;
        }
        .instructions-title {
            font-weight: bold;
            color: #0284c7;
            margin-bottom: 5px;
            font-size: 11px;
        }

        /* Signatures compactes */
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
            border-top: 1px solid #ccc;
            padding-top: 15px;
        }
        .signature-box {
            text-align: center;
            font-size: 10px;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            height: 40px;
            margin-bottom: 5px;
        }

        @media print {
            body { margin: 0; padding: 5mm; }
            .receipt { border: 2px solid #000; }
            .print-button { display: none; }
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #059669;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .print-button:hover {
            background-color: #047857;
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">🖨️ Imprimer</button>

    <div class="receipt">
        <!-- En-tête -->
        <div class="header">
            <div class="company-name">AL-AMENA DELIVERY</div>
            <div>Service de Livraison Express</div>
            <div class="receipt-title">BON DE LIVRAISON</div>
            <div class="package-code">{{ $package->package_code }}</div>
        </div>

        <!-- Layout en grille pour optimiser l'espace -->
        <div class="info-grid">
            <!-- Informations du colis -->
            <div class="section">
                <div class="section-title">📦 INFORMATIONS COLIS</div>
                <div class="info-row">
                    <span class="info-label">Date:</span>
                    <span class="info-value">{{ $package->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Contenu:</span>
                    <span class="info-value">{{ $package->content_description ?? 'Marchandise' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">De:</span>
                    <span class="info-value">{{ $delegationFrom }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Vers:</span>
                    <span class="info-value">{{ $delegationTo }}</span>
                </div>
            </div>

            <!-- Destinataire -->
            <div class="section">
                <div class="section-title">📥 DESTINATAIRE</div>
                <div class="info-row">
                    <span class="info-label">Nom:</span>
                    <span class="info-value">{{ $recipientData['name'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Téléphone:</span>
                    <span class="info-value">{{ $recipientData['phone'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Adresse:</span>
                    <span class="info-value">{{ $recipientData['address'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ville:</span>
                    <span class="info-value">{{ $recipientData['city'] }}</span>
                </div>
            </div>

            <!-- Montant COD -->
            @if($package->cod_amount > 0)
            <div class="cod-section">
                <div style="font-size: 14px; font-weight: bold; margin-bottom: 8px;">
                    💰 MONTANT À PERCEVOIR
                </div>
                <div class="cod-amount">{{ number_format($package->cod_amount, 3) }} DT</div>
                <div style="margin-top: 5px; font-size: 9px;">
                    ⚠️ À percevoir en espèces
                </div>
            </div>
            @endif

            <!-- Livreur -->
            @if($package->assignedDeliverer)
            <div class="section">
                <div class="section-title">🚚 LIVREUR</div>
                <div class="info-row">
                    <span class="info-label">Nom:</span>
                    <span class="info-value">{{ $package->assignedDeliverer->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Téléphone:</span>
                    <span class="info-value">{{ $package->assignedDeliverer->phone ?? 'N/A' }}</span>
                </div>
            </div>
            @endif
        </div>

        <!-- Instructions spéciales -->
        @if($package->requires_signature || $package->payment_method === 'COD' || $package->special_instructions)
        <div class="instructions">
            <div class="instructions-title">📋 INSTRUCTIONS DE LIVRAISON</div>
            <ul style="margin: 0; padding-left: 20px;">
                @if($package->requires_signature)
                <li>✍️ Signature obligatoire du destinataire</li>
                @endif
                @if($package->payment_method === 'COD' && $package->cod_amount > 0)
                <li>💰 Percevoir {{ number_format($package->cod_amount, 3) }} DT en espèces</li>
                @endif
                @if($package->special_instructions)
                <li>📝 {{ $package->special_instructions }}</li>
                @endif
                @if($package->payment_withdrawal_id)
                <li>💳 Colis de paiement - Vérifier identité du destinataire</li>
                @endif
            </ul>
        </div>
        @endif

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div><strong>Signature du Livreur</strong></div>
                <div>Date: _______________</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div><strong>Signature du Destinataire</strong></div>
                <div>Date: _______________</div>
            </div>
        </div>

        <!-- Pied de page -->
        <div style="text-align: center; margin-top: 30px; font-size: 10px; color: #666;">
            <div>Al-Amena Delivery - Service de livraison express en Tunisie</div>
            <div>Document généré le {{ now()->format('d/m/Y à H:i') }}</div>
        </div>
    </div>
</body>
</html>