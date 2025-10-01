<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manifeste {{ $manifest_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.2;
            margin: 0;
            padding: 15px;
            color: #333;
            background: white;
        }

        .no-print {
            display: block;
        }

        .header {
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .company-info {
            text-align: center;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 3px;
        }

        .company-tagline {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .manifest-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            background-color: #4F46E5;
            color: white;
            padding: 6px;
            border-radius: 3px;
            margin: 10px 0;
        }

        .manifest-number {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
            background-color: #F3F4F6;
            padding: 6px;
            border-radius: 3px;
        }

        .print-controls {
            text-align: center;
            margin-bottom: 30px;
            padding: 15px;
            background: linear-gradient(135deg, #F8F9FA 0%, #E9ECEF 100%);
            border-radius: 12px;
            border: 1px solid #E9ECEF;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .print-controls-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }

        .print-button {
            background: linear-gradient(135deg, #4F46E5 0%, #3730A3 100%);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 4px rgba(79, 70, 229, 0.3);
            min-width: 160px;
            justify-content: center;
        }

        .print-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
        }

        .back-button {
            background: linear-gradient(135deg, #6B7280 0%, #4B5563 100%);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(107, 114, 128, 0.3);
            min-width: 160px;
            justify-content: center;
        }

        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(107, 114, 128, 0.4);
        }

        .pdf-button {
            background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);
            box-shadow: 0 2px 4px rgba(220, 38, 38, 0.3);
        }

        .pdf-button:hover {
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
        }

        /* Mobile responsive controls */
        @media (max-width: 640px) {
            .print-controls {
                padding: 12px;
                margin-bottom: 20px;
            }

            .print-controls-grid {
                flex-direction: column;
                gap: 8px;
            }

            .print-button,
            .back-button {
                width: 100%;
                max-width: 280px;
                padding: 14px 20px;
                font-size: 16px;
                min-width: unset;
            }
        }

        .info-section {
            margin-bottom: 15px;
        }

        .info-title {
            font-size: 12px;
            font-weight: bold;
            background-color: #E5E7EB;
            padding: 4px 6px;
            border-left: 3px solid #4F46E5;
            margin-bottom: 6px;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 2px 8px 2px 0;
            width: 30%;
            vertical-align: top;
        }

        .info-value {
            display: table-cell;
            padding: 2px 0;
            vertical-align: top;
        }

        .packages-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 9px;
        }

        .packages-table th {
            background-color: #4F46E5;
            color: white;
            padding: 4px 3px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
            font-size: 9px;
        }

        .packages-table td {
            padding: 3px 2px;
            border: 1px solid #ddd;
            vertical-align: top;
            font-size: 9px;
        }

        .packages-table tr:nth-child(even) {
            background-color: #F9FAFB;
        }

        .summary-box {
            background-color: #F3F4F6;
            padding: 8px;
            border-radius: 3px;
            margin-bottom: 12px;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-item {
            display: table-cell;
            text-align: center;
            padding: 6px;
            border-right: 1px solid #D1D5DB;
            width: 25%;
        }

        .summary-item:last-child {
            border-right: none;
        }

        .summary-value {
            font-size: 13px;
            font-weight: bold;
            color: #4F46E5;
            display: block;
        }

        .summary-label {
            font-size: 9px;
            color: #666;
            margin-top: 2px;
        }

        .signature-section {
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .signature-grid {
            display: table;
            width: 100%;
        }

        .signature-box {
            display: table-cell;
            width: 50%;
            padding: 12px;
            border: 1px solid #E5E7EB;
            border-radius: 3px;
            text-align: center;
            vertical-align: top;
        }

        .signature-box + .signature-box {
            margin-left: 10px;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 8px;
            color: #4F46E5;
            font-size: 10px;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            height: 40px;
            margin: 10px 0 6px 0;
        }

        .signature-info {
            font-size: 8px;
            color: #666;
            line-height: 1.2;
        }

        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #E5E7EB;
            padding-top: 8px;
        }

        .notes-section {
            margin-top: 12px;
            padding: 8px;
            background-color: #FFFBEB;
            border-left: 3px solid #F59E0B;
            border-radius: 0 3px 3px 0;
        }

        .notes-title {
            font-weight: bold;
            color: #92400E;
            margin-bottom: 4px;
            font-size: 10px;
        }

        .tracking-number {
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }

        .cod-amount {
            color: #059669;
            font-weight: bold;
        }

        /* Styles d'impression */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
                padding: 10px;
                font-size: 10px;
            }

            .signature-section {
                page-break-inside: avoid;
            }

            .packages-table {
                font-size: 8px;
            }

            .packages-table th,
            .packages-table td {
                padding: 2px 1px;
            }

            .header {
                margin-bottom: 10px;
            }

            .info-section {
                margin-bottom: 8px;
            }

            .summary-box {
                margin-bottom: 8px;
                padding: 6px;
            }
        }

        @media screen {
            body {
                max-width: 210mm;
                margin: 0 auto;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                background: white;
            }
        }
    </style>
</head>
<body>
    <!-- Contrôles d'impression (masqués à l'impression) -->
    <div class="print-controls no-print">
        <div class="print-controls-grid">
            <button onclick="window.print()" class="print-button">
                <span>🖨️</span>
                <span>Imprimer le Manifeste</span>
            </button>
            <a href="{{ route('client.manifests.show', $manifest->id) }}" class="back-button">
                <span>←</span>
                <span>Retour au Manifeste</span>
            </a>
            <a href="{{ route('client.manifests.download-pdf', $manifest->id) }}" class="print-button pdf-button">
                <span>📄</span>
                <span>Télécharger PDF</span>
            </a>
        </div>
    </div>

    <!-- En-tête -->
    <div class="header">
        <div class="company-info">
            <div class="company-name">AL-AMENA DELIVERY</div>
            <div class="company-tagline">Service de Livraison Professionnel</div>
        </div>

        <div class="manifest-title">MANIFESTE DE COLLECTE</div>

        <div class="manifest-number">
            Numéro de Manifeste : {{ $manifest_number }}
        </div>
    </div>

    <!-- Informations générales -->
    <div class="info-section">
        <div class="info-title">INFORMATIONS GÉNÉRALES</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Date d'émission :</div>
                <div class="info-value">{{ $generated_at->format('d/m/Y à H:i') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Client :</div>
                <div class="info-value">{{ $client->name }} ({{ $client->email }})</div>
            </div>
            <div class="info-row">
                <div class="info-label">Téléphone client :</div>
                <div class="info-value">{{ $client->phone ?? 'Non renseigné' }}</div>
            </div>
            @if($pickup_info['date'])
            <div class="info-row">
                <div class="info-label">Date de collecte prévue :</div>
                <div class="info-value">{{ $pickup_info['date']->format('d/m/Y') }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Informations de collecte -->
    <div class="info-section">
        <div class="info-title">INFORMATIONS DE COLLECTE</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Adresse de collecte :</div>
                <div class="info-value">{{ $pickup_info['address'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Téléphone :</div>
                <div class="info-value">{{ $pickup_info['phone'] ?? 'Non renseigné' }}</div>
            </div>
        </div>
    </div>

    <!-- Résumé -->
    <div class="summary-box">
        <div class="summary-grid">
            <div class="summary-item">
                <span class="summary-value">{{ $total_packages }}</span>
                <div class="summary-label">Colis Total</div>
            </div>
            <div class="summary-item">
                <span class="summary-value">{{ number_format($total_weight, 1) }} kg</span>
                <div class="summary-label">Poids Total</div>
            </div>
            <div class="summary-item">
                <span class="summary-value">{{ number_format($packages->sum('package_value'), 2) }} DT</span>
                <div class="summary-label">Valeur Déclarée</div>
            </div>
            <div class="summary-item">
                <span class="summary-value">{{ number_format($total_cod, 3) }} DT</span>
                <div class="summary-label">COD Total</div>
            </div>
        </div>
    </div>

    <!-- Liste des colis -->
    <div class="info-section">
        <div class="info-title">DÉTAIL DES COLIS À COLLECTER</div>
        <table class="packages-table">
            <thead>
                <tr>
                    <th style="width: 15%;">N° Suivi</th>
                    <th style="width: 20%;">Destinataire</th>
                    <th style="width: 25%;">Adresse de Livraison</th>
                    <th style="width: 10%;">Poids</th>
                    <th style="width: 10%;">Valeur</th>
                    <th style="width: 10%;">COD</th>
                    <th style="width: 10%;">Collecté ✓</th>
                </tr>
            </thead>
            <tbody>
                @foreach($packages as $index => $package)
                <tr>
                    <td class="tracking-number">{{ $package->package_code }}</td>
                    <td>
                        <strong>{{ $package->recipient_data['name'] ?? 'N/A' }}</strong><br>
                        <small>{{ $package->recipient_data['phone'] ?? 'N/A' }}</small>
                    </td>
                    <td>{{ Str::limit($package->recipient_data['address'] ?? 'N/A', 60) }}</td>
                    <td>{{ $package->package_weight ? number_format($package->package_weight, 1) . ' kg' : '-' }}</td>
                    <td>{{ $package->package_value ? number_format($package->package_value, 2) . ' DT' : '-' }}</td>
                    <td>
                        @if($package->cod_amount > 0)
                            <span class="cod-amount">{{ number_format($package->cod_amount, 3) }} DT</span>
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align: center; border: 2px solid #333; height: 30px;"></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($notes)
    <!-- Notes -->
    <div class="notes-section">
        <div class="notes-title">NOTES ET INSTRUCTIONS SPÉCIALES</div>
        <div>{{ $notes }}</div>
    </div>
    @endif

    <!-- Section signatures -->
    <div class="signature-section">
        <div class="info-title">SIGNATURES ET VALIDATION</div>

        <div class="signature-grid">
            <div class="signature-box">
                <div class="signature-title">CLIENT EXPÉDITEUR</div>
                <div class="signature-line"></div>
                <div class="signature-info">
                    Nom : {{ $client->name }}<br>
                    Date : ___/___/______<br>
                    Heure : ___h___
                </div>
            </div>

            <div class="signature-box">
                <div class="signature-title">LIVREUR COLLECTEUR</div>
                <div class="signature-line"></div>
                <div class="signature-info">
                    Nom : ________________________<br>
                    Date : ___/___/______<br>
                    Heure : ___h___
                </div>
            </div>
        </div>

        <div style="margin-top: 15px; padding: 8px; background-color: #FEF3C7; border-radius: 3px; border-left: 3px solid #F59E0B;">
            <strong style="color: #92400E; font-size: 9px;">IMPORTANT :</strong>
            <ul style="margin: 5px 0; padding-left: 15px; color: #92400E; font-size: 8px; line-height: 1.2;">
                <li>Vérifier l'état de chaque colis avant collecte</li>
                <li>Confirmer les informations du destinataire</li>
                <li>Signaler tout problème ou dommage apparent</li>
                <li>Conserver une copie de ce manifeste</li>
                <li>Valider la collecte dans le système après signature</li>
            </ul>
        </div>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <p><strong>AL-AMENA DELIVERY</strong> - Service de Livraison Professionnel</p>
        <p>Manifeste généré le {{ $generated_at->format('d/m/Y à H:i') }} | Document officiel</p>
        <p style="font-size: 9px; margin-top: 10px;">
            Ce document constitue un accord contractuel entre le client et AL-AMENA DELIVERY.
            La signature des deux parties confirme la prise en charge des colis listés ci-dessus.
        </p>
    </div>

    <script>
        // Auto-focus sur le bouton d'impression au chargement
        document.addEventListener('DOMContentLoaded', function() {
            // Optionnel : lancer l'impression automatiquement
            // window.print();
        });

        // Raccourci clavier pour imprimer
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>