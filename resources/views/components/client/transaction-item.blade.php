@props(['transaction'])

@php
    $isCredit = $transaction->amount > 0;
    $amount = abs($transaction->amount);
    
    $typeLabels = [
        'PACKAGE_CREATION_DEBIT' => 'Création colis',
        'PACKAGE_DELIVERY_CREDIT' => 'Livraison colis',
        'WALLET_RECHARGE' => 'Recharge wallet',
        'WALLET_WITHDRAWAL' => 'Retrait wallet',
        'REFUND' => 'Remboursement',
        'ADJUSTMENT' => 'Ajustement',
        'COD_COLLECTION' => 'Collecte COD',
        'PACKAGE_RETURN' => 'Retour colis'
    ];
    
    $typeIcons = [
        'PACKAGE_CREATION_DEBIT' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5',
        'PACKAGE_DELIVERY_CREDIT' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'WALLET_RECHARGE' => 'M12 4.5v15m7.5-7.5h-15',
        'WALLET_WITHDRAWAL' => 'M19.5 12h-15',
        'REFUND' => 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99',
        'ADJUSTMENT' => 'M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m0 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5',
        'COD_COLLECTION' => 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H4.5m-1.5 0H3c.621 0 1.125.504 1.125 1.125v.375M3.75 15h-.75v.75c0 .621.504 1.125 1.125 1.125h.75m0-1.5v.375c0 .621.504 1.125 1.125 1.125H6.75m-3 0H4.5c-.621 0-1.125-.504-1.125-1.125V15m0 0h-.75',
        'PACKAGE_RETURN' => 'M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3'
    ];
    
    $label = $typeLabels[$transaction->type] ?? 'Transaction';
    $iconPath = $typeIcons[$transaction->type] ?? $typeIcons['ADJUSTMENT'];
@endphp

<div class="flex items-center justify-between py-2">
    <div class="flex items-center min-w-0 flex-1">
        <!-- Icon -->
        <div class="flex-shrink-0">
            <div class="h-8 w-8 rounded-lg {{ $isCredit ? 'bg-green-100' : 'bg-red-100' }} flex items-center justify-center">
                <svg class="h-4 w-4 {{ $isCredit ? 'text-green-600' : 'text-red-600' }}" 
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" />
                </svg>
            </div>
        </div>
        
        <!-- Details -->
        <div class="ml-3 min-w-0 flex-1">
            <p class="text-sm font-medium text-gray-900 truncate">
                {{ $label }}
            </p>
            <div class="flex items-center text-xs text-gray-500">
                <span>{{ $transaction->completed_at->format('d/m/Y H:i') }}</span>
                
                @if($transaction->package)
                    <span class="mx-1">•</span>
                    <span class="truncate">{{ $transaction->package->package_code }}</span>
                @endif
                
                @if($transaction->reference)
                    <span class="mx-1">•</span>
                    <span class="truncate">{{ $transaction->reference }}</span>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Amount -->
    <div class="flex-shrink-0 ml-4">
        <p class="text-sm font-semibold {{ $isCredit ? 'text-green-900' : 'text-red-900' }}">
            {{ $isCredit ? '+' : '-' }}{{ number_format($amount, 3) }} DT
        </p>
        
        @if($transaction->description)
            <p class="text-xs text-gray-500 text-right truncate max-w-24" 
               title="{{ $transaction->description }}">
                {{ Str::limit($transaction->description, 15) }}
            </p>
        @endif
    </div>
</div>