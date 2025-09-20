{{-- resources/views/your-path/delivery-note.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de Livraison - {{ $package->package_code }}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.5/JsBarcode.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            background-color: #e9ecef;
            color: #212529;
            line-height: 1.4;
        }
        .controls {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            z-index: 100;
        }
        .btn-print {
            background-color: #0d6efd;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        /* Format A4 Vertical */
        .bon-a4 {
            background: white;
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 15mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }

        /* --- Sections --- */
        .bon-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
            margin-bottom: 10mm;
        }
        .bon-header .company-info .company-name {
            font-size: 24pt;
            font-weight: bold;
            color: #0d6efd;
        }
        .bon-header .company-info .company-details {
            font-size: 9pt;
            color: #6c757d;
        }
        .bon-header .document-info .document-title {
            font-size: 20pt;
            font-weight: bold;
            text-align: right;
        }
        .bon-header .document-info .document-date {
            font-size: 10pt;
            text-align: right;
            color: #6c757d;
        }

        section {
            margin-bottom: 8mm;
        }
        
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
            margin-bottom: 5mm;
        }

        .codes-section {
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 10mm 0;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .codes-section .barcode-container,
        .codes-section .qrcode-container {
            text-align: center;
        }
        .codes-section .code-label {
            font-size: 8pt;
            color: #6c757d;
            margin-bottom: 5px;
        }
        #barcode {
            height: 60px;
        }
        .barcode-value {
            font-family: monospace;
            font-size: 12pt;
            letter-spacing: 2px;
            margin-top: 5px;
        }
        #qrcode {
            width: 120px;
            height: 120px;
            border: 1px solid #dee2e6;
        }

        .parties-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10mm;
        }
        .partie {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 5mm;
        }
        .partie .partie-title {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 4mm;
        }
        .partie .info-line {
            display: grid;
            grid-template-columns: 80px 1fr;
            margin-bottom: 8px;
        }
        .partie .info-line strong {
            font-weight: 600;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-table th, .details-table td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: left;
            font-size: 10pt;
        }
        .details-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .details-table .cod-row {
            font-weight: bold;
            font-size: 12pt;
        }
        .details-table .cod-row td:last-child {
            background-color: #fff3cd;
        }

        .notes-content {
            border: 1px solid #dee2e6;
            padding: 4mm;
            min-height: 50px;
            border-radius: 5px;
            background-color: #f8f9fa;
        }

        .bon-footer {
            margin-top: auto;
            border-top: 1px solid #dee2e6;
            padding-top: 5mm;
        }
        .signatures-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10mm;
            text-align: center;
        }
        .signature-box {
            padding-top: 20mm;
            border-top: 1px solid #6c757d;
        }
        .signature-label {
            font-size: 9pt;
            color: #6c757d;
        }

        @media print {
            @page {
                size: A4;
                margin: 0;
            }
            body {
                background-color: #fff;
            }
            .controls { display: none; }
            .bon-a4 {
                margin: 0;
                box-shadow: none;
                min-height: 297mm;
            }
        }
    </style>
