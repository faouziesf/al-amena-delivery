<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de Livraison - {{ $package->package_code }}</title>
    <!-- Bibliothèques pour codes -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.5/JsBarcode.all.min.js"></script>
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
        
        /* Format A5 Horizontal (210mm x 148mm) */
        .delivery-note {
            width: 210mm;
            height: 148mm;
            margin: 0 auto;
            padding: 10mm;
            background: white;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        
        /* En-tête compacte */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 2px solid #2563eb;
            height: 60px;
        }
        
        .logo-section {
            flex: 1;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 2px;
            letter-spacing: 0.5px;
        }
        
        .company-subtitle {
            color: #6b7280;
            font-size: 9px;
            margin-bottom: 3px;
        }
        
        .company-contact {
            font-size: 8px;
            color: #6b7280;
            line-height: 1.2;
        }
        
        .document-info {
            text-align: right;
            flex: 1;
        }
        
        .document-title {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 4px;
        }
        
        .package-code {
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
            background: #eff6ff;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 6px;
            border: 2px solid #2563eb;
        }
        
        .date-info {
            color: #6b7280;
            font-size: 8px;
            margin-bottom: 4px;
        }

        /* Section codes compacte */
        .codes-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 50px;
        }

        .barcode-container {
            flex: 2;
            text-align: center;
        }

        .barcode-title {
            font-size: 8px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        #barcode {
            margin: 0 auto;
            max-width: 100%;
        }

        .barcode-number {
            font-size: 9px;
            font-weight: bold;
            color: #1f2937;
            margin-top: 2px;
            letter-spacing: 0.5px;
        }

        .qr-container {
            flex: 1;
            text-align: center;
            padding-left: 10px;
        }

        .qr-title {
            font-size: 7px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        #qrcode {
            margin: 0 auto;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
        }

        .tracking-info {
            font-size: 6px;
            color: #6b7280;
            margin-top: 2px;
            max-width: 80px;
            word-break: break-all;
        }

        .qr-fallback {
            font-size: 7px;
            color: #ef4444;
            margin-top: 2px;
        }
        
        /* Contenu principal - Layout horizontal optimisé */
        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr 140px; /* Pickup | Delivery | Amount */
            gap: 8px;
            margin-bottom: 8px;
            flex: 1;
        }
        
        .section {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
            background: white;
            display: flex;
            flex-direction: column;
        }
        
        .section-header {
            background: #f9fafb;
            padding: 6px 8px;
            font-weight: bold;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            flex-shrink: 0;
        }
        
        .section-content {
            padding: 8px;
            flex: 1;
            font-size: 9px;
        }
        
        .pickup-section .section-header {
            background: #fef3c7;
            color: #92400e;
            border-bottom-color: #f59e0b;
        }
        
        .delivery-section .section-header {
            background: #d1fae5;
            color: #065f46;
            border-bottom-color: #10b981;
        }
        
        .info-row {
            margin-bottom: 4px;
            display: flex;
            align-items: flex-start;
            font-size: 8px;
        }
        
        .info-label {
            font-weight: bold;
            color: #6b7280;
            min-width: 50px;
            margin-right: 6px;
            font-size: 7px;
            text-transform: uppercase;
        }
        
        .info-value {
            flex: 1;
            color: #1f2937;
            font-size: 8px;
            font-weight: 500;
            line-height: 1.2;
        }
        
        /* Section montant COD compacte */
        .amount-section {
            background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%);
            border: 2px solid #f59e0b;
            border-radius: 8px;
            padding: 8px;
            text-align: center;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .amount-section::before {
            content: '💰';
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            background: #f59e0b;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }
        
        .amount-label {
            font-size: 8px;
            color: #92400e;
            margin-bottom: 4px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .amount-value {
            font-size: 20px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 2px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        
        .amount-currency {
            font-size: 8px;
            color: #92400e;
            font-weight: bold;
        }

        /* Section détails du colis compacte */
        .package-details {
            background: #f0f9ff;
            border: 1px solid #0284c7;
            border-radius: 6px;
            padding: 6px;
            margin-bottom: 8px;
            font-size: 8px;
        }

        .package-details-title {
            font-weight: bold;
            color: #0c4a6e;
            margin-bottom: 4px;
            font-size: 9px;
            text-transform: uppercase;
        }

        .package-details-content {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .detail-item {
            flex: 1;
            min-width: 80px;
        }
        
        /* Instructions spéciales compactes */
        .special-instructions {
            background: #fef2f2;
            border-left: 3px solid #ef4444;
            border-radius: 4px;
            padding: 6px;
            margin-bottom: 6px;
            font-size: 8px;
        }
        
        .instructions-title {
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 3px;
            font-size: 8px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
        }
        
        .instructions-title::before {
            content: '⚠️';
            margin-right: 4px;
            font-size: 10px;
        }
        
        .instructions-content {
            color: #7f1d1d;
            line-height: 1.3;
            font-size: 7px;
        }
        
        /* Notes générales compactes */
        .notes-section {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 4px;
            padding: 6px;
            margin-bottom: 8px;
            font-size: 8px;
        }
        
        .notes-title {
            font-weight: bold;
            color: #166534;
            margin-bottom: 3px;
            font-size: 8px;
            display: flex;
            align-items: center;
        }
        
        .notes-title::before {
            content: '📝';
            margin-right: 4px;
        }
        
        .notes-content {
            color: #166534;
            font-style: italic;
            line-height: 1.3;
            font-size: 7px;
        }
        
        /* Signatures compactes */
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px solid #e5e7eb;
            flex-shrink: 0;
        }
        
        .signature-box {
            text-align: center;
            min-height: 40px;
            border: 1px dashed #9ca3af;
            border-radius: 4px;
            padding: 4px;
            background: #fafafa;
            display: flex;
            flex-direction: column;
        }
        
        .signature-label {
            font-weight: bold;
            color: #374151;
            margin-bottom: 4px;
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .signature-line {
            border-bottom: 1px solid #6b7280;
            flex: 1;
            margin-bottom: 2px;
        }
        
        .signature-date {
            font-size: 6px;
            color: #9ca3af;
        }
        
        /* Pied de page compact */
        .footer {
            margin-top: auto;
            padding-top: 4px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 6px;
            line-height: 1.3;
            flex-shrink: 0;
        }
        
        .footer-logo {
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 2px;
        }
        
        /* Badges de statut */
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .status-created { background: #f3f4f6; color: #374151; }
        .status-available { background: #dbeafe; color: #1e40af; }
        .status-accepted { background: #fce7f3; color: #be185d; }
        .status-picked_up { background: #e0e7ff; color: #3730a3; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-returned { background: #fed7aa; color: #c2410c; }
        
        /* Impression A5 Landscape */
        @media print {
            @page {
                size: A5 landscape;
                margin: 0;
            }
            
            body { 
                margin: 0; 
                background: white !important;
            }
            
            .delivery-note { 
                margin: 0; 
                padding: 10mm;
                width: 210mm;
                height: 148mm;
            }
            
            .no-print { 
                display: none !important; 
            }
            
            .section {
                break-inside: avoid;
            }
        }
        
        /* Bouton d'impression */
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            font-size: 13px;
            transition: all 0.2s;
        }
        
        .print-button:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .delivery-note {
                width: 100%;
                height: auto;
                min-height: 400px;
                padding: 8px;
            }
            
            .main-content {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            
            .codes-section {
                flex-direction: column;
                height: auto;
                padding: 10px;
            }
            
            .qr-container {
                padding-left: 0;
                padding-top: 8px;
            }
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">
        🖨️ Imprimer A5
    </button>
    
    <div class="delivery-note">
        <!-- En-tête -->
        <div class="header">
            <div class="logo-section">
                <div class="company-name">AL-AMENA DELIVERY</div>
                <div class="company-subtitle">Service de livraison rapide et sécurisé</div>
                <div class="company-contact">
                    📧 contact@al-amena.tn • 📞 +216 XX XXX XXX
                </div>
            </div>
            
            <div class="document-info">
                <div class="document-title">BON DE LIVRAISON</div>
                <div class="package-code">{{ $package->package_code }}</div>
                <div class="date-info">
                    {{ $package->created_at->format('d/m/Y H:i') }}
                    <span class="status-badge status-{{ strtolower($package->status) }}">
                        {{ $package->status }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Section Codes -->
        <div class="codes-section">
            <div class="barcode-container">
                <div class="barcode-title">Code de Suivi</div>
                <canvas id="barcode"></canvas>
                <div class="barcode-number">{{ $package->package_code }}</div>
            </div>
            
            <div class="qr-container">
                <div class="qr-title">Suivi QR</div>
                <div id="qrcode"></div>
                <div class="tracking-info">{{ url('/track/' . $package->package_code) }}</div>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="main-content">
            <!-- Section Pickup -->
            <div class="section pickup-section">
                <div class="section-header">📦 Collecte</div>
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
                    @else
                    <div class="info-row">
                        <span class="info-label">Expéditeur:</span>
                        <span class="info-value">{{ $package->sender_data['name'] ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Téléphone:</span>
                        <span class="info-value">{{ $package->sender_data['phone'] ?? 'N/A' }}</span>
                    </div>
                    @endif
                    
                    <div class="info-row">
                        <span class="info-label">Délégation:</span>
                        <span class="info-value">{{ $package->delegationFrom->name ?? 'N/A' }}</span>
                    </div>
                    
                    @if($package->pickup_address)
                    <div class="info-row">
                        <span class="info-label">Adresse:</span>
                        <span class="info-value">{{ Str::limit($package->pickup_address, 40) }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Section Livraison -->
            <div class="section delivery-section">
                <div class="section-header">🎯 Livraison</div>
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
                        <span class="info-value">{{ Str::limit($package->recipient_data['address'] ?? 'N/A', 40) }}</span>
                    </div>
                </div>
            </div>

            <!-- Section Montant -->
            <div class="amount-section">
                <div class="amount-label">À Encaisser</div>
                <div class="amount-value">{{ number_format($package->cod_amount, 3) }}</div>
                <div class="amount-currency">DT</div>
            </div>
        </div>

        <!-- Détails du colis -->
        <div class="package-details">
            <div class="package-details-title">📋 Détails du Colis</div>
            <div class="package-details-content">
                <div class="detail-item">
                    <strong>Contenu:</strong> {{ Str::limit($package->content_description, 30) }}
                </div>
                @if($package->package_weight)
                <div class="detail-item">
                    <strong>Poids:</strong> {{ number_format($package->package_weight, 2) }}kg
                </div>
                @endif
                @if($package->package_value)
                <div class="detail-item">
                    <strong>Valeur:</strong> {{ number_format($package->package_value, 0) }}DT
                </div>
                @endif
            </div>
        </div>

        <!-- Instructions spéciales -->
        @if($package->is_fragile || $package->requires_signature || $package->special_instructions || $package->pickup_notes)
        <div class="special-instructions">
            <div class="instructions-title">Instructions Spéciales</div>
            <div class="instructions-content">
                @if($package->is_fragile) FRAGILE • @endif
                @if($package->requires_signature) SIGNATURE REQUISE • @endif
                @if($package->special_instructions) {{ Str::limit($package->special_instructions, 60) }} @endif
                @if($package->pickup_notes) • {{ Str::limit($package->pickup_notes, 60) }} @endif
            </div>
        </div>
        @endif

        <!-- Notes générales -->
        @if($package->notes)
        <div class="notes-section">
            <div class="notes-title">Notes</div>
            <div class="notes-content">{{ Str::limit($package->notes, 80) }}</div>
        </div>
        @endif

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-label">Expéditeur</div>
                <div class="signature-line"></div>
                <div class="signature-date">Date: ___/___</div>
            </div>
            
            <div class="signature-box">
                <div class="signature-label">Livreur</div>
                <div class="signature-line"></div>
                <div class="signature-date">Date: ___/___</div>
            </div>
            
            <div class="signature-box">
                <div class="signature-label">Destinataire</div>
                <div class="signature-line"></div>
                <div class="signature-date">Date: ___/___</div>
            </div>
        </div>

        <!-- Pied de page -->
        <div class="footer">
            <div class="footer-logo">AL-AMENA DELIVERY</div>
            <p>Service de livraison professionnel • Généré le {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <script>
        // Fonction pour générer le QR Code manuellement
        function generateQRCodeManually(text, size = 6) {
            try {
                const qr = qrcode(0, 'L');
                qr.addData(text);
                qr.make();
                
                const cellSize = 2; // Taille réduite pour A5
                const margin = 4;
                const moduleCount = qr.getModuleCount();
                const canvasSize = (moduleCount * cellSize) + (margin * 2);
                
                const canvas = document.createElement('canvas');
                canvas.width = canvasSize;
                canvas.height = canvasSize;
                const ctx = canvas.getContext('2d');
                
                // Fond blanc
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, canvasSize, canvasSize);
                
                // Modules noirs
                ctx.fillStyle = '#000000';
                for (let row = 0; row < moduleCount; row++) {
                    for (let col = 0; col < moduleCount; col++) {
                        if (qr.isDark(row, col)) {
                            ctx.fillRect(
                                (col * cellSize) + margin,
                                (row * cellSize) + margin,
                                cellSize,
                                cellSize
                            );
                        }
                    }
                }
                
                return canvas;
            } catch (error) {
                console.error('Erreur génération QR manuel:', error);
                return null;
            }
        }

        // Générer les codes au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Génération des codes A5...');
            
            try {
                // Code-barres optimisé pour A5
                if (typeof JsBarcode !== 'undefined') {
                    JsBarcode("#barcode", "{{ $package->package_code }}", {
                        format: "CODE128",
                        width: 1.5,
                        height: 25,
                        displayValue: false,
                        background: "transparent",
                        lineColor: "#000000",
                        margin: 2
                    });
                    console.log('Code-barres généré avec succès');
                } else {
                    console.error('JsBarcode non disponible');
                    document.querySelector('.barcode-container').style.display = 'none';
                }
            } catch (error) {
                console.error('Erreur code-barres:', error);
                document.querySelector('.barcode-container').style.display = 'none';
            }
            
            // QR Code compact pour A5
            const qrContainer = document.getElementById('qrcode');
            const trackingUrl = "{{ url('/track/' . $package->package_code) }}";
            
            if (qrContainer) {
                try {
                    if (typeof qrcode !== 'undefined') {
                        console.log('Génération QR code...');
                        const canvas = generateQRCodeManually(trackingUrl);
                        if (canvas) {
                            canvas.style.width = '35px';
                            canvas.style.height = '35px';
                            canvas.style.imageRendering = 'pixelated';
                            qrContainer.appendChild(canvas);
                            console.log('QR Code généré avec succès');
                        } else {
                            throw new Error('Impossible de générer le QR code manuellement');
                        }
                    } else {
                        throw new Error('Bibliothèque qrcode-generator non disponible');
                    }
                } catch (error) {
                    console.error('Erreur QR Code:', error);
                    qrContainer.innerHTML = `
                        <div style="
                            width: 35px; 
                            height: 35px; 
                            border: 1px dashed #ccc; 
                            display: flex; 
                            align-items: center; 
                            justify-content: center; 
                            font-size: 6px; 
                            text-align: center;
                            background: #f9f9f9;
                            color: #666;
                            margin: 0 auto;
                        ">
                            QR<br>Erreur
                        </div>
                    `;
                }
            }
        });

        // Auto-print si paramètre d'URL
        if (window.location.search.includes('auto_print=1')) {
            window.addEventListener('load', function() {
                setTimeout(() => window.print(), 1500);
            });
        }
    </script>
</body>
</html>