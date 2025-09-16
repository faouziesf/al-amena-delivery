<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de Livraison - {{ $package->package_code }}</title>
    <!-- Bibliothèques pour codes -->
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
            font-size: 13px;
            line-height: 1.4;
            color: #333;
            background: white;
        }
        
        /* Format A4 */
        .delivery-note {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 15mm;
            background: white;
            position: relative;
        }
        
        /* En-tête avec logo et informations */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #2563eb;
        }
        
        .logo-section {
            flex: 1;
        }
        
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }
        
        .company-subtitle {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .company-contact {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.5;
        }
        
        .document-info {
            text-align: right;
            flex: 1;
        }
        
        .document-title {
            font-size: 22px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 8px;
        }
        
        .package-code {
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
            background: #eff6ff;
            padding: 6px 12px;
            border-radius: 6px;
            display: inline-block;
            margin-bottom: 10px;
            border: 2px solid #2563eb;
        }
        
        .date-info {
            color: #6b7280;
            font-size: 11px;
            margin-bottom: 10px;
        }

        /* Section codes - OPTIMISÉE ET COMPACTE */
        .codes-section {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .barcode-container {
            flex: 2;
            text-align: center;
        }

        .barcode-title {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        #barcode {
            margin: 0 auto;
            max-width: 100%;
        }

        .barcode-number {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            margin-top: 5px;
            letter-spacing: 1px;
        }

        /* QR Code - COMPACT */
        .qr-container {
            flex: 1;
            text-align: center;
            padding-left: 20px;
        }

        .qr-title {
            font-size: 10px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        #qrcode {
            margin: 0 auto;
        }

        .tracking-info {
            font-size: 9px;
            color: #6b7280;
            margin-top: 5px;
            max-width: 120px;
            word-break: break-all;
        }
        
        /* Grille principale */
        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .section {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            background: white;
        }
        
        .section-header {
            background: #f9fafb;
            padding: 12px 15px;
            font-weight: bold;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .section-content {
            padding: 15px;
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
            margin-bottom: 10px;
            display: flex;
            align-items: flex-start;
        }
        
        .info-label {
            font-weight: bold;
            color: #6b7280;
            min-width: 85px;
            margin-right: 12px;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        .info-value {
            flex: 1;
            color: #1f2937;
            font-size: 13px;
            font-weight: 500;
        }
        
        /* Section détails du colis */
        .package-details {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .package-info .section-header {
            background: #f0f9ff;
            color: #0c4a6e;
            border-bottom-color: #0284c7;
        }
        
        /* Section montant COD - OPTIMISÉE */
        .amount-section {
            background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%);
            border: 3px solid #f59e0b;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            position: relative;
        }
        
        .amount-section::before {
            content: '💰';
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: #f59e0b;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        
        .amount-label {
            font-size: 11px;
            color: #92400e;
            margin-bottom: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .amount-value {
            font-size: 28px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 5px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        
        .amount-currency {
            font-size: 12px;
            color: #92400e;
            font-weight: bold;
        }
        
        /* Instructions spéciales */
        .special-instructions {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .instructions-title {
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 8px;
            font-size: 13px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
        }
        
        .instructions-title::before {
            content: '⚠️';
            margin-right: 6px;
            font-size: 14px;
        }
        
        .instructions-content {
            color: #7f1d1d;
            line-height: 1.5;
            font-size: 12px;
        }
        
        /* Notes générales */
        .notes-section {
            background: #f0fdf4;
            border: 2px solid #bbf7d0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }
        
        .notes-title {
            font-weight: bold;
            color: #166534;
            margin-bottom: 8px;
            font-size: 13px;
            display: flex;
            align-items: center;
        }
        
        .notes-title::before {
            content: '📝';
            margin-right: 6px;
        }
        
        .notes-content {
            color: #166534;
            font-style: italic;
            line-height: 1.5;
            font-size: 12px;
        }
        
        /* Signatures */
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
        }
        
        .signature-box {
            text-align: center;
            min-height: 80px;
            border: 1px dashed #9ca3af;
            border-radius: 6px;
            padding: 12px;
            background: #fafafa;
        }
        
        .signature-label {
            font-weight: bold;
            color: #374151;
            margin-bottom: 12px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .signature-line {
            border-bottom: 2px solid #6b7280;
            height: 50px;
            margin-bottom: 8px;
        }
        
        .signature-date {
            font-size: 9px;
            color: #9ca3af;
        }
        
        /* Pied de page */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
            line-height: 1.5;
        }
        
        .footer-logo {
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        
        /* Badges de statut */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-created { background: #f3f4f6; color: #374151; }
        .status-available { background: #dbeafe; color: #1e40af; }
        .status-accepted { background: #fce7f3; color: #be185d; }
        .status-picked_up { background: #e0e7ff; color: #3730a3; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-returned { background: #fed7aa; color: #c2410c; }
        
        /* Impression */
        @media print {
            body { 
                margin: 0; 
                background: white !important;
            }
            .delivery-note { 
                margin: 0; 
                padding: 15mm;
                width: 210mm;
                min-height: 297mm;
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
                padding: 10mm;
            }
            
            .main-content {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .package-details {
                grid-template-columns: 1fr;
            }
            
            .signatures {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .codes-section {
                flex-direction: column;
                text-align: center;
            }
            
            .qr-container {
                padding-left: 0;
                padding-top: 15px;
            }
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">
        🖨️ Imprimer
    </button>
    
    <div class="delivery-note">
        <!-- En-tête -->
        <div class="header">
            <div class="logo-section">
                <div class="company-name">AL-AMENA DELIVERY</div>
                <div class="company-subtitle">Service de livraison rapide et sécurisé</div>
                <div class="company-contact">
                    📧 contact@al-amena.tn<br>
                    📞 +216 XX XXX XXX<br>
                    📍 Adresse de l'entreprise, Ville, Tunisie
                </div>
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

        <!-- Section Codes - OPTIMISÉE -->
        <div class="codes-section">
            <div class="barcode-container">
                <div class="barcode-title">Code de Suivi</div>
                <canvas id="barcode"></canvas>
                <div class="barcode-number">{{ $package->package_code }}</div>
            </div>
            
            <div class="qr-container">
                <div class="qr-title">Suivi Rapide</div>
                <div id="qrcode"></div>
                <div class="tracking-info">{{ url('/track/' . $package->package_code) }}</div>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="main-content">
            <!-- Section Pickup -->
            <div class="section pickup-section">
                <div class="section-header">📦 Informations de Collecte</div>
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
                        <span class="info-value">{{ $package->pickup_address }}</span>
                    </div>
                    @endif
                    
                    @if($package->pickup_phone && $package->pickup_phone !== ($package->supplier_data['phone'] ?? ''))
                    <div class="info-row">
                        <span class="info-label">Contact:</span>
                        <span class="info-value">{{ $package->pickup_phone }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Section Livraison -->
            <div class="section delivery-section">
                <div class="section-header">🎯 Informations de Livraison</div>
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
                <div class="section-header">📋 Détails du Colis</div>
                <div class="section-content">
                    <div class="info-row">
                        <span class="info-label">Contenu:</span>
                        <span class="info-value">{{ $package->content_description }}</span>
                    </div>
                    
                    @if($package->package_weight)
                    <div class="info-row">
                        <span class="info-label">Poids:</span>
                        <span class="info-value">{{ number_format($package->package_weight, 3) }} kg</span>
                    </div>
                    @endif
                    
                    @if($package->package_value)
                    <div class="info-row">
                        <span class="info-label">Valeur:</span>
                        <span class="info-value">{{ number_format($package->package_value, 3) }} DT</span>
                    </div>
                    @endif
                    
                    @if($package->package_dimensions)
                    <div class="info-row">
                        <span class="info-label">Dimensions:</span>
                        <span class="info-value">
                            {{ $package->package_dimensions['length'] ?? 0 }} x 
                            {{ $package->package_dimensions['width'] ?? 0 }} x 
                            {{ $package->package_dimensions['height'] ?? 0 }} cm
                        </span>
                    </div>
                    @endif

                    <!-- FRAIS DE LIVRAISON SUPPRIMÉS COMME DEMANDÉ -->
                </div>
            </div>

            <div class="amount-section">
                <div class="amount-label">Montant à Encaisser</div>
                <div class="amount-value">{{ number_format($package->cod_amount, 3) }}</div>
                <div class="amount-currency">DINARS TUNISIENS</div>
            </div>
        </div>

        <!-- Instructions spéciales -->
        @if($package->is_fragile || $package->requires_signature || $package->special_instructions || $package->pickup_notes)
        <div class="special-instructions">
            <div class="instructions-title">Instructions Spéciales</div>
            <div class="instructions-content">
                @if($package->is_fragile)
                    <strong>🔸 FRAGILE :</strong> Manipuler avec précaution<br>
                @endif
                
                @if($package->requires_signature)
                    <strong>✍️ SIGNATURE REQUISE :</strong> Signature obligatoire du destinataire<br>
                @endif
                
                @if($package->special_instructions)
                    <strong>Instructions :</strong> {{ $package->special_instructions }}<br>
                @endif
                
                @if($package->pickup_notes)
                    <strong>Notes pickup :</strong> {{ $package->pickup_notes }}
                @endif
            </div>
        </div>
        @endif

        <!-- Notes générales -->
        @if($package->notes)
        <div class="notes-section">
            <div class="notes-title">Notes Générales</div>
            <div class="notes-content">{{ $package->notes }}</div>
        </div>
        @endif

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-label">Signature Expéditeur</div>
                <div class="signature-line"></div>
                <div class="signature-date">Date: ___/___/______</div>
            </div>
            
            <div class="signature-box">
                <div class="signature-label">Signature Livreur</div>
                <div class="signature-line"></div>
                <div class="signature-date">Date: ___/___/______</div>
            </div>
            
            <div class="signature-box">
                <div class="signature-label">Signature Destinataire</div>
                <div class="signature-line"></div>
                <div class="signature-date">Date: ___/___/______</div>
            </div>
        </div>

        <!-- Pied de page -->
        <div class="footer">
            <div class="footer-logo">AL-AMENA DELIVERY</div>
            <p>Service de livraison professionnel - Pour toute réclamation, contactez notre service client</p>
            <p>Document généré le {{ now()->format('d/m/Y à H:i:s') }} - Référence: {{ $package->id }}</p>
        </div>
    </div>

    <script>
        // Générer les codes au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            try {
                // Code-barres optimisé
                JsBarcode("#barcode", "{{ $package->package_code }}", {
                    format: "CODE128",
                    width: 2,
                    height: 50,
                    displayValue: false,
                    background: "transparent",
                    lineColor: "#000000",
                    margin: 5
                });
                
                // QR Code compact
                QRCode.toCanvas(document.getElementById('qrcode'), "{{ url('/track/' . $package->package_code) }}", {
                    width: 60,
                    height: 60,
                    margin: 1,
                    color: {
                        dark: '#000000',
                        light: '#ffffff'
                    }
                }, function (error) {
                    if (error) {
                        console.error('Erreur QR Code:', error);
                        // Masquer le QR code en cas d'erreur
                        document.getElementById('qrcode').style.display = 'none';
                    }
                });
                
            } catch (error) {
                console.error('Erreur génération codes:', error);
                // En cas d'erreur, masquer les sections concernées
                if (error.message.includes('barcode')) {
                    document.querySelector('.barcode-container').style.display = 'none';
                }
            }
        });

        // Auto-print si paramètre d'URL
        if (window.location.search.includes('auto_print=1')) {
            window.addEventListener('load', function() {
                setTimeout(() => window.print(), 1000);
            });
        }
    </script>
</body>
</html>