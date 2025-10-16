<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manifeste {{ $manifest_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .header {
            border-bottom: 3px solid #4F46E5;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .company-info {
            text-align: center;
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 5px;
        }

        .company-tagline {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .manifest-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            background-color: #4F46E5;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .manifest-number {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
            background-color: #F3F4F6;
            padding: 10px;
            border-radius: 5px;
        }

        .info-section {
            margin-bottom: 25px;
        }

        .info-title {
            font-size: 14px;
            font-weight: bold;
            background-color: #E5E7EB;
            padding: 8px;
            border-left: 4px solid #4F46E5;
            margin-bottom: 10px;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 5px 10px 5px 0;
            width: 30%;
            vertical-align: top;
        }

        .info-value {
            display: table-cell;
            padding: 5px 0;
            vertical-align: top;
        }

        .packages-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }

        .packages-table th {
            background-color: #4F46E5;
            color: white;
            padding: 8px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }

        .packages-table td {
            padding: 6px 4px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        .packages-table tr:nth-child(even) {
            background-color: #F9FAFB;
        }

        .summary-box {
            background-color: #F3F4F6;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-item {
            display: table-cell;
            text-align: center;
            padding: 10px;
            border-right: 1px solid #D1D5DB;
            width: 25%;
        }

        .summary-item:last-child {
            border-right: none;
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #4F46E5;
            display: block;
        }

        .summary-label {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }

        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .signature-grid {
            display: table;
            width: 100%;
        }

        .signature-box {
            display: table-cell;
            width: 50%;
            padding: 20px;
            border: 2px solid #E5E7EB;
            border-radius: 5px;
            text-align: center;
            vertical-align: top;
        }

        .signature-box + .signature-box {
            margin-left: 20px;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 15px;
            color: #4F46E5;
        }

        .signature-line {
            border-bottom: 2px solid #333;
            height: 60px;
            margin: 20px 0 10px 0;
        }

        .signature-info {
            font-size: 10px;
            color: #666;
            line-height: 1.3;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #E5E7EB;
            padding-top: 15px;
        }

        .notes-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #FFFBEB;
            border-left: 4px solid #F59E0B;
            border-radius: 0 5px 5px 0;
        }

        .notes-title {
            font-weight: bold;
            color: #92400E;
            margin-bottom: 8px;
        }

        .tracking-number {
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }

        .cod-amount {
            color: #059669;
            font-weight: bold;
        }

        .page-break {
            page-break-before: always;
        }

        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            .signature-section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
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

        <div style="margin-top: 30px; padding: 15px; background-color: #FEF3C7; border-radius: 5px; border-left: 4px solid #F59E0B;">
            <strong style="color: #92400E;">IMPORTANT :</strong>
            <ul style="margin: 10px 0; padding-left: 20px; color: #92400E; font-size: 11px;">
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
</body>
</html>