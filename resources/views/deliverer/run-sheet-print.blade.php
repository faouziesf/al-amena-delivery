<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Run Sheet - {{ auth()->user()->name }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .company-logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563EB;
        }

        .deliverer-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
        }

        .packages-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .packages-table th,
        .packages-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        .packages-table th {
            background: #2563EB;
            color: white;
            font-weight: bold;
        }

        .packages-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .barcode {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #333;
            padding: 5px;
            margin: 2px 0;
            background: white;
        }

        .signature-box {
            border: 1px solid #333;
            height: 40px;
            background: #f9f9f9;
        }

        .cod-amount {
            font-weight: bold;
            color: #16A34A;
            font-size: 14px;
        }

        .summary {
            background: #e5e7eb;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            text-align: center;
        }

        .summary-item {
            background: white;
            padding: 10px;
            border-radius: 3px;
            border: 1px solid #d1d5db;
        }

        .summary-number {
            font-size: 24px;
            font-weight: bold;
            color: #2563EB;
        }

        .instructions {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            padding: 10px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        @media screen {
            .print-controls {
                position: fixed;
                top: 10px;
                right: 10px;
                z-index: 1000;
            }

            .btn {
                padding: 10px 20px;
                margin: 0 5px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-weight: bold;
            }

            .btn-print {
                background: #2563EB;
                color: white;
            }

            .btn-close {
                background: #6B7280;
                color: white;
            }
        }
    </style>
</head>
<body>
    <!-- Contrôles d'impression (masqués à l'impression) -->
    <div class="print-controls no-print">
        <button class="btn btn-print" onclick="window.print()">🖨️ Imprimer</button>
        <button class="btn btn-close" onclick="window.close()">❌ Fermer</button>
    </div>

    <!-- En-tête -->
    <div class="header">
        <div class="company-logo">AL-AMENA DELIVERY</div>
        <h2>RUN SHEET DE LIVRAISON</h2>
        <p>{{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</p>
    </div>

    <!-- Informations Livreur -->
    <div class="deliverer-info">
        <div>
            <strong>Livreur:</strong> {{ auth()->user()->name }}<br>
            <strong>ID:</strong> {{ auth()->user()->id }}<br>
            <strong>Date:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y') }}
        </div>
        <div>
            <strong>Secteur:</strong> {{ $sector ?? 'Non défini' }}<br>
            <strong>Total Colis:</strong> {{ $packages->count() }}<br>
            <strong>COD Total:</strong> {{ number_format($packages->sum('cod_amount'), 3) }} DT
        </div>
    </div>

    <!-- Instructions -->
    <div class="instructions">
        <strong>INSTRUCTIONS IMPORTANTES:</strong><br>
        ✓ Scanner chaque colis avant livraison<br>
        ✓ Vérifier l'identité du destinataire<br>
        ✓ Collecter le montant COD exact<br>
        ✓ Faire signer le destinataire<br>
        ✓ Retourner les colis non livrés
    </div>

    <!-- Tableau des Colis -->
    <table class="packages-table">
        <thead>
            <tr>
                <th style="width: 50px;">#</th>
                <th style="width: 150px;">Code Colis</th>
                <th style="width: 200px;">Destinataire</th>
                <th style="width: 120px;">Téléphone</th>
                <th style="width: 250px;">Adresse</th>
                <th style="width: 100px;">COD (DT)</th>
                <th style="width: 150px;">Signature</th>
                <th style="width: 80px;">Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($packages as $index => $package)
            <tr>
                <!-- Numéro -->
                <td><strong>{{ $index + 1 }}</strong></td>

                <!-- Code avec Code-Barres -->
                <td>
                    <div class="barcode">{{ $package->code }}</div>
                    <div style="font-size: 10px; text-align: center;">
                        |||||||||||||||||||||||||||||||
                    </div>
                </td>

                <!-- Destinataire -->
                <td>
                    <strong>{{ $package->recipient_name }}</strong><br>
                    <small style="color: #666;">
                        {{ $package->client ? $package->client->name : 'Client N/A' }}
                    </small>
                </td>

                <!-- Téléphone -->
                <td>{{ $package->recipient_phone }}</td>

                <!-- Adresse -->
                <td>
                    {{ $package->recipient_address }}<br>
                    @if($package->recipient_city)
                        <small style="color: #666;">{{ $package->recipient_city }}</small>
                    @endif
                </td>

                <!-- COD -->
                <td>
                    @if($package->cod_amount > 0)
                        <div class="cod-amount">{{ number_format($package->cod_amount, 3) }}</div>
                    @else
                        <span style="color: #666;">-</span>
                    @endif
                </td>

                <!-- Zone de Signature -->
                <td>
                    <div class="signature-box"></div>
                </td>

                <!-- Statut -->
                <td style="font-size: 10px;">
                    ☐ Livré<br>
                    ☐ Absent<br>
                    ☐ Refusé<br>
                    ☐ Autre
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Résumé -->
    <div class="summary">
        <h3 style="margin-top: 0;">RÉSUMÉ DE LA TOURNÉE</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-number">{{ $packages->count() }}</div>
                <div>Total Colis</div>
            </div>
            <div class="summary-item">
                <div class="summary-number">{{ $packages->where('cod_amount', '>', 0)->count() }}</div>
                <div>Avec COD</div>
            </div>
            <div class="summary-item">
                <div class="summary-number">{{ number_format($packages->sum('cod_amount'), 0) }}</div>
                <div>Total COD (DT)</div>
            </div>
        </div>
    </div>

    <!-- Zone de Notes -->
    <div style="margin-top: 20px; border: 1px solid #333; padding: 15px; min-height: 100px;">
        <strong>NOTES ET OBSERVATIONS:</strong><br>
        <br><br><br><br><br>
    </div>

    <!-- Signatures -->
    <div style="display: flex; justify-content: space-between; margin-top: 30px;">
        <div style="width: 45%; text-align: center; border-top: 1px solid #333; padding-top: 10px;">
            <strong>Signature du Livreur</strong><br>
            {{ auth()->user()->name }}<br>
            Date: _______________
        </div>
        <div style="width: 45%; text-align: center; border-top: 1px solid #333; padding-top: 10px;">
            <strong>Signature du Superviseur</strong><br>
            Nom: _______________<br>
            Date: _______________
        </div>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <p>Document généré automatiquement le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i:s') }}</p>
        <p>Al-Amena Delivery - Système de Gestion des Livraisons</p>
    </div>

    <script>
        // Auto-impression si demandée
        if(window.location.search.includes('autoprint=1')) {
            window.onload = function() {
                setTimeout(() => {
                    window.print();
                }, 1000);
            }
        }
    </script>
</body>
</html>