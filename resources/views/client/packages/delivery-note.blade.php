<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de Livraison - {{ $package->package_code }}</title>
    <!-- Bibliothèques pour codes-barres et QR codes -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.3/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.5/JsBarcode.all.min.js"></script>
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
        }
        
        .delivery-note {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            padding: 10mm;
            background: white;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            color: #6b7280;
            font-size: 14px;
        }
        
        .document-info {
            text-align: right;
            flex: 1;
        }
        
        .document-title {
            font-size: 20px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .package-code {
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
            background: #eff6ff;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .date-info {
            color: #6b7280;
            font-size: 11px;
        }

        /* Section codes de suivi */
        .tracking-codes {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .barcode-section {
            text-align: center;
            flex: 1;
        }

        .qr-section {
            text-align: center;
            flex: 1;
        }

        .tracking-info {
            flex: 1;
            text-align: center;
            padding: 0 20px;
        }

        .tracking-url {
            font-size: 10px;
            color: #4f46e5;
            word-break: break-all;
            margin-top: 5px;
        }

        .code-label {
            font-size: 10px;
            color: #64748b;
            margin-bottom: 5px;
            font-weight: bold;
        }

        #barcode {
            max-width: 200px;
            height: 60px;
        }

        #qrcode {
            margin: 0 auto;
        }
        
        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .section {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .section-header {
            background: #f9fafb;
            padding: 10px 15px;
            font-weight: bold;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .section-content {
            padding: 15px;
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
            margin-bottom: 8px;
            display: flex;
            align-items: flex-start;
        }
        
        .info-label {
            font-weight: bold;
            color: #6b7280;
            min-width: 80px;
            margin-right: 10px;
        }
        
        .info-value {
            flex: 1;
            color: #1f2937;
        }
        
        .package-details {
            width: 100%;
            margin-bottom: 25px;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 20px;
        }
        
        .package-info {
            grid-column: span 1;
        }
        
        .amount-section {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        
        .amount-label {
            font-size: 12px;
            color: #92400e;
            margin-bottom: 5px;
        }
        
        .amount-value {
            font-size: 28px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 5px;
        }
        
        .amount-currency {
            font-size: 14px;
            color: #92400e;
        }
        
        .special-instructions {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 12px 15px;
            margin-bottom: 20px;
        }
        
        .instructions-title {
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 5px;
        }
        
        .instructions-content {
            color: #7f1d1d;
        }
        
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        
        .signature-box {
            text-align: center;
            min-height: 80px;
        }
        
        .signature-label {
            font-weight: bold;
            color: #6b7280;
            margin-bottom: 10px;
        }
        
        .signature-line {
            border-bottom: 1px solid #6b7280;
            height: 50px;
            margin-bottom: 8px;
        }
        
        .signature-date {
            font-size: 10px;
            color: #9ca3af;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-created { background: #f3f4f6; color: #374151; }
        .status-available { background: #dbeafe; color: #1e40af; }
        .status-accepted { background: #fce7f3; color: #be185d; }
        .status-picked_up { background: #e0e7ff; color: #3730a3; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-returned { background: #fed7aa; color: #c2410c; }
        
        .notes-section {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
        }
        
        .notes-title {
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
        }
        
        .notes-content {
            color: #6b7280;
            font-style: italic;
        }
        
        @media print {
            body { margin: 0; }
            .delivery-note { margin: 0; padding: 10mm; }
            .no-print { display: none !important; }
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .print-button:hover {
            background: #1d4ed8;
        }

        .scan-instructions {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 10px;
            margin-top: 10px;
            font-size: 10px;
            color: #1e40af;
            text-align: center;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">🖨️ Imprimer</button>
    
    <div class="delivery-note">
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
                    Créé le {{ $package->created_at->format('d/m/Y à H:i') }}<br>
                    <span class="status-badge status-{{ strtolower($package->status) }}">
                        {{ $package->status }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Section Codes de Suivi -->
        <div class="tracking-codes">
            <div class="barcode-section">
                <div class="code-label">CODE-BARRES</div>
                <svg id="barcode"></svg>
                <div class="code-label" style="margin-top: 5px;">{{ $package->package_code }}</div>
            </div>
            
            <div class="tracking-info">
                <div class="code-label">SUIVI EN LIGNE</div>
                <div style="font-weight: bold; color: #1f2937; margin: 5px 0;">
                    Scanner pour suivre
                </div>
                <div class="tracking-url">
                    {{ url('/track/' . $package->package_code) }}
                </div>
                <div class="scan-instructions">
                    📱 Scannez le QR code ou le code-barres pour suivre ce colis en temps réel
                </div>
            </div>
            
            <div class="qr-section">
                <div class="code-label">QR CODE</div>
                <div id="qrcode"></div>
                <div class="code-label" style="margin-top: 5px;">Suivi Rapide</div>
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
                    
                    @if($package->pickup_phone)
                    <div class="info-row">
                        <span class="info-label">Contact:</span>
                        <span class="info-value">{{ $package->pickup_phone }}</span>
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
            <div class="details-grid">
                <div class="package-info">
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
                            
                            @if($package->getFormattedDimensionsAttribute())
                            <div class="info-row">
                                <span class="info-label">Dimensions:</span>
                                <span class="info-value">{{ $package->getFormattedDimensionsAttribute() }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="amount-section">
                    <div class="amount-label">MONTANT À ENCAISSER</div>
                    <div class="amount-value">{{ number_format($package->cod_amount, 3) }}</div>
                    <div class="amount-currency">DINARS TUNISIENS</div>
                </div>
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
                
                @if($package->pickup_notes)
                    <strong>Notes pickup:</strong> {{ $package->pickup_notes }}
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
            <p>Pour toute réclamation, contactez notre service client</p>
            <p>Document généré le {{ now()->format('d/m/Y à H:i:s') }} - ID: {{ $package->id }}</p>
        </div>
    </div>

    <script>
        // Générer le code-barres
        JsBarcode("#barcode", "{{ $package->package_code }}", {
            format: "CODE128",
            width: 2,
            height: 60,
            displayValue: false,
            background: "transparent",
            lineColor: "#000000"
        });

        // Générer le QR Code
        const trackingUrl = "{{ url('/track/' . $package->package_code) }}";
        QRCode.toCanvas(document.getElementById('qrcode'), trackingUrl, {
            width: 80,
            height: 80,
            margin: 1,
            color: {
                dark: '#000000',
                light: '#ffffff'
            }
        }, function (error) {
            if (error) console.error(error);
        });

        // Auto-print si paramètre d'URL
        if (window.location.search.includes('auto_print=1')) {
            window.onload = function() {
                setTimeout(() => window.print(), 500);
            };
        }
    </script>
</body>
</html>