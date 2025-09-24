<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de Livraison - {{ $package->package_code }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            color: #333;
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .company-logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .receipt-title {
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0;
            color: #2563eb;
        }

        .receipt-info {
            margin: 20px 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 3px 0;
        }

        .info-label {
            font-weight: bold;
            width: 40%;
        }

        .info-value {
            width: 60%;
            text-align: right;
        }

        .amount-section {
            background: #f0f9ff;
            padding: 15px;
            margin: 20px 0;
            border: 2px solid #2563eb;
            text-align: center;
            border-radius: 8px;
        }

        .cod-amount {
            font-size: 32px;
            font-weight: bold;
            color: #2563eb;
            margin: 10px 0;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-delivered {
            background: #dcfce7;
            color: #16a34a;
            border: 1px solid #16a34a;
        }

        .qr-section {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            background: #f9f9f9;
        }

        .footer {
            border-top: 1px solid #333;
            padding-top: 15px;
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
        }

        .signature-section {
            margin: 30px 0;
        }

        .signature-line {
            border-top: 1px solid #333;
            width: 200px;
            margin: 30px auto 10px;
        }

        .section-divider {
            border-top: 1px dashed #ccc;
            margin: 20px 0;
            padding-top: 15px;
        }

        .print-button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px 5px;
        }

        .tracking-info {
            background: #fff7ed;
            border: 1px solid #fb923c;
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <!-- Boutons d'action (masqués à l'impression) -->
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button class="print-button" onclick="window.print()">🖨️ Imprimer</button>
        <button class="print-button" onclick="window.close()" style="background: #6b7280;">Fermer</button>
        <a href="{{ route('deliverer.packages.show', $package) }}" class="print-button" style="background: #059669; text-decoration: none; display: inline-block;">📦 Voir Colis</a>
    </div>

    <!-- En-tête du reçu -->
    <div class="receipt-header">
        <div class="company-logo">🚚 AL-AMENA DELIVERY</div>
        <div>Service de livraison professionnel</div>
        <div style="font-size: 12px; margin-top: 5px;">
            📞 +216 XX XXX XXX | 📧 contact@al-amena.tn
        </div>
    </div>

    <!-- Titre et statut -->
    <div class="receipt-title">
        📦 REÇU DE LIVRAISON
    </div>

    <div style="text-align: center; margin: 15px 0;">
        <span class="status-badge status-delivered">✅ LIVRÉ</span>
    </div>

    <!-- Informations de base -->
    <div class="receipt-info">
        <div class="info-row">
            <span class="info-label">Code colis:</span>
            <span class="info-value" style="font-weight: bold;">{{ $package->package_code }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date livraison:</span>
            <span class="info-value">{{ $package->delivered_at->format('d/m/Y à H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Livreur:</span>
            <span class="info-value">{{ $package->assignedDeliverer->name ?? 'N/A' }}</span>
        </div>
        @if($package->tracking_number)
        <div class="info-row">
            <span class="info-label">N° suivi:</span>
            <span class="info-value" style="font-family: monospace;">{{ $package->tracking_number }}</span>
        </div>
        @endif
    </div>

    <!-- Informations expéditeur/destinataire -->
    <div class="section-divider">
        <h4 style="margin: 0 0 10px 0; font-size: 14px;">📤 EXPÉDITEUR</h4>
        <div class="info-row">
            <span class="info-label">Nom:</span>
            <span class="info-value">{{ $package->sender->name ?? ($package->sender_data['name'] ?? 'N/A') }}</span>
        </div>
        @if(isset($package->sender_data['phone']))
        <div class="info-row">
            <span class="info-label">Téléphone:</span>
            <span class="info-value">{{ $package->sender_data['phone'] }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Délégation:</span>
            <span class="info-value">{{ $package->delegationFrom->name ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="section-divider">
        <h4 style="margin: 0 0 10px 0; font-size: 14px;">📥 DESTINATAIRE</h4>
        <div class="info-row">
            <span class="info-label">Nom:</span>
            <span class="info-value">{{ $package->recipient_data['name'] ?? 'N/A' }}</span>
        </div>
        @if(isset($package->recipient_data['phone']))
        <div class="info-row">
            <span class="info-label">Téléphone:</span>
            <span class="info-value">{{ $package->recipient_data['phone'] }}</span>
        </div>
        @endif
        @if(isset($package->recipient_data['address']))
        <div class="info-row">
            <span class="info-label">Adresse:</span>
            <span class="info-value" style="font-size: 12px;">{{ $package->recipient_data['address'] }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Délégation:</span>
            <span class="info-value">{{ $package->delegationTo->name ?? 'N/A' }}</span>
        </div>
    </div>

    <!-- Détails du colis -->
    <div class="section-divider">
        <h4 style="margin: 0 0 10px 0; font-size: 14px;">📋 DÉTAILS COLIS</h4>
        @if($package->content_description)
        <div class="info-row">
            <span class="info-label">Contenu:</span>
            <span class="info-value" style="font-size: 12px;">{{ $package->content_description }}</span>
        </div>
        @endif
        @if($package->package_weight)
        <div class="info-row">
            <span class="info-label">Poids:</span>
            <span class="info-value">{{ $package->package_weight }} kg</span>
        </div>
        @endif
        @if($package->package_value)
        <div class="info-row">
            <span class="info-label">Valeur déclarée:</span>
            <span class="info-value">{{ number_format($package->package_value, 3) }} DT</span>
        </div>
        @endif
        @if($package->delivery_attempts > 1)
        <div class="info-row">
            <span class="info-label">Tentatives:</span>
            <span class="info-value">{{ $package->delivery_attempts }}/3</span>
        </div>
        @endif
    </div>

    <!-- Montant COD -->
    <div class="amount-section">
        <div style="font-size: 14px; margin-bottom: 5px;">💰 MONTANT COD COLLECTÉ</div>
        <div class="cod-amount">{{ number_format($package->cod_amount, 3) }} DT</div>
        <div style="font-size: 12px; color: #6b7280; margin-top: 5px;">
            Méthode: Espèces | Statut: ✅ Collecté
        </div>
        @if($transaction)
        <div style="font-size: 11px; color: #6b7280; margin-top: 5px;">
            Réf. transaction: {{ $transaction->transaction_id }}
        </div>
        @endif
    </div>

    <!-- Informations de livraison -->
    @if($package->delivery_notes)
    <div class="section-divider">
        <h4 style="margin: 0 0 10px 0; font-size: 14px;">📝 NOTES DE LIVRAISON</h4>
        <div style="font-size: 12px; padding: 8px; background: #f9f9f9; border-radius: 4px;">
            {{ $package->delivery_notes }}
        </div>
    </div>
    @endif

    <!-- Tracking et vérification -->
    <div class="tracking-info">
        <div style="font-size: 12px; font-weight: bold; margin-bottom: 5px;">
            🔍 VÉRIFICATION ET SUIVI
        </div>
        <div style="font-size: 11px; line-height: 1.3;">
            • Suivi en ligne: {{ route('public.track.package', $package->package_code) }}<br>
            • Vérification reçu: {{ route('public.verify.receipt', $package->tracking_number) }}<br>
            • Code de vérification: {{ strtoupper(substr($package->package_code, -8)) }}
        </div>
    </div>

    <!-- Code QR pour vérification -->
    <div class="qr-section">
        <div style="font-size: 12px; margin-bottom: 10px; font-weight: bold;">
            📱 Code QR de vérification
        </div>
        <div style="border: 1px solid #ddd; padding: 20px; background: white; margin: 10px 0;">
            <div id="qrcode" style="margin: 0 auto; width: 120px; height: 120px; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #6b7280; border: 1px dashed #ccc;">
                [QR Code]<br>{{ $package->package_code }}
            </div>
        </div>
        <div style="font-size: 10px; color: #6b7280;">
            Scannez pour vérifier l'authenticité de ce reçu
        </div>
    </div>

    <!-- Section signature -->
    <div class="signature-section">
        <div style="text-align: center; margin: 30px 0;">
            <div style="display: inline-block; text-align: center; width: 200px;">
                <div class="signature-line"></div>
                <div style="font-size: 12px; margin-top: 5px;">Signature du destinataire</div>
                <div style="font-size: 10px; color: #6b7280; margin-top: 3px;">
                    {{ $package->recipient_data['name'] ?? 'N/A' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Conditions importantes -->
    <div style="border-top: 1px solid #ddd; padding-top: 15px; margin-top: 20px; font-size: 11px;">
        <h4 style="margin: 0 0 8px 0; font-size: 12px;">ℹ️ CONDITIONS ET GARANTIES</h4>
        <ul style="margin: 0; padding-left: 15px; line-height: 1.2;">
            <li>Colis livré en parfait état et conforme à la description</li>
            <li>Montant COD collecté intégralement selon les termes convenus</li>
            <li>Livraison effectuée dans les délais prévus</li>
            <li>Ce reçu fait foi de la bonne exécution du service</li>
            <li>Réclamation possible sous 24h via notre service client</li>
        </ul>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <div style="margin-bottom: 10px; font-weight: bold;">
            ✅ LIVRAISON RÉUSSIE - MERCI DE VOTRE CONFIANCE
        </div>
        <div style="line-height: 1.3;">
            AL-AMENA DELIVERY - Service de livraison professionnel<br>
            📞 Support 24/7: +216 XX XXX XXX<br>
            📧 support@al-amena-delivery.tn<br>
            🌐 www.al-amena-delivery.tn
        </div>
        <div style="margin-top: 15px; font-size: 10px; color: #6b7280; border-top: 1px solid #eee; padding-top: 10px;">
            Reçu généré le {{ now()->format('d/m/Y à H:i:s') }}<br>
            Document officiel - Conservez précieusement
        </div>
    </div>

    <script>
        // Auto-print si demandé
        window.addEventListener('load', function() {
            setTimeout(function() {
                if (window.location.search.includes('auto-print=true')) {
                    window.print();
                }
            }, 500);
        });

        // Génération QR code simulé
        document.addEventListener('DOMContentLoaded', function() {
            const qrDiv = document.getElementById('qrcode');
            // Simple pattern pour simulation
            qrDiv.innerHTML = `
                <div style="font-family: monospace; font-size: 8px; line-height: 1;">
                    ████ ██ ████<br>
                    █  █ ██ █  █<br>
                    █ ██ ██ ██ █<br>
                    ████ ██ ████<br>
                    ██ █ ██ █ ██<br>
                    █  █ ██ █  █<br>
                    ████ ██ ████
                </div>
            `;
        });
    </script>
</body>
</html>