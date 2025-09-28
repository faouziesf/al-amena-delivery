<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bons de Retour Groupés - {{ $packages->count() }} Colis</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #333;
            background: white;
        }

        .receipt-container {
            width: 100%;
        }

        .receipt {
            width: 100%;
            max-width: 800px;
            margin: 0 auto 40px auto;
            padding: 15px;
            background: white;
            border: 2px solid #e74c3c;
            page-break-after: always;
        }

        .receipt:last-child {
            page-break-after: avoid;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #e74c3c;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 5px;
        }

        .document-title {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-top: 10px;
        }

        .batch-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .batch-info strong {
            color: #856404;
            font-size: 12px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border-left: 3px solid #e74c3c;
        }

        .info-section h3 {
            font-size: 13px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .info-label {
            font-weight: bold;
            color: #495057;
            min-width: 100px;
        }

        .info-value {
            color: #212529;
            flex: 1;
            text-align: right;
        }

        .package-code {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            color: #e74c3c;
            background: #fee;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            letter-spacing: 1px;
        }

        .pickup-address {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .pickup-address h4 {
            color: #c53030;
            font-size: 13px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .address-box {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px;
            margin-top: 8px;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 30px;
        }

        .signature-box {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            text-align: center;
            min-height: 80px;
        }

        .signature-title {
            font-weight: bold;
            color: #495057;
            margin-bottom: 10px;
            font-size: 11px;
        }

        .signature-line {
            border-top: 1px solid #dee2e6;
            margin-top: 40px;
            padding-top: 8px;
            font-size: 9px;
            color: #6c757d;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            font-size: 9px;
            color: #6c757d;
        }

        .barcode {
            text-align: center;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            letter-spacing: 1px;
            background: #f8f9fa;
            padding: 8px;
            border: 1px dashed #dee2e6;
        }

        .batch-summary {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 30px;
            text-align: center;
        }

        .batch-summary h2 {
            color: #1565c0;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .batch-summary .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 15px;
        }

        .batch-summary .stat {
            background: white;
            border-radius: 4px;
            padding: 10px;
        }

        .batch-summary .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: #1565c0;
        }

        .batch-summary .stat-label {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }

        @media print {
            body {
                background: white;
            }

            .receipt {
                box-shadow: none;
                max-width: none;
                margin-bottom: 0;
                border: 1px solid #ccc;
            }

            .no-print {
                display: none !important;
            }

            .receipt {
                page-break-inside: avoid;
            }
        }

        @page {
            margin: 1cm;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Page de résumé du lot -->
        <div class="receipt">
            <div class="header">
                <div class="company-name">AL AMENA DELIVERY</div>
                <div class="document-title">BORDEREAU DE RETOUR GROUPÉ</div>
            </div>

            <div class="batch-summary">
                <h2>Résumé du Lot de Retour</h2>
                <p><strong>Date de traitement:</strong> {{ now()->format('d/m/Y à H:i') }}</p>
                <p><strong>Traité par:</strong> {{ $user->first_name }} {{ $user->last_name }} (Chef Dépôt)</p>

                <div class="stats">
                    <div class="stat">
                        <div class="stat-number">{{ $packages->count() }}</div>
                        <div class="stat-label">Total Colis</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number">{{ number_format($packages->sum('cod_amount'), 3) }}</div>
                        <div class="stat-label">COD Total (DT)</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number">{{ $packages->groupBy('sender_id')->count() }}</div>
                        <div class="stat-label">Expéditeurs</div>
                    </div>
                </div>
            </div>

            <!-- Liste récapitulative -->
            <div style="margin-bottom: 30px;">
                <h3 style="margin-bottom: 15px; color: #2c3e50;">Liste des Colis à Retourner</h3>
                <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="border: 1px solid #dee2e6; padding: 8px; text-align: left;">Code Colis</th>
                            <th style="border: 1px solid #dee2e6; padding: 8px; text-align: left;">Expéditeur</th>
                            <th style="border: 1px solid #dee2e6; padding: 8px; text-align: left;">Adresse Pickup</th>
                            <th style="border: 1px solid #dee2e6; padding: 8px; text-align: right;">COD (DT)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packages as $package)
                        <tr>
                            <td style="border: 1px solid #dee2e6; padding: 6px; font-weight: bold;">{{ $package->package_code }}</td>
                            <td style="border: 1px solid #dee2e6; padding: 6px;">
                                @if($package->sender)
                                    {{ $package->sender->first_name }} {{ $package->sender->last_name }}
                                @else
                                    Expéditeur non trouvé
                                @endif
                            </td>
                            <td style="border: 1px solid #dee2e6; padding: 6px; font-size: 9px;">
                                {{ Str::limit($package->pickup_address ?? 'N/A', 40) }}
                            </td>
                            <td style="border: 1px solid #dee2e6; padding: 6px; text-align: right;">
                                {{ number_format($package->cod_amount, 3) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Signatures pour le bordereau -->
            <div class="signatures">
                <div class="signature-box">
                    <div class="signature-title">Chef Dépôt</div>
                    <div class="signature-line">Signature et Cachet</div>
                </div>
                <div class="signature-box">
                    <div class="signature-title">Responsable Transport</div>
                    <div class="signature-line">Signature et Date</div>
                </div>
                <div class="signature-box">
                    <div class="signature-title">Réception Fournisseur</div>
                    <div class="signature-line">Signature et Cachet</div>
                </div>
            </div>

            <div class="footer">
                <p><strong>AL AMENA DELIVERY</strong> - Bordereau de Retour Groupé</p>
                <p>Document généré le {{ now()->format('d/m/Y à H:i:s') }}</p>
            </div>
        </div>

        <!-- Pages individuelles pour chaque colis -->
        @foreach($packages as $package)
        <div class="receipt">
            <div class="header">
                <div class="company-name">AL AMENA DELIVERY</div>
                <div class="document-title">BON DE RETOUR INDIVIDUEL</div>
            </div>

            <div class="batch-info">
                <strong>⚠️ RETOUR FOURNISSEUR - LOT {{ now()->format('Ymd-Hi') }}</strong>
            </div>

            <!-- Code du colis -->
            <div class="package-code">{{ $package->package_code }}</div>

            <div class="barcode">
                ||| {{ implode(' | ', str_split($package->package_code, 1)) }} |||
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
                        <span class="info-value">RETOUR FOURNISSEUR</span>
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
                    @if($package->content_description)
                    <div class="info-row">
                        <span class="info-label">Contenu:</span>
                        <span class="info-value">{{ $package->content_description }}</span>
                    </div>
                    @endif

                    @if($package->est_echange)
                    <div class="info-row">
                        <span class="info-label">Type:</span>
                        <span class="info-value" style="color: #f59e0b; font-weight: bold;">🔄 COLIS D'ÉCHANGE</span>
                    </div>
                    @endif

                    @if($package->package_weight)
                    <div class="info-row">
                        <span class="info-label">Poids:</span>
                        <span class="info-value">{{ number_format($package->package_weight, 3) }} kg</span>
                    </div>
                    @endif

                    @if($package->package_value)
                    <div class="info-row">
                        <span class="info-label">Valeur déclarée:</span>
                        <span class="info-value">{{ number_format($package->package_value, 3) }} DT</span>
                    </div>
                    @endif

                    <!-- Options spéciales -->
                    @if($package->is_fragile || $package->requires_signature || $package->allow_opening)
                    <div class="info-row">
                        <span class="info-label">Options:</span>
                        <span class="info-value">
                            @if($package->is_fragile) <span style="color: #dc2626; font-weight: bold;">🔴 FRAGILE</span> @endif
                            @if($package->requires_signature) <span style="color: #2563eb; font-weight: bold;">✍️ SIGNATURE</span> @endif
                            @if($package->allow_opening) <span style="color: #059669; font-weight: bold;">📦 OUVERTURE OK</span> @endif
                        </span>
                    </div>
                    @endif

                    @if($package->payment_method)
                    <div class="info-row">
                        <span class="info-label">Mode paiement:</span>
                        <span class="info-value">
                            @switch($package->payment_method)
                                @case('cash_only') Espèces uniquement @break
                                @case('check_only') Chèque uniquement @break
                                @case('cash_and_check') Espèces et chèques @break
                                @default {{ $package->payment_method }} @break
                            @endswitch
                        </span>
                    </div>
                    @endif

                    @if($package->delivery_fee || $package->return_fee)
                    <div class="info-row">
                        <span class="info-label">Frais:</span>
                        <span class="info-value">
                            @if($package->delivery_fee) Livraison: {{ number_format($package->delivery_fee, 3) }} DT @endif
                            @if($package->return_fee) | Retour: {{ number_format($package->return_fee, 3) }} DT @endif
                        </span>
                    </div>
                    @endif
                </div>

                <!-- Informations Destinataire Original -->
                <div class="info-section">
                    <h3>👤 Destinataire (Non Livré)</h3>
                    <div class="info-row">
                        <span class="info-label">Nom:</span>
                        <span class="info-value">{{ $package->recipient_name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Téléphone:</span>
                        <span class="info-value">{{ $package->recipient_phone ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Adresse:</span>
                        <span class="info-value">{{ $package->recipient_address ?? 'N/A' }}</span>
                    </div>
                    @if($package->delegationTo)
                    <div class="info-row">
                        <span class="info-label">Délégation:</span>
                        <span class="info-value">{{ $package->delegationTo->name }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informations Expéditeur -->
            @if($package->sender)
            <div class="info-grid">
                <div class="info-section">
                    <h3>📤 Expéditeur (Retour à)</h3>
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
                        <span class="info-value">{{ $package->sender->email ?? 'N/A' }}</span>
                    </div>
                </div>

                <div class="info-section">
                    <h3>🚚 Informations Livraison</h3>
                    @if($package->assignedDeliverer)
                    <div class="info-row">
                        <span class="info-label">Livreur:</span>
                        <span class="info-value">{{ $package->assignedDeliverer->first_name }} {{ $package->assignedDeliverer->last_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Gouvernorat:</span>
                        <span class="info-value">{{ $package->assignedDeliverer->assigned_delegation ?? 'N/A' }}</span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">Tentatives:</span>
                        <span class="info-value">{{ $package->delivery_attempts ?? 0 }}</span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Adresse de Pickup Importante -->
            <div class="pickup-address">
                <h4>📍 ADRESSE DE PICKUP POUR RETOUR</h4>
                <div class="address-box">
                    <strong>Adresse:</strong>
                    @if($package->pickupAddress)
                        {{ $package->pickupAddress->address }}
                    @else
                        {{ $package->pickup_address ?? 'Adresse de pickup non renseignée' }}
                    @endif
                </div>
                @if($package->pickup_phone || ($package->pickupAddress && $package->pickupAddress->phone))
                <div class="address-box" style="margin-top: 8px;">
                    <strong>Téléphone Contact:</strong>
                    {{ $package->pickup_phone ?? $package->pickupAddress->phone ?? 'N/A' }}
                </div>
                @endif
                @if($package->pickup_notes)
                <div class="address-box" style="margin-top: 8px;">
                    <strong>Notes Pickup:</strong> {{ $package->pickup_notes }}
                </div>
                @endif
                @if($package->pickupDelegation)
                <div class="address-box" style="margin-top: 8px;">
                    <strong>Délégation Pickup:</strong> {{ $package->pickupDelegation->name }}
                </div>
                @endif
            </div>

            <!-- Raison du retour -->
            @if($package->return_reason)
            <div style="background: #fff5f5; border: 1px solid #fed7d7; border-radius: 6px; padding: 15px; margin-bottom: 20px;">
                <h4 style="color: #c53030; font-size: 13px; margin-bottom: 10px; font-weight: bold;">📋 Raison du Retour</h4>
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px;">
                    {{ $package->return_reason }}
                </div>
            </div>
            @endif

            <!-- Section Signatures -->
            <div class="signatures">
                <div class="signature-box">
                    <div class="signature-title">Chef Dépôt</div>
                    <div class="signature-line">{{ $user->first_name }} {{ $user->last_name }}</div>
                </div>

                <div class="signature-box">
                    <div class="signature-title">Agent Transport</div>
                    <div class="signature-line">Signature et Date</div>
                </div>

                <div class="signature-box">
                    <div class="signature-title">Réception Expéditeur</div>
                    <div class="signature-line">Signature et Cachet</div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p><strong>AL AMENA DELIVERY</strong> - Bon de Retour Fournisseur</p>
                <p>📞 Contact Support: +216 XX XXX XXX | ✉️ Email: support@al-amena-delivery.tn</p>
                <p>Document généré le {{ now()->format('d/m/Y à H:i:s') }} - Colis {{ $package->package_code }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Boutons d'action (masqués à l'impression) -->
    <div class="no-print" style="text-align: center; margin: 20px 0; padding: 20px;">
        <button onclick="window.print()"
                style="background: #e74c3c; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-size: 16px; cursor: pointer; margin: 0 10px;">
            🖨️ Imprimer Tout ({{ $packages->count() }} bons)
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
                if (confirm('Voulez-vous imprimer automatiquement tous les bons de retour ({{ $packages->count() }} pages) ?')) {
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