<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bons de Livraison - {{ $packages->count() }} colis</title>
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
        }
        
        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
        }
        
        .print-button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .print-button:hover {
            background: #1d4ed8;
        }
        
        .close-button {
            background: #6b7280;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .batch-info {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .batch-title {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .batch-summary {
            color: #6b7280;
            font-size: 12px;
        }
        
        .delivery-note {
            width: 100%;
            max-width: 190mm;
            margin: 0 auto 15mm auto;
            padding: 8mm;
            background: white;
            border: 1px solid #e5e7eb;
            page-break-after: always;
            position: relative;
        }
        
        .delivery-note:last-child {
            page-break-after: avoid;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2563eb;
        }
        
        .logo-section {
            flex: 1;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 3px;
        }
        
        .company-subtitle {
            color: #6b7280;
            font-size: 11px;
        }
        
        .document-info {
            text-align: right;
            flex: 1;
        }
        
        .document-title {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 3px;
        }
        
        .package-code {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
            background: #eff6ff;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 5px;
        }
        
        .date-info {
            color: #6b7280;
            font-size: 9px;
        }
        
        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .section {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
        }
        
        .section-header {
            background: #f9fafb;
            padding: 8px 12px;
            font-weight: bold;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }
        
        .section-content {
            padding: 10px 12px;
        }
        
        .pickup-section .section-header {
            background: #fef3c7;
            color: #92400e;
        }
        
        .delivery-section .section-header {
            background: #d1fae5;
            color: #065f46;
        }
        
        .info-row {
            margin-bottom: 6px;
            display: flex;
            align-items: flex-start;
        }
        
        .info-label {
            font-weight: bold;
            color: #6b7280;
            min-width: 70px;
            margin-right: 8px;
            font-size: 9px;
        }
        
        .info-value {
            flex: 1;
            color: #1f2937;
            font-size: 10px;
        }
        
        .package-details {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .amount-section {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
        }
        
        .amount-label {
            font-size: 9px;
            color: #92400e;
            margin-bottom: 3px;
        }
        
        .amount-value {
            font-size: 22px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 3px;
        }
        
        .amount-currency {
            font-size: 10px;
            color: #92400e;
        }
        
        .special-instructions {
            background: #fef2f2;
            border-left: 3px solid #ef4444;
            padding: 8px 10px;
            margin-bottom: 12px;
            font-size: 9px;
        }
        
        .instructions-title {
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 3px;
        }
        
        .instructions-content {
            color: #7f1d1d;
        }
        
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
        }
        
        .signature-box {
            text-align: center;
            min-height: 60px;
        }
        
        .signature-label {
            font-weight: bold;
            color: #6b7280;
            margin-bottom: 6px;
            font-size: 9px;
        }
        
        .signature-line {
            border-bottom: 1px solid #6b7280;
            height: 35px;
            margin-bottom: 5px;
        }
        
        .signature-date {
            font-size: 8px;
            color: #9ca3af;
        }
        
        .notes-section {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 8px;
            margin-bottom: 12px;
            font-size: 9px;
        }
        
        .notes-title {
            font-weight: bold;
            color: #374151;
            margin-bottom: 3px;
        }
        
        .notes-content {
            color: #6b7280;
            font-style: italic;
        }
        
        .footer {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 8px;
        }
        
        .page-number {
            position: absolute;
            top: 5mm;
            right: 5mm;
            background: #f3f4f6;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 8px;
            color: #6b7280;
        }
        
        @media print {
            body { margin: 0; }
            .print-controls, .batch-info, .no-print { display: none !important; }
            .delivery-note { 
                margin: 0; 
                padding: 8mm; 
                border: none;
                page-break-inside: avoid;
            }
        }
        
        @media screen {
            .delivery-note {
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Contrôles d'impression -->
    <div class="print-controls no-print">
        <button onclick="window.print()" class="print-button">🖨️ Imprimer Tous</button>
        <button onclick="window.close()" class="close-button">❌ Fermer</button>
    </div>

    <!-- Informations du batch -->
    @if(isset($batch))
    <div class="batch-info no-print">
        <div class="batch-title">Bons de Livraison - Batch {{ $batch->batch_code }}</div>
        <div class="batch-summary">
            {{ $packages->count() }} colis • Import du {{ $batch->created_at->format('d/m/Y à H:i') }}
        </div>
    </div>
    @else
    <div class="batch-info no-print">
        <div class="batch-title">Bons de Livraison Sélectionnés</div>
        <div class="batch-summary">
            {{ $packages->count() }} colis • Généré le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>
    @endif

    <!-- Bons de livraison -->
    @foreach($packages as $index => $package)
    <div class="delivery-note">
        <div class="page-number">{{ $index + 1 }}/{{ $packages->count() }}</div>
        
        <!-- En-tête -->
        <div class="header">
            <div class="logo-section">
                <div class="company-name">AL-AMENA DELIVERY</div>
                <div class="company-subtitle">Service de livraison rapide et sécurisé</div>
            </div>
            
            <div class="document-info">
                <div class="document-title">BON DE LIVRAISON</div>
                <div class="package-code">{{ $package->package_code }}</div>
                <div class="date-info">
                    Créé le {{ $package->created_at->format('d/m/Y à H:i') }}
                </div>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="main-content">
            <!-- Section Pickup -->
            <div class="section pickup-section">
                <div class="section-header">📦 COLLECTE</div>
                <div class="section-content">
                    @if($package->supplier_data && is_array($package->supplier_data))
                    <div class="info-row">
                        <span class="info-label">Fournisseur:</span>
                        <span class="info-value">{{ $package->supplier_data['name'] ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Téléphone:</span>
                        <span class="info-value">{{ $package->supplier_data['phone'] ?? 'N/A' }}</span>
                    </div>
                    @endif
                    
                    <div class="info-row">
                        <span class="info-label">Délégation:</span>
                        <span class="info-value">{{ $package->delegationFrom->name ?? 'N/A' }}</span>
                    </div>
                    
                    @if($package->pickup_address)
                    <div class="info-row">
                        <span class="info-label">Adresse:</span>
                        <span class="info-value">{{ $package->pickup_address }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Section Livraison -->
            <div class="section delivery-section">
                <div class="section-header">🎯 LIVRAISON</div>
                <div class="section-content">
                    <div class="info-row">
                        <span class="info-label">Destinataire:</span>
                        <span class="info-value">{{ $package->recipient_data['name'] ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Téléphone:</span>
                        <span class="info-value">{{ $package->recipient_data['phone'] ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Délégation:</span>
                        <span class="info-value">{{ $package->delegationTo->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Adresse:</span>
                        <span class="info-value">{{ $package->recipient_data['address'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Détails du colis -->
        <div class="package-details">
            <div class="section">
                <div class="section-header">📋 DÉTAILS DU COLIS</div>
                <div class="section-content">
                    <div class="info-row">
                        <span class="info-label">Contenu:</span>
                        <span class="info-value">{{ $package->content_description }}</span>
                    </div>
                    
                    @if($package->package_weight)
                    <div class="info-row">
                        <span class="info-label">Poids:</span>
                        <span class="info-value">{{ $package->getFormattedWeightAttribute() }}</span>
                    </div>
                    @endif
                    
                    @if($package->package_value)
                    <div class="info-row">
                        <span class="info-label">Valeur:</span>
                        <span class="info-value">{{ $package->getFormattedValueAttribute() }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="amount-section">
                <div class="amount-label">MONTANT À ENCAISSER</div>
                <div class="amount-value">{{ number_format($package->cod_amount, 3) }}</div>
                <div class="amount-currency">DINARS TUNISIENS</div>
            </div>
        </div>

        <!-- Instructions spéciales -->
        @if($package->hasSpecialRequirements())
        <div class="special-instructions">
            <div class="instructions-title">⚠️ INSTRUCTIONS SPÉCIALES</div>
            <div class="instructions-content">
                @foreach($package->getSpecialRequirementsListAttribute() as $requirement)
                    • {{ $requirement }}<br>
                @endforeach
                
                @if($package->special_instructions)
                    <strong>Notes:</strong> {{ $package->special_instructions }}<br>
                @endif
            </div>
        </div>
        @endif

        <!-- Notes générales -->
        @if($package->notes)
        <div class="notes-section">
            <div class="notes-title">📝 Notes</div>
            <div class="notes-content">{{ $package->notes }}</div>
        </div>
        @endif

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-label">EXPÉDITEUR</div>
                <div class="signature-line"></div>
                <div class="signature-date">Date: ___/___/______</div>
            </div>
            
            <div class="signature-box">
                <div class="signature-label">LIVREUR</div>
                <div class="signature-line"></div>
                <div class="signature-date">Date: ___/___/______</div>
            </div>
            
            <div class="signature-box">
                <div class="signature-label">DESTINATAIRE</div>
                <div class="signature-line"></div>
                <div class="signature-date">Date: ___/___/______</div>
            </div>
        </div>

        <!-- Pied de page -->
        <div class="footer">
            <p>Al-Amena Delivery - Service de livraison professionnel</p>
        </div>
    </div>
    @endforeach

    <script>
        // Auto-print si paramètre d'URL
        if (window.location.search.includes('auto_print=1')) {
            window.onload = function() {
                setTimeout(() => window.print(), 1000);
            };
        }
        
        // Confirmation avant fermeture si pas d'impression
        let printed = false;
        window.addEventListener('beforeprint', () => printed = true);
        window.addEventListener('beforeunload', (e) => {
            if (!printed) {
                e.preventDefault();
                e.returnValue = 'Êtes-vous sûr de vouloir fermer sans imprimer ?';
            }
        });
    </script>
</body>
</html>