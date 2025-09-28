<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de Livraison Paiement - {{ $withdrawalRequest->request_code }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .receipt {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            border: 2px solid #333;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }
        .receipt-type {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            padding: 8px;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            color: #92400e;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            color: #374151;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 3px;
            margin-bottom: 8px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 13px;
        }
        .info-label {
            color: #6b7280;
        }
        .info-value {
            font-weight: bold;
            color: #111827;
        }
        .amount-box {
            background: linear-gradient(135deg, #065f46, #047857);
            color: white;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
            border-radius: 8px;
        }
        .amount-label {
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        .amount-value {
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .barcode-section {
            text-align: center;
            padding: 15px;
            background: #f9fafb;
            border: 2px dashed #d1d5db;
            margin: 15px 0;
        }
        .barcode {
            font-family: 'Courier New', monospace;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 3px;
            margin: 10px 0;
            padding: 10px;
            background: white;
            border: 1px solid #333;
        }
        .instructions {
            background: #fef2f2;
            border: 2px solid #fca5a5;
            padding: 15px;
            margin: 15px 0;
        }
        .instructions-title {
            font-weight: bold;
            color: #991b1b;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .instructions ul {
            margin: 0;
            padding-left: 15px;
        }
        .instructions li {
            font-size: 12px;
            color: #7f1d1d;
            margin-bottom: 3px;
        }
        .signature-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #d1d5db;
        }
        .signature-box {
            border: 1px solid #333;
            height: 60px;
            margin-top: 5px;
            position: relative;
        }
        .signature-label {
            position: absolute;
            top: -8px;
            left: 10px;
            background: white;
            padding: 0 5px;
            font-size: 11px;
            color: #666;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }
        .no-print {
            display: block;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .receipt {
                max-width: none;
                box-shadow: none;
                border: 1px solid #000;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="logo">🚛 AL-AMENA DELIVERY</div>
            <div class="subtitle">Service de Livraison Express</div>
            <div class="receipt-type">💰 BON DE LIVRAISON PAIEMENT</div>
        </div>

        <!-- Payment Details -->
        <div class="section">
            <div class="section-title">Détails du Paiement</div>
            <div class="info-row">
                <span class="info-label">Code demande:</span>
                <span class="info-value">{{ $withdrawalRequest->request_code }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date demande:</span>
                <span class="info-value">{{ $withdrawalRequest->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Méthode:</span>
                <span class="info-value">{{ $withdrawalRequest->method_display }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Statut:</span>
                <span class="info-value">{{ $withdrawalRequest->status_display }}</span>
            </div>
        </div>

        <!-- Amount to Deliver -->
        <div class="amount-box">
            <div class="amount-label">MONTANT À REMETTRE</div>
            <div class="amount-value">{{ number_format($withdrawalRequest->amount, 3) }} DT</div>
        </div>

        <!-- Client Information -->
        <div class="section">
            <div class="section-title">Client Bénéficiaire</div>
            <div class="info-row">
                <span class="info-label">Nom:</span>
                <span class="info-value">{{ $withdrawalRequest->client->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Téléphone:</span>
                <span class="info-value">{{ $withdrawalRequest->client->phone }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ $withdrawalRequest->client->email }}</span>
            </div>
        </div>

        <!-- Delivery Code -->
        <div class="barcode-section">
            <div style="font-size: 12px; color: #666; margin-bottom: 5px;">CODE DE LIVRAISON</div>
            <div class="barcode">{{ $withdrawalRequest->delivery_receipt_code }}</div>
            <div style="font-size: 10px; color: #666; margin-top: 5px;">Scanner ce code lors de la remise</div>
        </div>

        <!-- Processing Info -->
        <div class="section">
            <div class="section-title">Traitement</div>
            <div class="info-row">
                <span class="info-label">Traité par:</span>
                <span class="info-value">{{ $withdrawalRequest->processedByCommercial->name ?? 'Commercial' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date traitement:</span>
                <span class="info-value">{{ $withdrawalRequest->processed_at ? $withdrawalRequest->processed_at->format('d/m/Y H:i') : 'N/A' }}</span>
            </div>
            @if($withdrawalRequest->processing_notes)
            <div style="margin-top: 10px; padding: 8px; background: #f0f9ff; border: 1px solid #0284c7; font-size: 11px;">
                <strong>Notes:</strong> {{ $withdrawalRequest->processing_notes }}
            </div>
            @endif
        </div>

        <!-- Security Instructions -->
        <div class="instructions">
            <div class="instructions-title">⚠️ PROCÉDURE DE LIVRAISON</div>
            <ul>
                <li>Vérifier l'identité du client (CIN obligatoire)</li>
                <li>Remettre exactement {{ number_format($withdrawalRequest->amount, 3) }} DT en espèces</li>
                <li>Demander la signature du client ci-dessous</li>
                <li>Scanner le code {{ $withdrawalRequest->delivery_receipt_code }}</li>
                <li>Prendre une photo de la remise comme preuve</li>
            </ul>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; font-size: 11px;">
                <div>
                    <div style="margin-bottom: 5px;">LIVREUR:</div>
                    <div class="signature-box">
                        <div class="signature-label">Signature & Date</div>
                    </div>
                    <div style="text-align: center; margin-top: 5px; font-weight: bold;">
                        {{ Auth::user()->name }}
                    </div>
                </div>
                <div>
                    <div style="margin-bottom: 5px;">CLIENT BÉNÉFICIAIRE:</div>
                    <div class="signature-box">
                        <div class="signature-label">Signature & Date</div>
                    </div>
                    <div style="text-align: center; margin-top: 5px; font-weight: bold;">
                        {{ $withdrawalRequest->client->name }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Important Notice -->
        <div style="background: #fffbeb; border: 1px solid #f59e0b; padding: 10px; margin-top: 15px; font-size: 10px; text-align: center;">
            <strong style="color: #92400e;">ATTENTION:</strong>
            <div style="color: #78350f; margin-top: 3px;">
                Ce bon doit être signé par les deux parties et conservé comme preuve de livraison.
                Le code {{ $withdrawalRequest->delivery_receipt_code }} confirme la réception.
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>Al-Amena Delivery - Service de Livraison Express</div>
            <div>Généré le {{ now()->format('d/m/Y à H:i') }}</div>
            <div style="margin-top: 5px;">
                📞 Support: +216 XX XXX XXX | 📧 support@al-amena.tn
            </div>
        </div>

        <!-- Print Button -->
        <div style="text-align: center; margin-top: 20px;" class="no-print">
            <button onclick="window.print()" style="background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 5px; font-size: 14px; cursor: pointer;">
                🖨️ Imprimer ce Bon
            </button>
            <button onclick="window.close()" style="background: #6b7280; color: white; padding: 10px 20px; border: none; border-radius: 5px; font-size: 14px; cursor: pointer; margin-left: 10px;">
                Fermer
            </button>
        </div>
    </div>

    <script>
        // Auto-print when opened with print parameter
        if (window.location.search.includes('print=1')) {
            window.onload = function() {
                setTimeout(() => {
                    window.print();
                }, 100);
            }
        }
    </script>
</body>
</html>