<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bon de Livraison - {{ $withdrawal->delivery_receipt_code }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
            font-size: 14px;
            line-height: 1.5;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #7c3aed;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #7c3aed;
            margin-bottom: 5px;
        }
        .receipt-title {
            font-size: 24px;
            font-weight: bold;
            color: #374151;
            margin: 15px 0;
        }
        .receipt-code {
            font-size: 20px;
            color: #7c3aed;
            font-weight: bold;
            background: #f3f4f6;
            padding: 10px;
            border-radius: 5px;
            display: inline-block;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin: 30px 0;
        }
        .info-section {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            background: #f9fafb;
        }
        .info-title {
            font-size: 16px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 15px;
            border-bottom: 2px solid #7c3aed;
            padding-bottom: 5px;
        }
        .info-item {
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #4b5563;
            width: 120px;
            display: inline-block;
        }
        .info-value {
            color: #374151;
        }
        .amount-highlight {
            background: #10b981;
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 30px 0;
            font-size: 24px;
            font-weight: bold;
        }
        .instructions {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        .instructions-title {
            font-weight: bold;
            color: #92400e;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .instruction-item {
            margin: 8px 0;
            color: #92400e;
        }
        .signature-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid #e5e7eb;
        }
        .signature-box {
            text-align: center;
            border: 1px dashed #9ca3af;
            padding: 40px 20px;
            border-radius: 8px;
        }
        .signature-label {
            font-weight: bold;
            margin-bottom: 10px;
            color: #374151;
        }
        .barcode {
            text-align: center;
            margin: 30px 0;
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #374151;
            padding: 15px;
            background: #f3f4f6;
            border-radius: 8px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 12px;
        }
        .print-button {
            background: #7c3aed;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .print-button:hover {
            background: #6d28d9;
        }
        @media print {
            body { background: white; }
            .print-button { display: none; }
            .receipt-container { box-shadow: none; }
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-approved {
            background: #d1fae5;
            color: #047857;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <button class="print-button" onclick="window.print()">🖨️ Imprimer ce bon</button>

        <div class="header">
            <div class="company-name">AL AMENA DELIVERY</div>
            <div class="receipt-title">BON DE LIVRAISON ESPÈCES</div>
            <div class="receipt-code">{{ $withdrawal->delivery_receipt_code }}</div>
            <div class="status-badge status-approved">{{ $withdrawal->status_display }}</div>
        </div>

        <div class="info-grid">
            <div class="info-section">
                <div class="info-title">📋 Informations de la Demande</div>
                <div class="info-item">
                    <span class="info-label">Code demande:</span>
                    <span class="info-value">{{ $withdrawal->request_code }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Date création:</span>
                    <span class="info-value">{{ $withdrawal->created_at->format('d/m/Y à H:i') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Date approbation:</span>
                    <span class="info-value">{{ $withdrawal->processed_at ? $withdrawal->processed_at->format('d/m/Y à H:i') : 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Traité par:</span>
                    <span class="info-value">{{ $withdrawal->processedByCommercial->name ?? 'N/A' }}</span>
                </div>
            </div>

            <div class="info-section">
                <div class="info-title">👤 Informations Client</div>
                <div class="info-item">
                    <span class="info-label">Nom complet:</span>
                    <span class="info-value">{{ $withdrawal->client->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $withdrawal->client->email }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Téléphone:</span>
                    <span class="info-value">{{ $withdrawal->client->phone ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Adresse:</span>
                    <span class="info-value">{{ $withdrawal->client->address ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div class="amount-highlight">
            💰 MONTANT À LIVRER: {{ number_format($withdrawal->amount, 3) }} DT
        </div>

        @if($withdrawal->assignedDeliverer)
        <div class="info-section" style="margin: 30px 0;">
            <div class="info-title">🚚 Informations Livreur</div>
            <div class="info-item">
                <span class="info-label">Nom livreur:</span>
                <span class="info-value">{{ $withdrawal->assignedDeliverer->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Téléphone:</span>
                <span class="info-value">{{ $withdrawal->assignedDeliverer->phone ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Date assignation:</span>
                <span class="info-value">{{ $withdrawal->assigned_at ? $withdrawal->assigned_at->format('d/m/Y à H:i') : 'N/A' }}</span>
            </div>
        </div>
        @endif

        <div class="barcode">
            ||| {{ $withdrawal->delivery_receipt_code }} |||
        </div>

        <div class="instructions">
            <div class="instructions-title">🔒 INSTRUCTIONS DE SÉCURITÉ</div>
            <div class="instruction-item">• Vérifiez l'identité du client avant la remise des espèces</div>
            <div class="instruction-item">• Demandez une pièce d'identité officielle</div>
            <div class="instruction-item">• Obtenez la signature du client ci-dessous</div>
            <div class="instruction-item">• Conservez ce bon jusqu'à la confirmation de livraison</div>
            <div class="instruction-item">• En cas de problème, contactez immédiatement le service commercial</div>
            @if($withdrawal->notes)
                <div class="instruction-item" style="margin-top: 15px; font-weight: bold;">
                    📝 Notes spéciales: {{ $withdrawal->notes }}
                </div>
            @endif
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-label">Signature du Client</div>
                <div style="margin-top: 30px; color: #9ca3af;">
                    Nom: {{ $withdrawal->client->name }}
                </div>
                <div style="margin-top: 10px; color: #9ca3af;">
                    Date: _______________
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-label">Signature du Livreur</div>
                <div style="margin-top: 30px; color: #9ca3af;">
                    Nom: {{ $withdrawal->assignedDeliverer->name ?? '_______________' }}
                </div>
                <div style="margin-top: 10px; color: #9ca3af;">
                    Date: _______________
                </div>
            </div>
        </div>

        <div class="footer">
            <p><strong>AL AMENA DELIVERY</strong> - Service de livraison professionnel</p>
            <p>Ce document est généré automatiquement le {{ now()->format('d/m/Y à H:i') }}</p>
            <p>Bon de livraison N° {{ $withdrawal->delivery_receipt_code }}</p>
        </div>
    </div>

    <script>
        // Auto-focus pour impression
        window.addEventListener('load', function() {
            if (window.location.search.includes('print=1')) {
                setTimeout(() => window.print(), 500);
            }
        });
    </script>
</body>
</html>