<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de Retour - {{ $returnPackage->return_package_code }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .receipt {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border: 2px solid #333;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 28px;
            color: #dc3545;
            margin-bottom: 10px;
        }
        .header .subtitle {
            font-size: 16px;
            color: #666;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            background: #333;
            color: white;
            padding: 10px 15px;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .info-item {
            padding: 10px;
            border-left: 4px solid #dc3545;
            background: #f8f9fa;
        }
        .info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        .barcode {
            text-align: center;
            padding: 20px;
            margin: 20px 0;
            border: 2px dashed #333;
            background: #fff;
        }
        .barcode-text {
            font-family: 'Courier New', monospace;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 3px;
        }
        .signature-box {
            border: 2px solid #333;
            padding: 60px 20px 20px 20px;
            text-align: center;
            margin-top: 30px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #333;
            color: #666;
            font-size: 12px;
        }
        .alert-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
            color: #856404;
        }
        @media print {
            body { background: white; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 12px 30px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
            <i class="fas fa-print"></i> Imprimer
        </button>
        <button onclick="window.close()" style="padding: 12px 30px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-left: 10px;">
            Fermer
        </button>
    </div>

    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <h1>🔄 BON DE RETOUR - ÉCHANGE</h1>
            <div class="subtitle">Retour au fournisseur</div>
        </div>

        <!-- Alert Box -->
        <div class="alert-box">
            ⚠️ COLIS ÉCHANGE - RETOUR OBLIGATOIRE
        </div>

        <!-- Barcode Section -->
        <div class="barcode">
            <div class="info-label">Code Colis Retour</div>
            <div class="barcode-text">{{ $returnPackage->return_package_code }}</div>
        </div>

        <!-- Informations Colis Original -->
        <div class="section">
            <div class="section-title">📦 COLIS ORIGINAL</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Code Colis</div>
                    <div class="info-value">{{ $returnPackage->originalPackage->package_code }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Date Livraison</div>
                    <div class="info-value">{{ $returnPackage->originalPackage->delivered_at ? $returnPackage->originalPackage->delivered_at->format('d/m/Y H:i') : 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Livré à</div>
                    <div class="info-value">{{ $returnPackage->originalPackage->recipient_data['name'] ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Téléphone</div>
                    <div class="info-value">{{ $returnPackage->originalPackage->recipient_data['phone'] ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- Informations Retour -->
        <div class="section">
            <div class="section-title">↩️ RETOUR AU FOURNISSEUR</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Fournisseur</div>
                    <div class="info-value">{{ $returnPackage->recipient_info['name'] ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Téléphone</div>
                    <div class="info-value">{{ $returnPackage->recipient_info['phone'] ?? 'N/A' }}</div>
                </div>
                <div class="info-item" style="grid-column: 1 / -1;">
                    <div class="info-label">Adresse</div>
                    <div class="info-value">{{ $returnPackage->recipient_info['address'] ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Gouvernorat</div>
                    <div class="info-value">{{ $returnPackage->originalPackage->delegationFrom->governorate ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Délégation</div>
                    <div class="info-value">{{ $returnPackage->originalPackage->delegationFrom->name ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- Informations Traitement -->
        <div class="section">
            <div class="section-title">✅ INFORMATIONS TRAITEMENT</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Traité par</div>
                    <div class="info-value">{{ $returnPackage->depot_manager_name ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Date Traitement</div>
                    <div class="info-value">{{ $returnPackage->created_at ? $returnPackage->created_at->format('d/m/Y H:i') : 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Statut</div>
                    <div class="info-value">{{ $returnPackage->status }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Raison</div>
                    <div class="info-value">{{ $returnPackage->return_reason }}</div>
                </div>
            </div>
        </div>

        <!-- Signature Box -->
        <div class="signature-box">
            <div style="font-weight: bold; margin-bottom: 10px;">SIGNATURE DU DESTINATAIRE (FOURNISSEUR)</div>
            <div style="color: #666; font-size: 12px;">Je confirme avoir reçu le colis échange en bon état</div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div style="font-weight: bold; margin-bottom: 5px;">Al Amena Delivery</div>
            <div>Document généré le {{ now()->format('d/m/Y à H:i') }}</div>
        </div>
    </div>
</body>
</html>
