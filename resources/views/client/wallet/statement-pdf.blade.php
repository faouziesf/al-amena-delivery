<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relevé de compte - {{ $user->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }
        
        .header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-info {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .company-tagline {
            font-size: 14px;
            color: #6b7280;
        }
        
        .statement-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 20px;
        }
        
        .account-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .account-left, .account-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 15px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        
        .account-left {
            border-right: none;
        }
        
        .info-label {
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: #6b7280;
            margin-bottom: 10px;
        }
        
        .period-info {
            text-align: center;
            background: #eff6ff;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #dbeafe;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .stat-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 15px 10px;
            border: 1px solid #e2e8f0;
        }
        
        .stat-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
        }
        
        .stat-credit {
            color: #16a34a;
        }
        
        .stat-debit {
            color: #dc2626;
        }
        
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .transactions-table th {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 8px;
            font-weight: bold;
            text-align: left;
            font-size: 11px;
            color: #374151;
        }
        
        .transactions-table td {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            font-size: 10px;
            vertical-align: top;
        }
        
        .transaction-row:nth-child(even) {
            background: #fafbfc;
        }
        
        .amount-credit {
            color: #16a34a;
            font-weight: bold;
        }
        
        .amount-debit {
            color: #dc2626;
            font-weight: bold;
        }
        
        .status-completed {
            color: #16a34a;
            font-size: 9px;
            background: #dcfce7;
            padding: 1px 4px;
            border-radius: 2px;
        }
        
        .status-pending {
            color: #ea580c;
            font-size: 9px;
            background: #fed7aa;
            padding: 1px 4px;
            border-radius: 2px;
        }
        
        .status-failed {
            color: #dc2626;
            font-size: 9px;
            background: #fee2e2;
            padding: 1px 4px;
            border-radius: 2px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 10px;
            color: #6b7280;
        }
        
        .footer-info {
            text-align: center;
            line-height: 1.6;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .no-transactions {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
            font-style: italic;
        }
        
        .balance-summary {
            background: #f0f9ff;
            border: 2px solid #0ea5e9;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .current-balance {
            font-size: 18px;
            font-weight: bold;
            color: #0369a1;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <div class="company-info">
            <div class="company-name">{{ config('app.name', 'Système de Livraison') }}</div>
            <div class="company-tagline">Plateforme de livraison avec portefeuille intégré</div>
        </div>
        
        <div class="statement-title">RELEVÉ DE COMPTE PORTEFEUILLE</div>
    </div>

    <!-- Informations du compte -->
    <div class="account-info">
        <div class="account-left">
            <div class="info-label">Titulaire du compte</div>
            <div class="info-value">{{ $user->name }}</div>
            
            <div class="info-label">Email</div>
            <div class="info-value">{{ $user->email }}</div>
            
            @if($user->clientProfile)
                <div class="info-label">Entreprise</div>
                <div class="info-value">{{ $user->clientProfile->shop_name ?? 'N/A' }}</div>
            @endif
        </div>
        
        <div class="account-right">
            <div class="info-label">Date d'édition</div>
            <div class="info-value">{{ now()->format('d/m/Y à H:i') }}</div>
            
            <div class="info-label">Numéro client</div>
            <div class="info-value">#{{ str_pad($user->id, 6, '0', STR_PAD_LEFT) }}</div>
            
            <div class="info-label">Type de compte</div>
            <div class="info-value">Client {{ $user->isVerified() ? 'Vérifié' : 'En attente' }}</div>
        </div>
    </div>

    <!-- Période du relevé -->
    <div class="period-info">
        <strong>Période du relevé:</strong> Du {{ $stats['period_from'] }} au {{ $stats['period_to'] }}
    </div>

    <!-- Solde actuel -->
    <div class="balance-summary">
        <div class="info-label">SOLDE ACTUEL DU PORTEFEUILLE</div>
        <div class="current-balance">{{ number_format($stats['balance'], 3, ',', ' ') }} DT</div>
    </div>

    <!-- Statistiques -->
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-label">Total crédité</div>
            <div class="stat-value stat-credit">+{{ number_format($stats['total_credits'], 3, ',', ' ') }} DT</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Total débité</div>
            <div class="stat-value stat-debit">-{{ number_format($stats['total_debits'], 3, ',', ' ') }} DT</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Solde net période</div>
            <div class="stat-value {{ ($stats['total_credits'] - $stats['total_debits']) >= 0 ? 'stat-credit' : 'stat-debit' }}">
                {{ number_format($stats['total_credits'] - $stats['total_debits'], 3, ',', ' ') }} DT
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Nb transactions</div>
            <div class="stat-value">{{ $stats['transaction_count'] }}</div>
        </div>
    </div>

    <!-- Tableau des transactions -->
    @if($transactions->count() > 0)
        <table class="transactions-table">
            <thead>
                <tr>
                    <th style="width: 15%">Date</th>
                    <th style="width: 15%">ID Transaction</th>
                    <th style="width: 35%">Description</th>
                    <th style="width: 10%">Type</th>
                    <th style="width: 15%">Montant (DT)</th>
                    <th style="width: 10%">Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $transaction)
                    <tr class="transaction-row">
                        <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                        <td style="font-family: monospace; font-size: 9px;">{{ $transaction->transaction_id }}</td>
                        <td>
                            {{ $transaction->description }}
                            @if($transaction->package_id)
                                <br><small style="color: #6b7280;">Colis: {{ $transaction->package->package_code ?? '#'.$transaction->package_id }}</small>
                            @endif
                        </td>
                        <td>{{ $transaction->type_display }}</td>
                        <td class="{{ $transaction->amount >= 0 ? 'amount-credit' : 'amount-debit' }}" style="text-align: right;">
                            {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 3, ',', ' ') }}
                        </td>
                        <td>
                            <span class="status-{{ strtolower($transaction->status) }}">
                                {{ $transaction->status_display }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-transactions">
            Aucune transaction trouvée pour cette période.
        </div>
    @endif

    <!-- Pied de page -->
    <div class="footer">
        <div class="footer-info">
            <p><strong>{{ config('app.name', 'Système de Livraison') }}</strong></p>
            <p>Relevé généré automatiquement le {{ now()->format('d/m/Y à H:i') }}</p>
            <p>Ce document est un relevé officiel de votre portefeuille électronique</p>
            <p style="margin-top: 10px; font-size: 9px; color: #9ca3af;">
                En cas de questions sur ce relevé, contactez notre service client.<br>
                Les transactions sont listées par ordre chronologique décroissant.
            </p>
        </div>
    </div>
</body>
</html>