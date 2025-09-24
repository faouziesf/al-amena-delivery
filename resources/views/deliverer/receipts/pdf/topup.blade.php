<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de Recharge PDF - {{ $topup->request_code }}</title>
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
            color: #059669;
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
            width: 45%;
        }

        .info-value {
            width: 55%;
            text-align: right;
        }

        .amount-section {
            background: #ecfdf5;
            padding: 15px;
            margin: 20px 0;
            border: 2px solid #059669;
            text-align: center;
            border-radius: 8px;
        }

        .topup-amount {
            font-size: 32px;
            font-weight: bold;
            color: #059669;
            margin: 10px 0;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            background: #dcfce7;
            color: #16a34a;
            border: 1px solid #16a34a;
        }

        .client-section {
            background: #f0f9ff;
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            border: 1px solid #0ea5e9;
        }

        .qr-section {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            background: #f9f9f9;
            border-radius: 8px;
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
            width: 180px;
            margin: 25px auto 8px;
        }

        .section-divider {
            border-top: 1px dashed #ccc;
            margin: 20px 0;
            padding-top: 15px;
        }

        .print-button {
            background: #059669;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px 5px;
        }

        .important-info {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            padding: 12px;
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
        <a href="{{ route('deliverer.client-topup.show', $topup) }}" class="print-button" style="background: #2563eb; text-decoration: none; display: inline-block;">🔄 Voir Recharge</a>
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
        🔄 REÇU DE RECHARGE CLIENT
    </div>

    <div style="text-align: center; margin: 15px 0;">
        <span class="status-badge">✅ VALIDÉE</span>
    </div>

    <!-- Informations de base -->
    <div class="receipt-info">
        <div class="info-row">
            <span class="info-label">Code recharge:</span>
            <span class="info-value" style="font-weight: bold; font-family: monospace;">{{ $topup->request_code }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date & Heure:</span>
            <span class="info-value">{{ $topup->processed_at->format('d/m/Y à H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Livreur:</span>
            <span class="info-value">{{ $topup->processedBy->name ?? 'N/A' }}</span>
        </div>
        @if($topup->reference)
        <div class="info-row">
            <span class="info-label">Référence:</span>
            <span class="info-value" style="font-family: monospace; font-size: 11px;">{{ $topup->reference }}</span>
        </div>
        @endif
    </div>

    <!-- Informations client -->
    <div class="client-section">
        <h4 style="margin: 0 0 12px 0; font-size: 14px; color: #0ea5e9;">👤 CLIENT BÉNÉFICIAIRE</h4>
        <div class="info-row">
            <span class="info-label">Nom complet:</span>
            <span class="info-value" style="font-weight: bold;">{{ $topup->client->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Téléphone:</span>
            <span class="info-value" style="font-family: monospace;">{{ $topup->client->phone }}</span>
        </div>
        @if($topup->client->email)
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value" style="font-size: 11px;">{{ $topup->client->email }}</span>
        </div>
        @endif
        @if($topup->client->shop_name)
        <div class="info-row">
            <span class="info-label">Boutique:</span>
            <span class="info-value" style="font-size: 12px;">{{ $topup->client->shop_name }}</span>
        </div>
        @endif
    </div>

    <!-- Montant principal -->
    <div class="amount-section">
        <div style="font-size: 14px; margin-bottom: 8px;">💰 MONTANT RECHARGÉ</div>
        <div class="topup-amount">{{ number_format($topup->amount, 3) }} DT</div>
        <div style="font-size: 12px; color: #6b7280; margin-top: 8px;">
            Méthode: {{ $topup->method === 'CASH' ? '💵 Espèces' : $topup->method }}
        </div>
        <div style="font-size: 11px; color: #059669; margin-top: 5px; font-weight: bold;">
            ✅ Créditée immédiatement sur le wallet client
        </div>
    </div>

    <!-- Détails de la transaction -->
    <div class="section-divider">
        <h4 style="margin: 0 0 10px 0; font-size: 14px;">💳 DÉTAILS TRANSACTION</h4>
        <div class="info-row">
            <span class="info-label">Statut:</span>
            <span class="info-value" style="color: #059669; font-weight: bold;">
                ✅ {{ $topup->status === 'VALIDATED' ? 'VALIDÉE' : $topup->status }}
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Type:</span>
            <span class="info-value">Recharge terrain (Cash)</span>
        </div>
        @if($topup->validation_notes)
        <div style="margin-top: 10px;">
            <div style="font-weight: bold; font-size: 12px; margin-bottom: 5px;">📝 Notes de validation:</div>
            <div style="font-size: 11px; padding: 8px; background: #f3f4f6; border-radius: 4px; border-left: 3px solid #059669;">
                {{ $topup->validation_notes }}
            </div>
        </div>
        @endif
        @if($topup->notes)
        <div style="margin-top: 10px;">
            <div style="font-weight: bold; font-size: 12px; margin-bottom: 5px;">💬 Notes livreur:</div>
            <div style="font-size: 11px; padding: 8px; background: #f9fafb; border-radius: 4px;">
                {{ $topup->notes }}
            </div>
        </div>
        @endif
    </div>

    <!-- Impact sur les wallets -->
    <div class="important-info">
        <div style="font-size: 12px; font-weight: bold; margin-bottom: 8px;">
            💡 IMPACT SUR LES WALLETS
        </div>
        <div style="font-size: 11px; line-height: 1.4;">
            <div style="margin-bottom: 5px;">• <strong>Wallet client :</strong> +{{ number_format($topup->amount, 3) }} DT</div>
            <div style="margin-bottom: 5px;">• <strong>Wallet livreur :</strong> +{{ number_format($topup->amount, 3) }} DT (espèces reçues)</div>
            <div style="color: #6b7280; font-style: italic;">Double ajout selon le principe "wallet = caisse physique"</div>
        </div>
    </div>

    <!-- Code QR pour vérification -->
    <div class="qr-section">
        <div style="font-size: 12px; margin-bottom: 10px; font-weight: bold;">
            🔍 Code QR de vérification
        </div>
        <div style="border: 1px solid #ddd; padding: 20px; background: white; margin: 10px 0;">
            <div id="qrcode" style="margin: 0 auto; width: 120px; height: 120px; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #6b7280; border: 1px dashed #ccc;">
                [QR Code]<br>{{ $topup->request_code }}
            </div>
        </div>
        <div style="font-size: 10px; color: #6b7280; margin-top: 5px;">
            Scannez pour vérifier l'authenticité
        </div>
        <div style="font-size: 9px; margin-top: 3px; word-break: break-all;">
            {{ route('public.verify.topup', $topup->request_code) }}
        </div>
    </div>

    <!-- Section signatures -->
    <div class="signature-section">
        <div style="display: flex; justify-content: space-between; margin-top: 25px;">
            <div style="text-align: center; width: 45%;">
                <div class="signature-line"></div>
                <div style="font-size: 11px; margin-top: 5px; font-weight: bold;">Signature Client</div>
                <div style="font-size: 10px; color: #6b7280; margin-top: 2px;">
                    {{ $topup->client->name }}
                </div>
            </div>
            <div style="text-align: center; width: 45%;">
                <div class="signature-line"></div>
                <div style="font-size: 11px; margin-top: 5px; font-weight: bold;">Signature Livreur</div>
                <div style="font-size: 10px; color: #6b7280; margin-top: 2px;">
                    {{ $topup->processedBy->name ?? 'N/A' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Conditions importantes -->
    <div style="border-top: 1px solid #ddd; padding-top: 15px; margin-top: 20px; font-size: 11px;">
        <h4 style="margin: 0 0 8px 0; font-size: 12px;">ℹ️ CONDITIONS ET GARANTIES</h4>
        <ul style="margin: 0; padding-left: 15px; line-height: 1.3;">
            <li>Cette recharge est immédiatement créditée sur le wallet client</li>
            <li>Le montant apparaît dans le solde disponible en temps réel</li>
            <li>Espèces reçues et comptabilisées dans le wallet du livreur</li>
            <li>Conservez ce reçu comme preuve de transaction</li>
            <li>Vérification possible via le QR code ci-dessus</li>
            <li>Réclamation possible sous 48h avec ce code de recharge</li>
        </ul>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <div style="margin-bottom: 10px; font-weight: bold; color: #059669;">
            ✅ RECHARGE EFFECTUÉE AVEC SUCCÈS
        </div>
        <div style="line-height: 1.3;">
            <strong>MERCI DE VOTRE CONFIANCE</strong><br><br>
            AL-AMENA DELIVERY - Service de livraison professionnel<br>
            📞 Support 24/7: +216 XX XXX XXX<br>
            📧 support@al-amena-delivery.tn<br>
            🌐 www.al-amena-delivery.tn
        </div>
        <div style="margin-top: 15px; font-size: 10px; color: #6b7280; border-top: 1px solid #eee; padding-top: 10px;">
            Reçu généré le {{ now()->format('d/m/Y à H:i:s') }}<br>
            Document officiel - Transaction sécurisée et tracée
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
            // Pattern de QR code simulé
            qrDiv.innerHTML = `
                <div style="font-family: monospace; font-size: 7px; line-height: 1;">
                    ████ █ █ ████<br>
                    █  █ █ █ █  █<br>
                    █ ██ █ █ ██ █<br>
                    ████ █ █ ████<br>
                    █ █ ███ █ █ █<br>
                    █  █ █ █ █  █<br>
                    ████ █ █ ████
                </div>
            `;
        });
    </script>
</body>
</html>