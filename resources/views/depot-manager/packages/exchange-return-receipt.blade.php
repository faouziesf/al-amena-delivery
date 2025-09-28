<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de Retour Échange - {{ $package->package_code }}</title>
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
            border-bottom: 3px solid #ff9800;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #ff9800;
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

        .exchange-notice {
            background: #fff3e0;
            border: 2px solid #ffcc02;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }

        .exchange-notice strong {
            color: #ef6c00;
            font-size: 16px;
        }

        .exchange-notice p {
            color: #e65100;
            margin-top: 8px;
            font-weight: 500;
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
            border-left: 4px solid #ff9800;
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

        .status-exchange {
            background: #fff3e0;
            color: #ef6c00;
            border: 1px solid #ffcc02;
        }

        .package-details {
            background: white;
            border: 2px solid #ff9800;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .package-code {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            color: #ff9800;
            background: #fff3e0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            letter-spacing: 2px;
        }

        .exchange-details {
            background: #fff3e0;
            border: 1px solid #ffcc02;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .exchange-details h3 {
            color: #ef6c00;
            font-size: 16px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .exchange-box {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 15px;
            margin-top: 10px;
        }

        .pickup-info {
            background: #e8f5e8;
            border: 1px solid #c8e6c9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .pickup-info h3 {
            color: #2e7d32;
            font-size: 16px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .pickup-box {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 15px;
            margin-top: 10px;
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

        .process-flow {
            background: #f0f4f8;
            border: 1px solid #cbd5e0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .process-flow h3 {
            color: #2d3748;
            font-size: 16px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .process-steps {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .process-step {
            background: white;
            border-radius: 6px;
            padding: 15px;
            text-align: center;
            border: 2px solid #e2e8f0;
        }

        .process-step.active {
            border-color: #ff9800;
            background: #fff3e0;
        }

        .process-step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #ff9800;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px auto;
            font-weight: bold;
        }

        .process-step-title {
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .process-step-desc {
            font-size: 11px;
            color: #718096;
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
            <div class="document-title">BON DE RETOUR ÉCHANGE</div>
            <div class="document-subtitle">Partie à Retourner au Fournisseur</div>
        </div>

        <!-- Notice d'échange -->
        <div class="exchange-notice">
            <strong>🔄 ÉCHANGE EN COURS - RETOUR FOURNISSEUR REQUIS</strong>
            <p>Ce colis fait partie d'une demande d'échange et doit être retourné au fournisseur d'origine</p>
        </div>

        <!-- Code du colis -->
        <div class="package-details">
            <div class="package-code">{{ $package->package_code }}</div>

            <div class="barcode">
                ||| {{ implode(' | ', str_split($package->package_code, 1)) }} |||
            </div>
        </div>

        <!-- Flux de traitement -->
        <div class="process-flow">
            <h3>🔄 Processus d'Échange</h3>
            <div class="process-steps">
                <div class="process-step active">
                    <div class="process-step-number">1</div>
                    <div class="process-step-title">Retour Fournisseur</div>
                    <div class="process-step-desc">Colis à retourner</div>
                </div>
                <div class="process-step">
                    <div class="process-step-number">2</div>
                    <div class="process-step-title">Nouveau Produit</div>
                    <div class="process-step-desc">Expédition échange</div>
                </div>
                <div class="process-step">
                    <div class="process-step-number">3</div>
                    <div class="process-step-title">Livraison Finale</div>
                    <div class="process-step-desc">Remise au client</div>
                </div>
            </div>
        </div>

        <!-- Informations principales -->
        <div class="info-grid">
            <!-- Informations Colis -->
            <div class="info-section">
                <h3>📦 Informations Colis d'Échange</h3>
                <div class="info-row">
                    <span class="info-label">Code:</span>
                    <span class="info-value">{{ $package->package_code }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Statut:</span>
                    <span class="info-value">
                        <span class="status-badge status-exchange">
                            @if($package->status === 'EXCHANGE_REQUESTED') ÉCHANGE DEMANDÉ
                            @elseif($package->status === 'EXCHANGE_PROCESSED') ÉCHANGE EN COURS
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

                @if($package->content_description)
                <div class="info-row">
                    <span class="info-label">Contenu:</span>
                    <span class="info-value">{{ $package->content_description }}</span>
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

                @if($package->package_dimensions)
                @php $dims = json_decode($package->package_dimensions, true); @endphp
                @if($dims && isset($dims['length'], $dims['width'], $dims['height']))
                <div class="info-row">
                    <span class="info-label">Dimensions:</span>
                    <span class="info-value">{{ $dims['length'] }}×{{ $dims['width'] }}×{{ $dims['height'] }} cm</span>
                </div>
                @endif
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

                @if($package->notes || $package->special_instructions)
                <div class="info-row">
                    <span class="info-label">Instructions:</span>
                    <span class="info-value">{{ $package->notes ?? $package->special_instructions }}</span>
                </div>
                @endif
            </div>

            <!-- Informations Client -->
            <div class="info-section">
                <h3>👤 Client Demandeur d'Échange</h3>
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

        <!-- Informations Expéditeur et Pickup -->
        <div class="info-grid">
            @if($package->sender)
            <div class="info-section">
                <h3>📤 Expéditeur/Fournisseur</h3>
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
                <h3>🚚 Livreur Assigné</h3>
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

        <!-- Adresse de Pickup pour Retour -->
        <div class="pickup-info">
            <h3>📍 ADRESSE DE PICKUP POUR RETOUR FOURNISSEUR</h3>

            <div class="pickup-box">
                <strong>Adresse de Collecte:</strong><br>
                @if($package->pickupAddress)
                    {{ $package->pickupAddress->address }}
                @else
                    {{ $package->pickup_address ?? 'Adresse de pickup non renseignée' }}
                @endif
            </div>

            @if($package->pickup_phone || ($package->pickupAddress && $package->pickupAddress->phone))
            <div class="pickup-box">
                <strong>Téléphone Contact Pickup:</strong><br>
                {{ $package->pickup_phone ?? $package->pickupAddress->phone ?? 'N/A' }}
            </div>
            @endif

            @if($package->pickup_notes)
            <div class="pickup-box">
                <strong>Instructions Pickup:</strong><br>
                {{ $package->pickup_notes }}
            </div>
            @endif

            @if($package->pickupDelegation)
            <div class="pickup-box">
                <strong>Délégation Pickup:</strong><br>
                {{ $package->pickupDelegation->name }}
            </div>
            @endif

            <div class="pickup-box" style="background: #fff3e0; border-color: #ffcc02;">
                <strong>⚠️ ATTENTION:</strong> Ce colis doit être collecté et retourné au fournisseur dans le cadre d'un échange.
                Assurez-vous que le nouveau produit d'échange est prêt à être expédié au client.
            </div>
        </div>

        <!-- Détails de l'échange -->
        <div class="exchange-details">
            <h3>🔄 Détails de l'Échange</h3>

            @if($package->exchange_notes)
            <div class="info-row">
                <span class="info-label">Notes Échange:</span>
            </div>
            <div class="exchange-box">{{ $package->exchange_notes }}</div>
            @endif

            @if($package->new_package_code)
            <div class="info-row">
                <span class="info-label">Nouveau Code Colis:</span>
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

            @if($package->exchange_processed_at)
            <div class="info-row">
                <span class="info-label">Échange Traité le:</span>
                <span class="info-value">{{ $package->exchange_processed_at->format('d/m/Y à H:i') }}</span>
            </div>
            @endif
        </div>

        <!-- Section Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-title">Chef Dépôt</div>
                <div class="signature-line">
                    {{ $user->first_name }} {{ $user->last_name }}<br>
                    Signature et Cachet
                </div>
            </div>

            <div class="signature-box">
                <div class="signature-title">Agent de Transport</div>
                <div class="signature-line">
                    Signature et Date<br>
                    Retour Fournisseur
                </div>
            </div>

            <div class="signature-box">
                <div class="signature-title">Réception Fournisseur</div>
                <div class="signature-line">
                    Signature et Cachet<br>
                    Accusé de Réception
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>AL AMENA DELIVERY</strong> - Bon de Retour pour Échange</p>
            <p>📞 Contact Support: +216 XX XXX XXX | ✉️ Email: support@al-amena-delivery.tn</p>
            <p>Document généré le {{ now()->format('d/m/Y à H:i:s') }} par le système AL AMENA</p>
            <p style="margin-top: 10px; font-style: italic; color: #ef6c00;">
                <strong>⚠️ IMPORTANT:</strong> Ce document certifie le retour fournisseur du colis {{ $package->package_code }} dans le cadre d'un échange
            </p>
        </div>
    </div>

    <!-- Boutons d'action (masqués à l'impression) -->
    <div class="no-print" style="text-align: center; margin: 20px 0; padding: 20px;">
        <button onclick="window.print()"
                style="background: #ff9800; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-size: 16px; cursor: pointer; margin: 0 10px;">
            🖨️ Imprimer Bon Échange
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
                if (confirm('Voulez-vous imprimer automatiquement ce bon de retour échange ?')) {
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