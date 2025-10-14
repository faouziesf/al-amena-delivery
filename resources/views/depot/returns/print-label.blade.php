<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bordereau Retour - {{ $returnPackage->return_package_code }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-color: #ea580c;
            --dark-gray: #374151;
            --medium-gray: #6b7280;
            --light-gray: #f3f4f6;
            --border-color: #e5e7eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: var(--dark-gray);
            background-color: #f1f1f1; /* Fond gris clair pour distinguer la page */
        }

        .no-print {
            display: flex;
            justify-content: center;
            padding: 20px;
            gap: 10px;
        }

        .no-print button {
            padding: 12px 24px;
            font-size: 14px;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-print {
            background: var(--brand-color);
            color: white;
        }

        .btn-print:hover {
            background: #c2410c;
        }

        .btn-close {
            background: var(--medium-gray);
            color: white;
        }

        .btn-close:hover {
            background: var(--dark-gray);
        }

        .page-container {
            width: 21cm;
            min-height: 29.7cm;
            padding: 1.5cm;
            margin: 20px auto;
            background: white;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .bordereau {
            border: 1px solid var(--border-color);
            padding: 30px;
        }

        /* --- Header --- */
        .header {
            display: flex;
            flex-direction: column; /* Changed for stacking elements */
            border-bottom: 2px solid var(--brand-color);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            width: 100%;
        }

        .company-info .logo {
            font-size: 24px;
            font-weight: bold;
            color: var(--brand-color);
            margin-bottom: 10px;
        }

        .company-info p {
            font-size: 11px;
            color: var(--medium-gray);
            line-height: 1.4;
        }

        .return-title h1 {
            font-size: 22px;
            text-transform: uppercase;
            text-align: right;
            color: var(--dark-gray);
            margin: 0;
        }

        .return-title .package-code {
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: bold;
            text-align: right;
            background-color: var(--light-gray);
            color: var(--brand-color);
            padding: 8px 12px;
            border-radius: 4px;
            margin-top: 10px;
            display: inline-block;
        }
        
        /* MODIFICATION: Styles for the moved codes section */
        .codes-section {
            margin-top: 25px;
            padding: 20px;
            background-color: var(--light-gray);
            border: 1px solid var(--border-color);
            border-radius: 6px;
        }
        
        .codes-box {
            display: flex;
            gap: 20px;
            justify-content: center;
            align-items: center;
        }
        .code-item {
            text-align: center;
        }
        .code-item svg, .code-item img {
            background: white;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }


        /* --- Main Content Grid --- */
        /* MODIFICATION: Changed to a single column layout */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .info-box {
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 20px;
        }

        .info-box h2 {
            font-size: 16px;
            padding-bottom: 10px;
            margin-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .info-label {
            font-size: 10px;
            color: var(--medium-gray);
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .info-value {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        /* --- Details Section --- */
        .details-section {
            background-color: var(--light-gray);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .details-section h2 {
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .reason-box .info-label {
            font-size: 12px;
            font-weight: bold;
            color: var(--dark-gray);
            margin-bottom: 8px;
        }
        .reason-box .info-value {
            font-size: 13px;
            font-weight: normal;
            background: white;
            padding: 10px;
            border-radius: 4px;
        }

        /* --- Signatures --- */
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        .signature-box {
            text-align: left;
        }

        .signature-label {
            font-size: 12px;
            font-weight: bold;
            color: var(--dark-gray);
            margin-bottom: 8px;
        }

        .signature-area {
            border: 2px dashed var(--border-color);
            height: 100px;
            border-radius: 6px;
            background: #fafafa;
        }
        
        .signature-date {
            margin-top: 8px;
            font-size: 11px;
            color: var(--medium-gray);
        }

        /* --- Footer --- */
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            font-size: 10px;
            color: var(--medium-gray);
        }
        .footer p {
            margin: 0;
        }

        /* --- Print Styles --- */
        @media print {
            body {
                background-color: white;
            }
            .no-print {
                display: none;
            }
            .page-container {
                margin: 0;
                padding: 0;
                box-shadow: none;
                width: 100%;
                min-height: auto;
            }
            .bordereau {
                border: none;
                padding: 0;
            }
            .info-box, .details-section, .codes-section {
                background-color: transparent !important;
            }
            .signature-area {
                background: transparent !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Imprimer le Bordereau</button>
        <button onclick="window.close()" class="btn-close">✕ Fermer</button>
    </div>

    <div class="page-container">
        <div class="bordereau">
            
            <header class="header">
                <div class="header-top">
                    <div class="company-info">
                        <div class="logo">{{ config('app.name', 'Al-Amena Delivery') }}</div>
                        <p>
                            Votre Adresse Complète<br>
                            Ville, Code Postal<br>
                            Tél : +216 XX XXX XXX<br>
                            Email: contact@example.com
                        </p>
                    </div>
                    <div class="return-title">
                        <h1>Bordereau de Retour</h1>
                        <div class="package-code">{{ $returnPackage->return_package_code }}</div>
                    </div>
                </div>

                <div class="codes-section">
                    <div class="codes-box">
                        <div class="code-item">
                            <svg id="barcode"></svg>
                        </div>
                        <div class="code-item">
                            <div id="qrcode"></div>
                        </div>
                    </div>
                </div>
            </header>

            <main>
                <section class="content-grid">
                    <div class="info-box">
                        <h2>🚚 Destinataire (Adresse de Retour)</h2>
                        @if($pickupInfo)
                        <div>
                            <div class="info-label">Nom / Entreprise</div>
                            <div class="info-value">{{ $pickupInfo['name'] }}</div>
                        </div>
                        @if($pickupInfo['contact_name'] && $pickupInfo['contact_name'] !== $pickupInfo['name'])
                        <div>
                            <div class="info-label">Contact</div>
                            <div class="info-value">{{ $pickupInfo['contact_name'] }}</div>
                        </div>
                        @endif
                        <div>
                            <div class="info-label">Téléphone</div>
                            <div class="info-value">
                                {{ $pickupInfo['phone'] }}
                                @if($pickupInfo['tel2'])
                                    / {{ $pickupInfo['tel2'] }}
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="info-label">Adresse de Livraison</div>
                            <div class="info-value">
                                {{ $pickupInfo['address'] }}
                                @if($pickupInfo['city'] || $pickupInfo['postal_code'])
                                    <br>{{ $pickupInfo['city'] }} {{ $pickupInfo['postal_code'] }}
                                @endif
                            </div>
                        </div>
                        @else
                        <div>
                            <div class="info-label">Nom de l'entreprise</div>
                            <div class="info-value">{{ config('app.name', 'Al-Amena Delivery') }}</div>
                        </div>
                        <div>
                            <div class="info-label">Téléphone</div>
                            <div class="info-value">+216 XX XXX XXX</div>
                        </div>
                        <div>
                            <div class="info-label">Adresse de Retour</div>
                            <div class="info-value">Adresse de votre dépôt ou entrepôt<br>Ville, Code Postal</div>
                        </div>
                        @endif
                    </div>
                </section>

                @if($returnPackage->return_reason)
                <section class="details-section">
                    <h2>Détails du Retour</h2>
                    <div class="reason-box">
                        <div class="info-label">📝 Raison du retour :</div>
                        <div class="info-value">{{ $returnPackage->return_reason }}</div>
                    </div>
                </section>
                @endif

                <section class="signatures">
                    <div class="signature-box">
                        <div class="signature-label">Signature Client / Expéditeur</div>
                        <div class="signature-area"></div>
                        <div class="signature-date">Date: ____ / ____ / ________</div>
                    </div>
                    <div class="signature-box">
                        <div class="signature-label">Signature Transporteur / Agent de Dépôt</div>
                        <div class="signature-area"></div>
                        <div class="signature-date">Date: ____ / ____ / ________</div>
                    </div>
                </section>
            </main>

            <footer class="footer">
                <p>Ce document atteste de la prise en charge du colis retourné.</p>
                <p>Bordereau généré le {{ now()->format('d/m/Y à H:i') }} par {{ config('app.name') }}.</p>
            </footer>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const returnCode = "{{ $returnPackage->return_package_code }}";

            // Générer le Code-barres
            JsBarcode("#barcode", returnCode, {
                format: "CODE128",
                width: 2,
                height: 60,
                displayValue: true,
                fontSize: 14,
                margin: 10,
            });

            // Générer le QR Code
            try {
                const qr = qrcode(0, 'M');
                qr.addData(returnCode);
                qr.make();
                document.getElementById('qrcode').innerHTML = qr.createImgTag(4, 8);
            } catch (e) {
                console.error("Erreur de génération du QR Code:", e);
                document.getElementById('qrcode').textContent = "Erreur QR";
            }
        });
    </script>
</body>
</html>