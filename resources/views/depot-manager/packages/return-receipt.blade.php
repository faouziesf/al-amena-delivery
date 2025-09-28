<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de Retour - {{ $package->package_code }}</title>
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

        .receipt {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #e74c3c;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 5px;
        }

        .document-title {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-top: 15px;
        }

        .document-subtitle {
            font-size: 14px;
            color: #7f8c8d;
            margin-top: 5px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .info-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #e74c3c;
        }

        .info-section h3 {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .info-label {
            font-weight: bold;
            color: #495057;
            min-width: 120px;
        }

        .info-value {
            color: #212529;
            flex: 1;
            text-align: right;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-returned {
            background: #fee;
            color: #c53030;
            border: 1px solid #fc8181;
        }

        .status-exchange {
            background: #fff5e6;
            color: #d69e2e;
            border: 1px solid #fbd38d;
        }

        .package-details {
            background: white;
            border: 2px solid #e74c3c;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .package-code {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            color: #e74c3c;
            background: #fee;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            letter-spacing: 2px;
        }

        .return-info {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .return-info h3 {
            color: #c53030;
            font-size: 16px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .reason-box {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 15px;
            margin-top: 10px;
            min-height: 60px;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
            margin-top: 40px;
        }

        .signature-box {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            min-height: 100px;
        }

        .signature-title {
            font-weight: bold;
            color: #495057;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .signature-line {
            border-top: 2px solid #dee2e6;
            margin-top: 60px;
            padding-top: 10px;
            font-size: 11px;
            color: #6c757d;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e9ecef;
            text-align: center;
            font-size: 10px;
            color: #6c757d;
        }

        .barcode {
            text-align: center;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            letter-spacing: 2px;
            background: #f8f9fa;
            padding: 10px;
            border: 1px dashed #dee2e6;
        }

        .urgent-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }

        .urgent-notice strong {
            color: #856404;
            font-size: 14px;
        }

        @media print {
            body {
                background: white;
            }

            .receipt {
                box-shadow: none;
                max-width: none;
                padding: 10px;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="company-name">AL AMENA DELIVERY</div>
            <div class="document-title">BON DE RETOUR</div>
            <div class="document-subtitle">Traitement des Colis Retournés</div>
        </div>

        <!-- Notice urgente si nécessaire -->
        @if($package->status === 'EXCHANGE_REQUESTED')
        <div class="urgent-notice">
            <strong>⚠️ ÉCHANGE DEMANDÉ - Traitement Prioritaire Requis</strong>
        </div>
        @endif

        <!-- Code du colis -->
        <div class="package-details">
            <div class="package-code">{{ $package->package_code }}</div>

            <div class="barcode">
                ||| {{ implode(' | ', str_split($package->package_code, 1)) }} |||
            </div>
        </div>

        <!-- Informations principales -->
        <div class="info-grid">
            <!-- Informations Colis -->
            <div class="info-section">
                <h3>📦 Informations Colis</h3>
                <div class="info-row">
                    <span class="info-label">Code:</span>
                    <span class="info-value">{{ $package->package_code }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Statut:</span>
                    <span class="info-value">
                        <span class="status-badge {{ $package->status === 'RETURNED' ? 'status-returned' : 'status-exchange' }}">
                            @if($package->status === 'RETURNED') RETOUR
                            @elseif($package->status === 'EXCHANGE_REQUESTED') ÉCHANGE DEMANDÉ
                            @elseif($package->status === 'EXCHANGE_PROCESSED') ÉCHANGE TRAITÉ
                            @else {{ $package->status }}
                            @endif
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date Création:</span>
                    <span class="info-value">{{ $package->created_at->format('d/m/Y à H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date Retour:</span>
                    <span class="info-value">{{ $package->returned_at ? $package->returned_at->format('d/m/Y à H:i') : 'N/A' }}</span>
                </div>
                @if($package->cod_amount > 0)
                <div class="info-row">
                    <span class="info-label">Montant COD:</span>
                    <span class="info-value"><strong>{{ number_format($package->cod_amount, 3) }} DT</strong></span>
                </div>
                @endif
            </div>

            <!-- Informations Destinataire -->
            <div class="info-section">
                <h3>👤 Destinataire</h3>
                <div class="info-row">
                    <span class="info-label">Nom:</span>
                    <span class="info-value">{{ $package->recipient_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Téléphone:</span>
                    <span class="info-value">{{ $package->recipient_phone }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Adresse:</span>
                    <span class="info-value">{{ $package->recipient_address }}</span>
                </div>
                @if($package->delegationTo)
                <div class="info-row">
                    <span class="info-label">Délégation:</span>
                    <span class="info-value">{{ $package->delegationTo->name }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Informations Expéditeur et Livreur -->
        <div class="info-grid">
            @if($package->sender)
            <div class="info-section">
                <h3>📤 Expéditeur</h3>
                <div class="info-row">
                    <span class="info-label">Nom:</span>
                    <span class="info-value">{{ $package->sender->first_name }} {{ $package->sender->last_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Téléphone:</span>
                    <span class="info-value">{{ $package->sender->phone ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $package->sender->email }}</span>
                </div>
            </div>
            @endif

            @if($package->assignedDeliverer)
            <div class="info-section">
                <h3>🚚 Livreur</h3>
                <div class="info-row">
                    <span class="info-label">Nom:</span>
                    <span class="info-value">{{ $package->assignedDeliverer->first_name }} {{ $package->assignedDeliverer->last_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Téléphone:</span>
                    <span class="info-value">{{ $package->assignedDeliverer->phone ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Gouvernorat:</span>
                    <span class="info-value">{{ $package->assignedDeliverer->assigned_delegation ?? 'N/A' }}</span>
                </div>
            </div>
            @endif
        </div>

        <!-- Informations sur le retour -->
        <div class="return-info">
            <h3>📋 Détails du {{ $package->status === 'RETURNED' ? 'Retour' : 'Échange' }}</h3>

            @if($package->return_reason)
            <div class="info-row">
                <span class="info-label">Raison:</span>
                <div class="reason-box">{{ $package->return_reason }}</div>
            </div>
            @endif

            @if($package->exchange_notes)
            <div class="info-row">
                <span class="info-label">Notes Échange:</span>
                <div class="reason-box">{{ $package->exchange_notes }}</div>
            </div>
            @endif

            @if($package->new_package_code)
            <div class="info-row">
                <span class="info-label">Nouveau Code:</span>
                <span class="info-value"><strong>{{ $package->new_package_code }}</strong></span>
            </div>
            @endif

            <div class="info-row">
                <span class="info-label">Traité par:</span>
                <span class="info-value">{{ $user->first_name }} {{ $user->last_name }} (Chef Dépôt)</span>
            </div>

            <div class="info-row">
                <span class="info-label">Date Traitement:</span>
                <span class="info-value">{{ now()->format('d/m/Y à H:i') }}</span>
            </div>
        </div>

        <!-- Section Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-title">Chef Dépôt</div>
                <div class="signature-line">
                    Signature et Cachet
                </div>
            </div>

            <div class="signature-box">
                <div class="signature-title">Agent de Transport</div>
                <div class="signature-line">
                    Signature et Date
                </div>
            </div>

            <div class="signature-box">
                <div class="signature-title">Réception Client/Expéditeur</div>
                <div class="signature-line">
                    Signature et Date
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>AL AMENA DELIVERY</strong> - Service de Livraison Professionnel</p>
            <p>📞 Contact Support: +216 XX XXX XXX | ✉️ Email: support@al-amena-delivery.tn</p>
            <p>Document généré le {{ now()->format('d/m/Y à H:i:s') }} par le système AL AMENA</p>
            <p style="margin-top: 10px; font-style: italic;">
                Ce document certifie le {{ $package->status === 'RETURNED' ? 'retour' : 'traitement d\'échange' }} du colis {{ $package->package_code }}
            </p>
        </div>
    </div>

    <!-- Boutons d'action (masqués à l'impression) -->
    <div class="no-print" style="text-align: center; margin: 20px 0; padding: 20px;">
        <button onclick="window.print()"
                style="background: #e74c3c; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-size: 16px; cursor: pointer; margin: 0 10px;">
            🖨️ Imprimer
        </button>
        <button onclick="window.close()"
                style="background: #6c757d; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-size: 16px; cursor: pointer; margin: 0 10px;">
            ❌ Fermer
        </button>
        <button onclick="window.history.back()"
                style="background: #17a2b8; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-size: 16px; cursor: pointer; margin: 0 10px;">
            ↩️ Retour
        </button>
    </div>

    <script>
        // Auto-print après le chargement
        window.addEventListener('load', function() {
            setTimeout(function() {
                if (confirm('Voulez-vous imprimer automatiquement ce bon de retour ?')) {
                    window.print();
                }
            }, 1000);
        });

        // Améliorer l'affichage pour l'impression
        window.addEventListener('beforeprint', function() {
            document.body.style.backgroundColor = 'white';
        });

        window.addEventListener('afterprint', function() {
            document.body.style.backgroundColor = '';
        });
    </script>
</body>
</html>