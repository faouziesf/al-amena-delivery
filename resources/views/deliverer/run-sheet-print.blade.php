<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Run Sheet - {{ Auth::user()->name }}</title>
    <style>
        @media print {
            body { margin: 0; padding: 20px; }
            .no-print { display: none; }
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .info-section {
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th, table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .signature-box {
            margin-top: 30px;
            border: 1px solid #000;
            padding: 10px;
            min-height: 80px;
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimer</button>
        <button onclick="window.close()" class="btn btn-secondary">✖ Fermer</button>
    </div>

    <div class="header">
        <h1>🚚 FEUILLE DE ROUTE (RUN SHEET)</h1>
        <p><strong>Date:</strong> {{ now()->format('d/m/Y') }}</p>
        <p><strong>Livreur:</strong> {{ Auth::user()->name }}</p>
        <p><strong>Secteur:</strong> {{ $sector }}</p>
    </div>

    <div class="info-section">
        <strong>Total de colis:</strong> {{ $packages->count() }}<br>
        <strong>Montant COD total:</strong> {{ number_format($packages->sum('cod_amount'), 3) }} DT
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Code Colis</th>
                <th>Destinataire</th>
                <th>Téléphone</th>
                <th>Adresse</th>
                <th>COD (DT)</th>
                <th>Signature</th>
            </tr>
        </thead>
        <tbody>
            @foreach($packages as $index => $package)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $package->tracking_number ?? $package->package_code }}</strong></td>
                    <td>{{ $package->recipient_name }}</td>
                    <td>{{ $package->recipient_phone }}</td>
                    <td>{{ $package->recipient_address }}, {{ $package->recipient_city }}</td>
                    <td><strong>{{ number_format($package->cod_amount, 3) }}</strong></td>
                    <td style="width: 100px;"></td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" style="text-align: right;">TOTAL:</th>
                <th><strong>{{ number_format($packages->sum('cod_amount'), 3) }} DT</strong></th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <div class="signature-box">
        <p><strong>Signature du Livreur:</strong></p>
        <br><br>
        <p>Date: _________________ Signature: _________________</p>
    </div>

    <script>
        // Auto-print on load
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
