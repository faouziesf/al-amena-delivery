<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feuille de Route - {{ $runSheet->sheet_code }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: white;
        }
        
        .container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 15mm;
        }
        
        /* En-tête */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #2563eb;
        }
        
        .logo-section {
            flex: 1;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        
        .company-subtitle {
            font-size: 14px;
            color: #6b7280;
        }
        
        .sheet-info {
            text-align: right;
            flex: 1;
        }
        
        .sheet-code {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .print-date {
            font-size: 12px;
            color: #6b7280;
        }
        
        /* Informations livreur */
        .deliverer-info {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: 600;
            color: #374151;
            width: 120px;
        }
        
        .info-value {
            color: #1f2937;
            flex: 1;
        }
        
        /* Statistiques */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 4px;
        }
        
        .stat-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Types de colis */
        .package-types {
            margin-bottom: 20px;
        }
        
        .types-list {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .type-badge {
            background: #e0e7ff;
            color: #3730a3;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        /* Tableau des colis */
        .packages-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        
        .packages-table th,
        .packages-table td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        
        .packages-table th {
            background: #f3f4f6;
            font-weight: 600;
            color: #374151;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .packages-table tr:nth-child(even) {
            background: #f9fafb;
        }
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-accepted { background: #dbeafe; color: #1e40af; }
        .status-picked-up { background: #dcfce7; color: #15803d; }
        .status-verified { background: #fed7d7; color: #c53030; }
        
        .cod-amount {
            font-weight: 600;
            color: #059669;
        }
        
        .package-code {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: #1f2937;
        }
        
        /* Résumé COD */
        .cod-summary {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .cod-summary h3 {
            color: #92400e;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .cod-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        .cod-item {
            text-align: center;
        }
        
        .cod-value {
            font-size: 16px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 2px;
        }
        
        .cod-label {
            font-size: 10px;
            color: #78350f;
            text-transform: uppercase;
        }
        
        /* Signatures */
        .signatures {
            margin-top: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        
        .signature-box {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 20px;
            text-align: center;
            min-height: 80px;
        }
        
        .signature-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 10px;
        }
        
        .signature-line {
            border-top: 1px solid #9ca3af;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 10px;
            color: #6b7280;
        }
        
        /* Instructions */
        .instructions {
            background: #e0f2fe;
            border: 1px solid #0284c7;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
        }
        
        .instructions h4 {
            color: #075985;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .instructions ul {
            list-style: none;
            padding: 0;
        }
        
        .instructions li {
            color: #0c4a6e;
            font-size: 10px;
            margin-bottom: 4px;
            padding-left: 12px;
            position: relative;
        }
        
        .instructions li:before {
            content: "→";
            position: absolute;
            left: 0;
            color: #0284c7;
            font-weight: bold;
        }
        
        /* Pied de page */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }
        
        /* Styles d'impression */
        @media print {
            .container {
                padding: 0;
                margin: 0;
                max-width: none;
            }
            
            .header {
                page-break-inside: avoid;
            }
            
            .packages-table {
                page-break-inside: avoid;
            }
            
            .packages-table th {
                background: #f3f4f6 !important;
            }
            
            .signatures {
                page-break-inside: avoid;
            }
            
            /* Forcer les couleurs d'arrière-plan */
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }
        
        /* Mode sombre désactivé pour l'impression */
        @media (prefers-color-scheme: dark) {
            body {
                background: white;
                color: #333;
            }
        }
        
        /* Adresse responsive */
        .address-cell {
            max-width: 120px;
            word-wrap: break-word;
            font-size: 9px;
        }
        
        /* Notes importantes */
        .important-note {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            border-radius: 4px;
            padding: 8px;
            margin: 5px 0;
            font-size: 9px;
            color: #7f1d1d;
        }
        
        .fragile-icon {
            color: #dc2626;
            font-weight: bold;
        }
        
        .signature-required {
            color: #7c2d12;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        <div class="header">
            <div class="logo-section">
                <div class="company-name">AL-AMENA DELIVERY</div>
                <div class="company-subtitle">Service de livraison express</div>
            </div>
            <div class="sheet-info">
                <div class="sheet-code">{{ $runSheet->sheet_code }}</div>
                <div class="print-date">Imprimé le {{ now()->format('d/m/Y à H:i') }}</div>
            </div>
        </div>

        <!-- Informations livreur -->
        <div class="deliverer-info">
            <div class="info-row">
                <span class="info-label">Livreur:</span>
                <span class="info-value">{{ $runSheet->deliverer->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Téléphone:</span>
                <span class="info-value">{{ $runSheet->deliverer->phone ?? 'Non renseigné' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Délégation:</span>
                <span class="info-value">{{ $runSheet->delegation->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date de route:</span>
                <span class="info-value">{{ $runSheet->date->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tri par:</span>
                <span class="info-value">
                    {{ $runSheet->sort_criteria === 'address' ? 'Adresse' : 
                       ($runSheet->sort_criteria === 'cod_amount' ? 'Montant COD' : 'Date de création') }}
                </span>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">{{ $runSheet->packages_count }}</div>
                <div class="stat-label">Total Colis</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ number_format($runSheet->total_cod_amount, 3) }}</div>
                <div class="stat-label">COD Total (DT)</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $codSummary['packages_with_cod'] ?? 0 }}</div>
                <div class="stat-label">Avec COD</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ number_format($codSummary['average_cod'] ?? 0, 2) }}</div>
                <div class="stat-label">COD Moyen (DT)</div>
            </div>
        </div>

        <!-- Types de colis -->
        @if($runSheet->package_types)
        <div class="package-types">
            <strong>Types inclus:</strong>
            <div class="types-list">
                @foreach($runSheet->package_types as $type)
                <span class="type-badge">
                    {{ $type === 'pickups' ? 'Collectes' : ($type === 'deliveries' ? 'Livraisons' : 'Retours') }}
                </span>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Instructions importantes -->
        <div class="instructions">
            <h4>📋 Instructions de Route</h4>
            <ul>
                <li>Vérifier l'identité du destinataire avant livraison</li>
                <li>Collecter le COD EXACT selon le montant indiqué</li>
                <li>Prendre photo de preuve pour les livraisons importantes</li>
                <li>Marquer les colis comme livrés immédiatement après remise</li>
                <li>En cas de problème, contacter le commercial</li>
                <li>Retourner les colis non livrés selon les procédures</li>
            </ul>
        </div>

        <!-- Résumé COD (si activé) -->
        @if($runSheet->include_cod_summary && isset($codSummary))
        <div class="cod-summary">
            <h3>💰 Résumé COD à Collecter</h3>
            <div class="cod-grid">
                <div class="cod-item">
                    <div class="cod-value">{{ number_format($codSummary['total_cod_amount'] ?? 0, 3) }}</div>
                    <div class="cod-label">Total à collecter</div>
                </div>
                <div class="cod-item">
                    <div class="cod-value">{{ $codSummary['packages_with_cod'] ?? 0 }}</div>
                    <div class="cod-label">Colis avec COD</div>
                </div>
                <div class="cod-item">
                    <div class="cod-value">{{ $codSummary['total_packages'] - $codSummary['packages_with_cod'] }}</div>
                    <div class="cod-label">Colis sans COD</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Tableau des colis -->
        <table class="packages-table">
            <thead>
                <tr>
                    <th style="width: 12%">Code Colis</th>
                    <th style="width: 8%">Statut</th>
                    <th style="width: 15%">Destinataire</th>
                    <th style="width: 12%">Téléphone</th>
                    <th style="width: 25%">Adresse</th>
                    <th style="width: 10%">COD (DT)</th>
                    <th style="width: 15%">Contenu</th>
                    <th style="width: 3%">📦</th>
                </tr>
            </thead>
            <tbody>
                @foreach($packages as $package)
                <tr>
                    <td class="package-code">{{ $package['package_code'] ?? 'N/A' }}</td>
                    <td>
                        <span class="status-badge status-{{ strtolower($package['status'] ?? 'unknown') }}">
                            {{ $package['status'] ?? 'N/A' }}
                        </span>
                    </td>
                    <td>
                        @if($package['status'] === 'ACCEPTED')
                            {{ $package['sender_data']['name'] ?? 'N/A' }}
                        @else
                            {{ $package['recipient_data']['name'] ?? 'N/A' }}
                        @endif
                    </td>
                    <td>
                        @if($package['status'] === 'ACCEPTED')
                            {{ $package['sender_data']['phone'] ?? 'N/A' }}
                        @else
                            {{ $package['recipient_data']['phone'] ?? 'N/A' }}
                        @endif
                    </td>
                    <td class="address-cell">
                        @if($package['status'] === 'ACCEPTED')
                            {{ $package['pickup_address'] ?? $package['sender_data']['address'] ?? 'N/A' }}
                        @else
                            {{ $package['recipient_data']['address'] ?? 'N/A' }}
                        @endif
                    </td>
                    <td class="cod-amount">
                        @if(($package['cod_amount'] ?? 0) > 0)
                            {{ number_format($package['cod_amount'], 3) }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        {{ $package['content_description'] ?? 'N/A' }}
                        
                        @if($package['is_fragile'] ?? false)
                        <div class="important-note">
                            <span class="fragile-icon">⚠️ FRAGILE</span>
                        </div>
                        @endif
                        
                        @if($package['requires_signature'] ?? false)
                        <div class="important-note">
                            <span class="signature-required">✍️ Signature requise</span>
                        </div>
                        @endif
                        
                        @if(!empty($package['special_instructions']))
                        <div class="important-note">
                            📝 {{ $package['special_instructions'] }}
                        </div>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if(($package['delivery_attempts'] ?? 0) >= 3)
                            <span style="color: #dc2626; font-weight: bold;">🚨</span>
                        @elseif(($package['delivery_attempts'] ?? 0) > 0)
                            <span style="color: #f59e0b; font-weight: bold;">{{ $package['delivery_attempts'] }}</span>
                        @else
                            <span style="color: #10b981;">✓</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-title">Signature Livreur</div>
                <div class="signature-line">{{ $runSheet->deliverer->name }}</div>
            </div>
            <div class="signature-box">
                <div class="signature-title">Signature Superviseur</div>
                <div class="signature-line">Nom et signature</div>
            </div>
        </div>

        <!-- Pied de page -->
        <div class="footer">
            <p>
                📱 Application Al-Amena Delivery | 
                📞 Support: +216 XX XXX XXX | 
                🌐 www.alamena-delivery.com
            </p>
            <p style="margin-top: 5px;">
                Document généré automatiquement le {{ now()->format('d/m/Y à H:i:s') }} - 
                Ne pas modifier manuellement
            </p>
        </div>
    </div>

    <!-- JavaScript pour l'impression automatique -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-print si demandé via URL
            if (window.location.search.includes('auto_print=1')) {
                setTimeout(() => {
                    window.print();
                }, 500);
            }
        });
        
        // Améliorer l'impression
        window.addEventListener('beforeprint', function() {
            document.title = 'Feuille de Route - {{ $runSheet->sheet_code }}';
        });
        
        window.addEventListener('afterprint', function() {
            // Rediriger vers l'index après impression (optionnel)
            if (window.location.search.includes('auto_close=1')) {
                window.close();
            }
        });
    </script>
</body>
</html>