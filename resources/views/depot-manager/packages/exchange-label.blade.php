<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étiquette Retour Échange - {{ $returnPackage->package_code }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: white;
        }

        .label-container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 3px solid #f59e0b;
            border-radius: 15px;
            background: white;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #f59e0b;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #f59e0b;
            margin-bottom: 5px;
        }

        .label-title {
            font-size: 18px;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 5px;
        }

        .package-code {
            font-size: 20px;
            font-weight: bold;
            color: #1f2937;
            background: #fef3c7;
            padding: 10px;
            border-radius: 8px;
            display: inline-block;
            margin: 10px 0;
        }

        .barcode {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            background: #f3f4f6;
            border-radius: 5px;
            letter-spacing: 2px;
        }

        .info-section {
            margin: 15px 0;
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
            border-left: 4px solid #f59e0b;
        }

        .info-title {
            font-size: 14px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }

        .info-label {
            font-weight: bold;
            color: #6b7280;
            flex: 0 0 30%;
        }

        .info-value {
            color: #1f2937;
            flex: 1;
            text-align: right;
        }

        .exchange-notice {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            border-radius: 10px;
            padding: 15px;
            margin: 15px 0;
            text-align: center;
        }

        .exchange-notice h3 {
            color: #92400e;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .exchange-notice p {
            color: #451a03;
            font-size: 12px;
        }

        .original-package {
            background: #e0f2fe;
            border: 1px solid #0284c7;
            border-radius: 8px;
            padding: 10px;
            margin: 10px 0;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #f59e0b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            z-index: 100;
        }

        .print-button:hover {
            background: #d97706;
        }

        @media print {
            body { background: white; }
            .print-button { display: none; }
            .label-container {
                max-width: none;
                margin: 0;
                border: 2px solid #f59e0b;
                page-break-inside: avoid;
            }
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            background: #dc2626;
            color: white;
        }

        .instructions {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 12px;
            margin: 15px 0;
        }

        .instructions h4 {
            color: #dc2626;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .instructions ul {
            margin-left: 15px;
            color: #7f1d1d;
            font-size: 11px;
        }

        .instructions li {
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">🖨️ Imprimer Étiquette</button>

    <div class="label-container">
        <div class="header">
            <div class="company-name">AL-AMENA DELIVERY</div>
            <div class="label-title">🔄 ÉTIQUETTE RETOUR ÉCHANGE</div>
            <div class="status-badge">RETOUR FOURNISSEUR</div>
        </div>

        <!-- Code du nouveau colis de retour -->
        <div style="text-align: center;">
            <div class="package-code">{{ $returnPackage->package_code }}</div>
            <div class="barcode">
                ||| {{ implode(' | ', str_split($returnPackage->package_code, 1)) }} |||
            </div>
        </div>

        <!-- Notice d'échange -->
        <div class="exchange-notice">
            <h3>🔄 COLIS D'ÉCHANGE - RETOUR</h3>
            <p>Ce colis doit être retourné au fournisseur dans le cadre d'un échange</p>
        </div>

        <!-- Informations du colis original -->
        <div class="original-package">
            <h4 style="color: #0284c7; font-size: 12px; margin-bottom: 8px;">📦 Colis d'origine échangé :</h4>
            <div style="font-size: 11px; color: #0c4a6e;">
                <strong>Code:</strong> {{ $package->package_code }} |
                <strong>Contenu:</strong> {{ $package->content_description }} |
                <strong>COD:</strong> {{ number_format($package->cod_amount, 3) }} DT
            </div>
        </div>

        <!-- Informations expéditeur (fournisseur) -->
        <div class="info-section">
            <div class="info-title">
                📤 RETOUR VERS (Fournisseur)
            </div>
            <div class="info-row">
                <span class="info-label">Nom:</span>
                <span class="info-value">{{ $returnPackage->recipient_data['name'] ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Téléphone:</span>
                <span class="info-value">{{ $returnPackage->recipient_data['phone'] ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Délégation:</span>
                <span class="info-value">{{ $package->delegationFrom->name ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Informations du retour -->
        <div class="info-section">
            <div class="info-title">
                🏷️ DÉTAILS DU RETOUR
            </div>
            <div class="info-row">
                <span class="info-label">Type:</span>
                <span class="info-value">Retour d'échange</span>
            </div>
            <div class="info-row">
                <span class="info-label">Montant COD:</span>
                <span class="info-value">{{ number_format($returnPackage->cod_amount, 3) }} DT</span>
            </div>
            <div class="info-row">
                <span class="info-label">Frais de livraison:</span>
                <span class="info-value">{{ number_format($returnPackage->delivery_fee, 3) }} DT</span>
            </div>
            <div class="info-row">
                <span class="info-label">Généré le:</span>
                <span class="info-value">{{ now()->format('d/m/Y à H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Généré par:</span>
                <span class="info-value">{{ $user->name }}</span>
            </div>
        </div>

        <!-- Instructions de manipulation -->
        <div class="instructions">
            <h4>⚠️ INSTRUCTIONS IMPORTANTES</h4>
            <ul>
                <li>Coller cette étiquette sur le colis d'échange ramené par le livreur</li>
                <li>Ce colis doit être traité comme un retour fournisseur standard</li>
                <li>Aucun montant COD à collecter pour ce retour</li>
                <li>Vérifier l'état du produit avant expédition</li>
                <li>Conserver une copie de cette étiquette pour suivi</li>
            </ul>
        </div>

        <!-- Signatures -->
        <div style="display: flex; justify-content: space-between; margin-top: 20px;">
            <div style="width: 45%; text-align: center;">
                <div style="border-top: 1px solid #374151; margin-top: 40px; padding-top: 5px;">
                    <small>Signature Chef Dépôt</small>
                </div>
            </div>
            <div style="width: 45%; text-align: center;">
                <div style="border-top: 1px solid #374151; margin-top: 40px; padding-top: 5px;">
                    <small>Signature Livreur</small>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="text-align: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
            <small style="color: #6b7280;">
                Étiquette générée le {{ now()->format('d/m/Y à H:i:s') }} |
                Référence: {{ $returnPackage->package_code }}
            </small>
        </div>
    </div>

    <script>
        // Auto-impression après 2 secondes
        setTimeout(() => {
            window.print();
        }, 2000);
    </script>
</body>
</html>