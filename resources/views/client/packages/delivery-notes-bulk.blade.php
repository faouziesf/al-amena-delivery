<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bons de Livraison A5 - {{ $packages->count() }} colis</title>
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
            font-size: 10px;
            line-height: 1.2;
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
        
        /* Format A5 Horizontal pour chaque bon */
        .delivery-note {
            width: 210mm;
            height: 148mm;
            margin: 0 auto 10mm auto;
            padding: 8mm;
            background: white;
            border: 1px solid #e5e7eb;
            page-break-after: always;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        
        .delivery-note:last-child {
            page-break-after: avoid;
        }
        
        /* En-tête compacte */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 2px solid #2563eb;
            height: 50px;
        }
        
        .logo-section {
            flex: 1;
        }
        
        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 2px;
        }
        
        .company-subtitle {
            color: #6b7280;
            font-size: 8px;
        }
        
        .document-info {
            text-align: right;
            flex: 1;
        }
        
        .document-title {
            font-size: 12px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 3px;
        }
        
        .package-code {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
            background: #eff6ff;
            padding: 3px 6px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 4px;
        }
        
        .date-info {
            color: #6b7280;
            font-size: 7px;
        }

        /* Section codes compacte */
        .codes-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 40px;
        }

        .barcode-container {
            flex: 2;
            text-align: center;
        }

        .barcode-title {
            font-size: 7px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .package-barcode {
            margin: 0 auto;
            max-width: 100%;
        }

        .barcode-number {
            font-size: 8px;
            font-weight: bold;
            color: #1f2937;
            margin-top: 2px;
            letter-spacing: 0.3px;
        }

        .qr-container {
            flex: 1;
            text-align: center;
            padding-left: 8px;
        }

        .qr-title {
            font-size: 6px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .package-qr {
            margin: 0 auto;
            border: 1px solid #e5e7eb;
            border-radius: 2px;
        }

        .qr-tracking-info {
            font-size: 5px;
            color: #6b7280;
            margin-top: 2px;
            max-width: 60px;
            word-break: break-all;
        }

        .qr-fallback {
            width: 30px;
            height: 30px;
            border: 1px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 6px;
            text-align: center;
            background: #f9f9f9;
            color: #666;
            margin: 0 auto;
            border-radius: 2px;
        }
        
        /* Contenu principal - Layout horizontal optimisé */
        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr 100px; /* Pickup | Delivery | Amount */
            gap: 6px;
            margin-bottom: 6px;
            flex: 1;
        }
        
        .section {
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .section-header {
            background: #f9fafb;
            padding: 4px 6px;
            font-weight: bold;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            font-size: 8px;
            flex-shrink: 0;
        }
        
        .section-content {
            padding: 6px;
            flex: 1;
            font-size: 7px;
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
            margin-bottom: 3px;
            display: flex;
            align-items: flex-start;
        }
        
        .info-label {
            font-weight: bold;
            color: #6b7280;
            min-width: 40px;
            margin-right: 4px;
            font-size: 6px;
        }
        
        .info-value {
            flex: 1;
            color: #1f2937;
            font-size: 7px;
            line-height: 1.2;
        }
        
        /* Section montant compacte */
        .amount-section {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            border-radius: 6px;
            padding: 6px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .amount-label {
            font-size: 6px;
            color: #92400e;
            margin-bottom: 2px;
            font-weight: bold;
        }
        
        .amount-value {
            font-size: 16px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 2px;
        }
        
        .amount-currency {
            font-size: 6px;
            color: #92400e;
        }

        /* Détails du colis compacts */
        .package-details {
            background: #f0f9ff;
            border: 1px solid #0284c7;
            border-radius: 4px;
            padding: 4px;
            margin-bottom: 6px;
            font-size: 7px;
        }

        .package-details-title {
            font-weight: bold;
            color: #0c4a6e;
            margin-bottom: 3px;
            font-size: 7px;
        }

        .package-details-content {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .detail-item {
            flex: 1;
            min-width: 60px;
        }
        
        .special-instructions {
            background: #fef2f2;
            border-left: 2px solid #ef4444;
            padding: 4px;
            margin-bottom: 4px;
            font-size: 6px;
        }
        
        .instructions-title {
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 2px;
            font-size: 7px;
        }
        
        .instructions-content {
            color: #7f1d1d;
            line-height: 1.2;
        }
        
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 6px;
            margin-top: 4px;
            padding-top: 4px;
            border-top: 1px solid #e5e7eb;
            flex-shrink: 0;
        }
        
        .signature-box {
            text-align: center;
            min-height: 35px;
            display: flex;
            flex-direction: column;
        }
        
        .signature-label {
            font-weight: bold;
            color: #6b7280;
            margin-bottom: 3px;
            font-size: 6px;
        }
        
        .signature-line {
            border-bottom: 1px solid #6b7280;
            flex: 1;
            margin-bottom: 2px;
        }
        
        .signature-date {
            font-size: 5px;
            color: #9ca3af;
        }
        
        .notes-section {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 4px;
            margin-bottom: 4px;
            font-size: 6px;
        }
        
        .notes-title {
            font-weight: bold;
            color: #374151;
            margin-bottom: 2px;
        }
        
        .notes-content {
            color: #6b7280;
            font-style: italic;
        }
        
        .footer {
            margin-top: auto;
            padding-top: 3px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 5px;
            flex-shrink: 0;
        }
        
        .page-number {
            position: absolute;
            top: 3mm;
            right: 3mm;
            background: #f3f4f6;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 6px;
            color: #6b7280;
        }
        
        /* Impression A5 Landscape */
        @media print {
            @page {
                size: A5 landscape;
                margin: 0;
            }
            
            body { margin: 0; }
            .print-controls, .batch-info, .no-print { display: none !important; }
            .delivery-note { 
                margin: 0; 
                padding: 8mm; 
                border: none;
                page-break-inside: avoid;
                width: 210mm;
                height: 148mm;
            }
        }
        
        @media screen {
            .delivery-note {
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Contrôles d'impression -->
    <div class="print-controls no-print">
        <button onclick="window.print()" class="print-button">🖨️ Imprimer A5</button>
        <button onclick="window.close()" class="close-button">❌ Fermer</button>
    </div>

    <!-- Informations du batch -->
    @if(isset($batch))
    <div class="batch-info no-print">
        <div class="batch-title">Bons de Livraison A5 - Batch {{ $batch->batch_code }}</div>
        <div class="batch-summary">
            {{ $packages->count() }} colis • Import du {{ $batch->created_at->format('d/m/Y à H:i') }}
        </div>
    </div>
    @else
    <div class="batch-info no-print">
        <div class="batch-title">Bons de Livraison A5 Sélectionnés</div>
        <div class="batch-summary">
            {{ $packages->count() }} colis • Généré le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>
    @endif

    <!-- Bons de livraison -->
    @foreach($packages as $index => $package)
    <div class="delivery-note" data-package-code="{{ $package->package_code }}">
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
                    {{ $package->created_at->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>

        <!-- Section Codes -->
        <div class="codes-section">
            <div class="barcode-container">
                <div class="barcode-title">Code de Suivi</div>
                <canvas class="package-barcode" id="barcode-{{ $index }}"></canvas>
                <div class="barcode-number">{{ $package->package_code }}</div>
            </div>
            
            <div class="qr-container">
                <div class="qr-title">QR</div>
                <div class="package-qr" id="qrcode-{{ $index }}"></div>
                <div class="qr-tracking-info">{{ url('/track/' . $package->package_code) }}</div>
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
                        <span class="info-value">{{ Str::limit($package->supplier_data['name'] ?? 'N/A', 25) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tél:</span>
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
                        <span class="info-value">{{ Str::limit($package->pickup_address, 30) }}</span>
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
                        <span class="info-value">{{ Str::limit($package->recipient_data['name'] ?? 'N/A', 25) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tél:</span>
                        <span class="info-value">{{ $package->recipient_data['phone'] ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Délégation:</span>
                        <span class="info-value">{{ $package->delegationTo->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Adresse:</span>
                        <span class="info-value">{{ Str::limit($package->recipient_data['address'] ?? 'N/A', 30) }}</span>
                    </div>
                </div>
            </div>

            <!-- Section Montant -->
            <div class="amount-section">
                <div class="amount-label">À ENCAISSER</div>
                <div class="amount-value">{{ number_format($package->cod_amount, 3) }}</div>
                <div class="amount-currency">DT</div>
            </div>
        </div>

        <!-- Détails du colis -->
        <div class="package-details">
            <div class="package-details-title">📋 DÉTAILS</div>
            <div class="package-details-content">
                <div class="detail-item">
                    <strong>Contenu:</strong> {{ Str::limit($package->content_description, 25) }}
                </div>
                @if($package->package_weight)
                <div class="detail-item">
                    <strong>Poids:</strong> {{ number_format($package->package_weight, 1) }}kg
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
            <div class="instructions-title">⚠️ INSTRUCTIONS</div>
            <div class="instructions-content">
                @if($package->is_fragile) FRAGILE • @endif
                @if($package->requires_signature) SIGNATURE • @endif
                @if($package->special_instructions) {{ Str::limit($package->special_instructions, 40) }} @endif
                @if($package->pickup_notes) • {{ Str::limit($package->pickup_notes, 40) }} @endif
            </div>
        </div>
        @endif

        <!-- Notes générales -->
        @if($package->notes)
        <div class="notes-section">
            <div class="notes-title">📝 Notes</div>
            <div class="notes-content">{{ Str::limit($package->notes, 50) }}</div>
        </div>
        @endif

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-label">EXPÉDITEUR</div>
                <div class="signature-line"></div>
                <div class="signature-date">Date: ___/___</div>
            </div>
            
            <div class="signature-box">
                <div class="signature-label">LIVREUR</div>
                <div class="signature-line"></div>
                <div class="signature-date">Date: ___/___</div>
            </div>
            
            <div class="signature-box">
                <div class="signature-label">DESTINATAIRE</div>
                <div class="signature-line"></div>
                <div class="signature-date">Date: ___/___</div>
            </div>
        </div>

        <!-- Pied de page -->
        <div class="footer">
            <p>Al-Amena Delivery - Service professionnel</p>
        </div>
    </div>
    @endforeach

    <script>
        // Fonction pour générer le QR Code manuellement
        function generateQRCodeManually(text, size = 4) {
            try {
                const qr = qrcode(0, 'L');
                qr.addData(text);
                qr.make();
                
                const cellSize = 2; // Très compact pour A5
                const margin = 2;
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

        // Générer tous les codes au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Génération des codes A5 pour {{ $packages->count() }} colis...');
            
            const packages = document.querySelectorAll('.delivery-note');
            let successCount = 0;
            let errorCount = 0;
            
            packages.forEach((packageElement, index) => {
                const packageCode = packageElement.getAttribute('data-package-code');
                const trackingUrl = "{{ url('/track/') }}/" + packageCode;
                
                try {
                    // 1. Générer le code-barres
                    const barcodeCanvas = document.getElementById(`barcode-${index}`);
                    if (barcodeCanvas && typeof JsBarcode !== 'undefined') {
                        JsBarcode(barcodeCanvas, packageCode, {
                            format: "CODE128",
                            width: 1.2,
                            height: 20,
                            displayValue: false,
                            background: "transparent",
                            lineColor: "#000000",
                            margin: 1
                        });
                    }
                    
                    // 2. Générer le QR Code
                    const qrContainer = document.getElementById(`qrcode-${index}`);
                    if (qrContainer) {
                        if (typeof qrcode !== 'undefined') {
                            const canvas = generateQRCodeManually(trackingUrl);
                            if (canvas) {
                                canvas.style.width = '30px';
                                canvas.style.height = '30px';
                                canvas.style.imageRendering = 'pixelated';
                                qrContainer.appendChild(canvas);
                                successCount++;
                            } else {
                                throw new Error('Impossible de générer le QR code');
                            }
                        } else {
                            throw new Error('Bibliothèque qrcode non disponible');
                        }
                    }
                } catch (error) {
                    console.error(`Erreur colis ${packageCode}:`, error);
                    errorCount++;
                    
                    // Affichage fallback
                    const qrContainer = document.getElementById(`qrcode-${index}`);
                    if (qrContainer) {
                        qrContainer.innerHTML = `
                            <div class="qr-fallback">
                                QR<br>Err
                            </div>
                        `;
                    }
                }
            });
            
            console.log(`Génération A5 terminée: ${successCount} succès, ${errorCount} erreurs`);
        });

        // Auto-print si paramètre d'URL
        if (window.location.search.includes('auto_print=1')) {
            window.onload = function() {
                setTimeout(() => window.print(), 2500); // Délai pour la génération
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