<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de Livraison - {{ $package->package_code }}</title>
    <style>
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body { margin: 0; padding: 20px; }
        }
        @media screen {
            .print-only { display: none; }
            body { background: #f5f5f5; padding: 20px; }
            .receipt { background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        }

        .receipt {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 5px;
        }

        .document-title {
            font-size: 20px;
            color: #4a5568;
            margin-bottom: 10px;
        }

        .receipt-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-section {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #3182ce;
        }

        .info-title {
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .info-item {
            margin-bottom: 8px;
            color: #4a5568;
        }

        .info-label {
            font-weight: 600;
            color: #2d3748;
        }

        .package-details {
            background: #edf2f7;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #cbd5e0;
        }

        .detail-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 18px;
            color: #1a365d;
        }

        .cod-amount {
            color: #e53e3e;
            font-weight: bold;
            font-size: 20px;
        }

        .signature-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 50px;
            padding-top: 30px;
            border-top: 2px solid #e2e8f0;
        }

        .signature-box {
            text-align: center;
        }

        .signature-line {
            border-bottom: 2px solid #4a5568;
            margin: 40px 0 10px 0;
            height: 60px;
        }

        .signature-label {
            font-weight: 600;
            color: #2d3748;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #718096;
            font-size: 14px;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #3182ce;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .print-btn:hover {
            background: #2c5282;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-btn no-print">🖨️ Imprimer</button>

    <div class="receipt">
        <div class="header">
            <div class="company-name">AL AMENA DELIVERY</div>
            <div class="document-title">REÇU DE LIVRAISON</div>
            <div style="color: #718096; font-size: 14px;">
                {{ now()->format('d/m/Y à H:i') }}
            </div>
        </div>

        <div class="receipt-info">
            <div class="info-section">
                <div class="info-title">📦 INFORMATIONS DU COLIS</div>
                <div class="info-item">
                    <span class="info-label">Code:</span> {{ $package->package_code }}
                </div>
                <div class="info-item">
                    <span class="info-label">Date de livraison:</span>
                    {{ $package->delivered_at ? $package->delivered_at->format('d/m/Y à H:i') : 'N/A' }}
                </div>
                <div class="info-item">
                    <span class="info-label">Livreur:</span> {{ Auth::user()->name }}
                </div>
                @if($package->delivery_notes)
                <div class="info-item">
                    <span class="info-label">Notes:</span> {{ $package->delivery_notes }}
                </div>
                @endif
            </div>

            <div class="info-section">
                <div class="info-title">👤 DESTINATAIRE</div>
                <div class="info-item">
                    <span class="info-label">Nom:</span> {{ $recipientData['name'] ?? 'N/A' }}
                </div>
                <div class="info-item">
                    <span class="info-label">Téléphone:</span> {{ $recipientData['phone'] ?? 'N/A' }}
                </div>
                <div class="info-item">
                    <span class="info-label">Adresse:</span>
                    {{ $recipientData['address'] ?? 'N/A' }}
                </div>
                @if(isset($recipientData['city']))
                <div class="info-item">
                    <span class="info-label">Ville:</span> {{ $recipientData['city'] }}
                </div>
                @endif
            </div>
        </div>

        @if(isset($senderData))
        <div class="info-section" style="margin-bottom: 30px;">
            <div class="info-title">📤 EXPÉDITEUR</div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <div class="info-item">
                        <span class="info-label">Nom:</span> {{ $senderData['name'] ?? 'N/A' }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Téléphone:</span> {{ $senderData['phone'] ?? 'N/A' }}
                    </div>
                </div>
                <div>
                    <div class="info-item">
                        <span class="info-label">Adresse:</span> {{ $senderData['address'] ?? 'N/A' }}
                    </div>
                    @if(isset($senderData['city']))
                    <div class="info-item">
                        <span class="info-label">Ville:</span> {{ $senderData['city'] }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <div class="package-details">
            <div class="detail-row">
                <span>💰 Montant COD (Cash on Delivery)</span>
                <span class="cod-amount">{{ number_format($package->cod_amount, 3) }} DT</span>
            </div>
            @if($package->weight)
            <div class="detail-row">
                <span>⚖️ Poids du colis</span>
                <span>{{ $package->weight }} kg</span>
            </div>
            @endif
            @if($package->description)
            <div class="detail-row">
                <span>📋 Contenu du colis</span>
                <span>{{ Str::limit($package->description, 50) }}</span>
            </div>
            @endif
            <div class="detail-row">
                <span><strong>✅ LIVRAISON CONFIRMÉE</strong></span>
                <span><strong>{{ number_format($package->cod_amount, 3) }} DT</strong></span>
            </div>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <div style="font-weight: 600; margin-bottom: 10px;">SIGNATURE DU DESTINATAIRE</div>
                <div style="color: #718096; font-size: 14px; margin-bottom: 20px;">
                    Je certifie avoir reçu le colis en bon état
                </div>
                <div class="signature-line"></div>
                <div class="signature-label">{{ $recipientData['name'] ?? 'Destinataire' }}</div>
                <div style="color: #718096; font-size: 12px;">Date: {{ now()->format('d/m/Y') }}</div>
            </div>

            <div class="signature-box">
                <div style="font-weight: 600; margin-bottom: 10px;">SIGNATURE DU LIVREUR</div>
                <div style="color: #718096; font-size: 14px; margin-bottom: 20px;">
                    Livraison effectuée avec succès
                </div>
                <div class="signature-line"></div>
                <div class="signature-label">{{ Auth::user()->name }}</div>
                <div style="color: #718096; font-size: 12px;">ID: {{ Auth::id() }}</div>
            </div>
        </div>

        <div class="footer">
            <div>AL AMENA DELIVERY - Service de livraison professionnel</div>
            <div style="margin-top: 5px;">
                Ce reçu certifie la livraison du colis {{ $package->package_code }} le {{ now()->format('d/m/Y à H:i') }}
            </div>
        </div>
    </div>

    <script>
        // Auto-print for mobile devices
        if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            setTimeout(() => {
                window.print();
            }, 1000);
        }
    </script>
</body>
</html>