</head>
<body>
    <div class="controls">
        <button class="btn-print" onclick="window.print()">🖨️ Imprimer (A4)</button>
    </div>

    <div class="bon-a4">
        <header class="bon-header">
            <div class="company-info">
                <div class="company-name">AL-AMENA DELIVERY</div>
                <div class="company-details">Service de livraison rapide et sécurisé</div>
            </div>
            <div class="document-info">
                <div class="document-title">BON DE LIVRAISON</div>
                <div class="document-date">Date: {{ $package->created_at->format('d/m/Y') }}</div>
            </div>
        </header>

        <main>
            <section class="codes-section">
                <div class="barcode-container">
                    <div class="code-label">CODE DE SUIVI</div>
                    <canvas id="barcode"></canvas>
                    <div class="barcode-value">{{ $package->package_code }}</div>
                </div>
                <div class="qrcode-container">
                    <div class="code-label">SCANNEZ POUR SUIVRE</div>
                    <div id="qrcode"></div>
                </div>
            </section>

            <section class="parties-section">
                <div class="partie">
                    <div class="partie-title">EXPÉDITEUR</div>
                    <div class="info-line"><strong>Nom:</strong> <span>{{ ($package->supplier_data['name'] ?? $package->sender_data['name']) ?? 'N/A' }}</span></div>
                    <div class="info-line"><strong>Téléphone:</strong> <span>{{ ($package->supplier_data['phone'] ?? $package->sender_data['phone']) ?? 'N/A' }}</span></div>
                    <div class="info-line"><strong>Délégation:</strong> <span>{{ $package->delegationFrom->name ?? 'N/A' }}</span></div>
                    <div class="info-line"><strong>Adresse:</strong> <span>{{ $package->pickup_address ?? 'N/A' }}</span></div>
                </div>
                <div class="partie">
                    <div class="partie-title">DESTINATAIRE</div>
                    <div class="info-line"><strong>Nom:</strong> <span>{{ $package->recipient_data['name'] ?? 'N/A' }}</span></div>
                    <div class="info-line"><strong>Téléphone:</strong> <span>{{ $package->recipient_data['phone'] ?? 'N/A' }}</span></div>
                    <div class="info-line"><strong>Délégation:</strong> <span>{{ $package->delegationTo->name ?? 'N/A' }}</span></div>
                    <div class="info-line"><strong>Adresse:</strong> <span>{{ $package->recipient_data['address'] ?? 'N/A' }}</span></div>
                </div>
            </section>

            <section class="details-section">
                <div class="section-title">DÉTAILS DU COLIS</div>
                <table class="details-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th style="width: 150px;">Valeur</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Contenu du colis</td>
                            <td>{{ $package->content_description }}</td>
                        </tr>
                        @if($package->package_weight)
                        <tr>
                            <td>Poids</td>
                            <td>{{ number_format($package->package_weight, 2) }} kg</td>
                        </tr>
                        @endif
                        <tr class="cod-row">
                            <td>MONTANT À ENCAISSER (COD)</td>
                            <td>{{ number_format($package->cod_amount, 3) }} TND</td>
                        </tr>
                    </tbody>
                </table>
            </section>
            
            @if($package->is_fragile || $package->requires_signature || $package->special_instructions || $package->notes)
            <section class="notes-section">
                 <div class="section-title">INSTRUCTIONS & NOTES</div>
                 <div class="notes-content">
                    @if($package->is_fragile)<strong>FRAGILE</strong><br>@endif
                    @if($package->requires_signature)<strong>SIGNATURE REQUISE</strong><br>@endif
                    {{ $package->special_instructions }}<br>
                    {{ $package->notes }}
                 </div>
            </section>
            @endif
        </main>
        
        <footer class="bon-footer">
            <div class="signatures-section">
                <div class="signature-box">
                    <div class="signature-label">Signature Expéditeur</div>
                </div>
                <div class="signature-box">
                    <div class="signature-label">Signature Livreur</div>
                </div>
                <div class="signature-box">
                    <div class="signature-label">Signature Destinataire</div>
                </div>
            </div>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const packageCode = "{{ $package->package_code }}";
            const trackingUrl = "{{ url('/track/' . $package->package_code) }}";

            // Génération du code-barres (inchangé)
            try {
                JsBarcode("#barcode", packageCode, {
                    format: "CODE128", width: 2, height: 60, displayValue: false
                });
            } catch (e) { console.error("Erreur JsBarcode:", e); }

            // Génération du QR Code (corrigé)
            try {
                const qrContainer = document.getElementById('qrcode');
                if (!qrContainer) return;

                const containerSize = 120; // La taille de notre conteneur en pixels
                const qr = qrcode(0, 'M');
                qr.addData(trackingUrl);
                qr.make();

                // 1. Calculer la taille de module maximale pour que le QR code rentre dans le conteneur
                const moduleCount = qr.getModuleCount();
                const moduleSize = Math.max(1, Math.floor(containerSize / moduleCount));
                
                const qrCanvas = document.createElement('canvas');
                qrCanvas.width = containerSize;
                qrCanvas.height = containerSize;
                const ctx = qrCanvas.getContext('2d');

                // 2. Calculer le décalage pour centrer le QR code dans le canvas
                const actualQrSize = moduleCount * moduleSize;
                const offset = (containerSize - actualQrSize) / 2;

                // 3. Dessiner le QR code centré
                ctx.fillStyle = "#ffffff"; // Fond blanc
                ctx.fillRect(0, 0, containerSize, containerSize);
                
                ctx.save();
                ctx.translate(offset, offset);
                qr.renderTo2dContext(ctx, moduleSize);
                ctx.restore();
                
                qrContainer.innerHTML = ''; // Vider le conteneur
                qrContainer.appendChild(qrCanvas); // Ajouter notre canvas parfait

            } catch (e) { console.error("Erreur QR Code:", e); }
        });
    </script>
</body>
</html>