<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de Recharge - {{ $topupRequest->request_code }}</title>
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
        }

        .amount-section {
            background: #f5f5f5;
            padding: 15px;
            margin: 20px 0;
            border: 2px solid #333;
            text-align: center;
        }

        .amount {
            font-size: 28px;
            font-weight: bold;
            color: #2563eb;
        }

        .qr-section {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
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
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            width: 200px;
            margin: 30px auto 10px;
        }

        .print-button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 20px 0;
        }

        .print-button:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
    <!-- Bouton d'impression (masqué à l'impression) -->
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button class="print-button" onclick="window.print()">🖨️ Imprimer ce reçu</button>
        <button class="print-button" onclick="window.close()" style="background: #6b7280;">Fermer</button>
    </div>

    <!-- En-tête du reçu -->
    <div class="receipt-header">
        <div class="company-logo">🚚 AL-AMENA DELIVERY</div>
        <div>Service de livraison professionnel</div>
        <div style="font-size: 12px; margin-top: 5px;">
            📞 +216 XX XXX XXX | 📧 contact@al-amena.tn
        </div>
    </div>

    <!-- Titre -->
    <div class="receipt-title">
        🔄 REÇU DE RECHARGE CLIENT
    </div>

    <!-- Informations de base -->
    <div class="receipt-info">
        <div class="info-row">
            <span class="info-label">Code recharge:</span>
            <span>{{ $topupRequest->request_code }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date & Heure:</span>
            <span>{{ $topupRequest->processed_at->format('d/m/Y à H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Livreur:</span>
            <span>{{ $topupRequest->processedBy->name ?? 'N/A' }}</span>
        </div>
    </div>

    <!-- Informations client -->
    <div style="border-top: 1px solid #ddd; padding-top: 15px; margin-top: 15px;">
        <h4 style="margin: 0 0 10px 0; font-size: 14px;">👤 INFORMATIONS CLIENT</h4>
        <div class="info-row">
            <span class="info-label">Nom:</span>
            <span>{{ $topupRequest->client->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Téléphone:</span>
            <span>{{ $topupRequest->client->phone }}</span>
        </div>
        @if($topupRequest->client->email)
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span style="font-size: 12px;">{{ $topupRequest->client->email }}</span>
        </div>
        @endif
    </div>

    <!-- Montant principal -->
    <div class="amount-section">
        <div style="font-size: 14px; margin-bottom: 10px;">MONTANT RECHARGÉ</div>
        <div class="amount">{{ number_format($topupRequest->amount, 3) }} DT</div>
        <div style="font-size: 12px; margin-top: 10px; color: #6b7280;">
            Méthode: {{ $topupRequest->method === 'CASH' ? 'Espèces' : $topupRequest->method }}
        </div>
    </div>

    <!-- Détails de la transaction -->
    <div style="border-top: 1px solid #ddd; padding-top: 15px;">
        <h4 style="margin: 0 0 10px 0; font-size: 14px;">💳 DÉTAILS TRANSACTION</h4>
        <div class="info-row">
            <span class="info-label">Statut:</span>
            <span style="color: #059669; font-weight: bold;">
                ✅ {{ $topupRequest->status === 'VALIDATED' ? 'VALIDÉE' : $topupRequest->status }}
            </span>
        </div>
        @if($topupRequest->validation_notes)
        <div class="info-row">
            <span class="info-label">Notes:</span>
            <span style="font-size: 12px;">{{ $topupRequest->validation_notes }}</span>
        </div>
        @endif
        @if($topupRequest->reference)
        <div class="info-row">
            <span class="info-label">Référence:</span>
            <span style="font-size: 12px;">{{ $topupRequest->reference }}</span>
        </div>
        @endif
    </div>

    <!-- Code QR pour vérification -->
    <div class="qr-section">
        <div style="font-size: 12px; margin-bottom: 10px;">
            🔍 Code QR de vérification
        </div>
        <div style="border: 1px solid #ddd; padding: 20px; background: white;">
            <div id="qrcode" style="margin: 0 auto; width: 150px; height: 150px; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #6b7280;">
                [QR Code de vérification]
            </div>
        </div>
        <div style="font-size: 10px; margin-top: 5px; color: #6b7280;">
            Scannez pour vérifier l'authenticité
        </div>
        <div style="font-size: 10px; margin-top: 5px;">
            URL: {{ route('public.verify.topup', $topupRequest->request_code) }}
        </div>
    </div>

    <!-- Section signature -->
    <div class="signature-section">
        <div style="display: flex; justify-content: space-between; margin-top: 30px;">
            <div style="text-align: center; width: 45%;">
                <div class="signature-line"></div>
                <div style="font-size: 12px;">Signature Client</div>
            </div>
            <div style="text-align: center; width: 45%;">
                <div class="signature-line"></div>
                <div style="font-size: 12px;">Signature Livreur</div>
            </div>
        </div>
    </div>

    <!-- Conditions importantes -->
    <div style="border-top: 1px solid #ddd; padding-top: 15px; margin-top: 20px; font-size: 11px;">
        <h4 style="margin: 0 0 10px 0; font-size: 12px;">ℹ️ CONDITIONS IMPORTANTES</h4>
        <ul style="margin: 0; padding-left: 15px; line-height: 1.3;">
            <li>Cette recharge est immédiatement créditée sur votre compte client</li>
            <li>Le montant apparaîtra dans votre wallet dans les 2-3 minutes</li>
            <li>Conservez ce reçu comme preuve de paiement</li>
            <li>En cas de problème, contactez le service client avec ce code</li>
            <li>Ce reçu peut être vérifié via le QR code ci-dessus</li>
        </ul>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <div style="margin-bottom: 10px;">
            <strong>MERCI DE VOTRE CONFIANCE</strong>
        </div>
        <div>
            AL-AMENA DELIVERY - Service de livraison professionnel<br>
            📞 Support 24/7: +216 XX XXX XXX<br>
            🌐 www.al-amena-delivery.tn
        </div>
        <div style="margin-top: 10px; font-size: 10px; color: #6b7280;">
            Reçu généré le {{ now()->format('d/m/Y à H:i:s') }} - Document officiel
        </div>
    </div>

    <script>
        // Auto-print quand la page est chargée
        window.addEventListener('load', function() {
            // Petit délai pour s'assurer que tout est chargé
            setTimeout(function() {
                if (window.location.search.includes('auto-print=true')) {
                    window.print();
                }
            }, 500);
        });

        // Générer QR code simple (placeholder)
        document.addEventListener('DOMContentLoaded', function() {
            const qrDiv = document.getElementById('qrcode');

            // Simulation d'un QR code avec des caractères
            const qrPattern = `
                ████ ██ ████
                █  █ ██ █  █
                █  █ ██ █  █
                ████ ██ ████
                ██ ████ ██ █
                █  █ ██ █  █
                ████ ██ ████
            `;

            qrDiv.innerHTML = `<pre style="font-size: 8px; line-height: 1; margin: 0;">${qrPattern}</pre>`;
        });
    </script>
</body>
</html>