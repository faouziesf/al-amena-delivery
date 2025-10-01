<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de Livraison - {{ $package->code }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            margin: 20px;
            max-width: 400px;
            margin: 0 auto;
        }

        .receipt {
            border: 2px solid #000;
            padding: 15px;
            background: white;
        }

        .header {
            text-align: center;
            border-bottom: 1px dashed #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #2563EB;
        }

        .receipt-title {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }

        .receipt-number {
            font-size: 12px;
            color: #666;
        }

        .section {
            margin: 15px 0;
            padding: 8px;
            background: #f9f9f9;
            border-radius: 5px;
        }

        .section-title {
            font-weight: bold;
            font-size: 12px;
            color: #2563EB;
            margin-bottom: 5px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 12px;
        }

        .info-label {
            font-weight: bold;
            color: #333;
        }

        .barcode {
            text-align: center;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            font-weight: bold;
            border: 1px solid #333;
            padding: 8px;
            margin: 10px 0;
            background: white;
        }

        .barcode-lines {
            font-size: 10px;
            letter-spacing: 1px;
        }

        .cod-section {
            background: #dcfce7;
            border: 1px solid #16a34a;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            margin: 15px 0;
        }

        .cod-amount {
            font-size: 24px;
            font-weight: bold;
            color: #16a34a;
        }

        .signature-section {
            border: 1px solid #333;
            padding: 10px;
            margin: 15px 0;
            height: 80px;
            position: relative;
        }

        .signature-label {
            font-size: 10px;
            font-weight: bold;
            position: absolute;
            top: 5px;
            left: 10px;
        }

        .status {
            background: #16a34a;
            color: white;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            border-radius: 5px;
            margin: 10px 0;
        }

        .footer {
            border-top: 1px dashed #333;
            padding-top: 10px;
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .delivery-info {
            background: #e0f2fe;
            border: 1px solid #0284c7;
            padding: 8px;
            border-radius: 5px;
            margin: 10px 0;
        }

        @media screen {
            .print-controls {
                position: fixed;
                top: 10px;
                right: 10px;
                z-index: 1000;
            }

            .btn {
                padding: 8px 15px;
                margin: 0 3px;
                border: none;
                border-radius: 3px;
                cursor: pointer;
                font-weight: bold;
                font-size: 12px;
            }

            .btn-print {
                background: #2563EB;
                color: white;
            }

            .btn-close {
                background: #6B7280;
                color: white;
            }
        }
    </style>
</head>
<body>
    <!-- Contrôles d'impression (masqués à l'impression) -->
    <div class="print-controls no-print">
        <button class="btn btn-print" onclick="window.print()">🖨️ Imprimer</button>
        <button class="btn btn-close" onclick="window.close()">❌ Fermer</button>
    </div>

    <div class="receipt">
        <!-- En-tête -->
        <div class="header">
            <div class="company-name">AL-AMENA DELIVERY</div>
            <div class="receipt-title">REÇU DE LIVRAISON</div>
            <div class="receipt-number">N° {{ $package->code }}</div>
        </div>

        <!-- Code-Barres -->
        <div class="barcode">
            {{ $package->code }}
            <div class="barcode-lines">||||||||||||||||||||||||||||||||||||</div>
        </div>

        <!-- Informations Expéditeur -->
        <div class="section">
            <div class="section-title">EXPÉDITEUR</div>
            <div class="info-row">
                <span class="info-label">Client:</span>
                <span>{{ $package->client ? $package->client->name : 'N/A' }}</span>
            </div>
            @if($package->client && $package->client->phone)
            <div class="info-row">
                <span class="info-label">Téléphone:</span>
                <span>{{ $package->client->phone }}</span>
            </div>
            @endif
        </div>

        <!-- Informations Destinataire -->
        <div class="section">
            <div class="section-title">DESTINATAIRE</div>
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
                <span>{{ $package->recipient_address }}</span>
            </div>
            @if($package->recipient_city)
            <div class="info-row">
                <span class="info-label">Ville:</span>
                <span>{{ $package->recipient_city }}</span>
            </div>
            @endif
        </div>

        <!-- COD si applicable -->
        @if($package->cod_amount > 0)
        <div class="cod-section">
            <div style="font-size: 12px; margin-bottom: 5px;">MONTANT COLLECTÉ (COD)</div>
            <div class="cod-amount">{{ number_format($package->cod_amount, 3) }} DT</div>
        </div>
        @endif

        <!-- Informations de Livraison -->
        <div class="delivery-info">
            <div class="section-title">DÉTAILS DE LIVRAISON</div>
            <div class="info-row">
                <span class="info-label">Livreur:</span>
                <span>{{ auth()->user()->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date:</span>
                <span>{{ $package->updated_at->format('d/m/Y à H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Statut:</span>
                <span>{{ $package->status === 'DELIVERED' ? 'LIVRÉ' : 'COLLECTÉ' }}</span>
            </div>
        </div>

        <!-- Statut -->
        <div class="status">
            ✓ {{ $package->status === 'DELIVERED' ? 'COLIS LIVRÉ AVEC SUCCÈS' : 'COLIS COLLECTÉ AVEC SUCCÈS' }}
        </div>

        <!-- Zone de Signature -->
        <div class="signature-section">
            <div class="signature-label">SIGNATURE DU DESTINATAIRE</div>
            @if($package->signature_path)
                <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #666;">
                    [Signature Numérique Enregistrée]
                </div>
            @endif
        </div>

        <!-- Notes si disponibles -->
        @if($package->notes)
        <div class="section">
            <div class="section-title">NOTES</div>
            <div style="font-size: 12px;">{{ $package->notes }}</div>
        </div>
        @endif

        <!-- Pied de page -->
        <div class="footer">
            <p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
            <p>Merci d'avoir choisi Al-Amena Delivery</p>
            <p>www.al-amena.com | Service Client: +216 70 123 456</p>
        </div>
    </div>

    <script>
        // Auto-impression si demandée
        if(window.location.search.includes('autoprint=1')) {
            window.onload = function() {
                setTimeout(() => {
                    window.print();
                }, 500);
            }
        }
    </script>
</body>
</html>