@props([
    'transaction',
    'showDetails' => true,
    'compact' => false
])

<div class="p-{{ $compact ? '4' : '6' }} hover:bg-gray-50 transition-colors {{ $compact ? '' : 'border-b border-gray-200 last:border-b-0' }}">
    <div class="flex items-start justify-between">
        <!-- Icône et détails -->
        <div class="flex items-start">
            <!-- Icône du type de transaction -->
            <div class="flex-shrink-0 mr-{{ $compact ? '3' : '4' }}">
                @if($transaction->amount > 0)
                    <div class="w-{{ $compact ? '8' : '12' }} h-{{ $compact ? '8' : '12' }} bg-green-100 rounded-full flex items-center justify-center">
                        @if($transaction->type === 'PACKAGE_PAYMENT')
                            <svg class="w-{{ $compact ? '4' : '6' }} h-{{ $compact ? '4' : '6' }} text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        @elseif($transaction->type === 'CREDIT')
                            <svg class="w-{{ $compact ? '4' : '6' }} h-{{ $compact ? '4' : '6' }} text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        @else
                            <svg class="w-{{ $compact ? '4' : '6' }} h-{{ $compact ? '4' : '6' }} text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                            </svg>
                        @endif
                    </div>
                @else
                    <div class="w-{{ $compact ? '8' : '12' }} h-{{ $compact ? '8' : '12' }} bg-red-100 rounded-full flex items-center justify-center">
                        @if($transaction->type === 'WITHDRAWAL')
                            <svg class="w-{{ $compact ? '4' : '6' }} h-{{ $compact ? '4' : '6' }} text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        @elseif($transaction->type === 'DEBIT')
                            <svg class="w-{{ $compact ? '4' : '6' }} h-{{ $compact ? '4' : '6' }} text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        @else
                            <svg class="w-{{ $compact ? '4' : '6' }} h-{{ $compact ? '4' : '6' }} text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                            </svg>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Détails de la transaction -->
            <div class="flex-1">
                <div class="flex items-center mb-1">
                    <h4 class="text-{{ $compact ? 'base' : 'lg' }} font-semibold text-gray-900 mr-3">
                        {{ Str::limit($transaction->description, $compact ? 40 : 60) }}
                    </h4>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $transaction->status_color }}">
                        {{ $transaction->status_display }}
                    </span>
                </div>

                @if($showDetails && !$compact)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2 text-sm text-gray-600">
                        <div>
                            <span class="font-medium">ID:</span>
                            <span class="font-mono text-xs">{{ Str::limit($transaction->transaction_id, 20) }}</span>
                        </div>

                        <div>
                            <span class="font-medium">Type:</span>
                            {{ $transaction->type_display }}
                        </div>

                        @if($transaction->package_id)
                            <div>
                                <span class="font-medium">Colis:</span>
                                @if(Route::has('client.packages.show'))
                                    <a href="{{ route('client.packages.show', $transaction->package_id) }}" 
                                       class="text-blue-600 hover:text-blue-800">
                                        {{ $transaction->package->package_code ?? '#'.$transaction->package_id }}
                                    </a>
                                @else
                                    <span class="text-blue-600">
                                        {{ $transaction->package->package_code ?? '#'.$transaction->package_id }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        @if($transaction->completed_at)
                            <div>
                                <span class="font-medium">Traité:</span>
                                {{ $transaction->completed_at->format('d/m/Y H:i') }}
                            </div>
                        @endif
                    </div>

                    @if($transaction->wallet_balance_before !== null && $transaction->wallet_balance_after !== null)
                        <div class="mt-2 text-xs text-gray-500">
                            Solde: {{ number_format($transaction->wallet_balance_before, 3) }} DT 
                            → {{ number_format($transaction->wallet_balance_after, 3) }} DT
                        </div>
                    @endif
                @endif

                @if($compact)
                    <div class="flex items-center mt-1 text-xs text-gray-500">
                        <span class="mr-2">{{ $transaction->type_display }}</span>
                        @if($transaction->package_id)
                            <span class="mr-2">• Colis: {{ $transaction->package->package_code ?? '#'.$transaction->package_id }}</span>
                        @endif
                        <span>• {{ $transaction->created_at->format('d/m H:i') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Montant et date -->
        <div class="text-right flex-shrink-0 ml-4">
            <p class="text-{{ $compact ? 'lg' : 'xl' }} font-bold {{ $transaction->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ $transaction->formatted_amount }}
            </p>
            @if(!$compact)
                <p class="text-sm text-gray-500 mt-1">
                    {{ $transaction->created_at->format('d/m/Y') }}
                </p>
                <p class="text-xs text-gray-400">
                    {{ $transaction->created_at->format('H:i:s') }}
                </p>
            @else
                <p class="text-xs text-gray-500 mt-1">
                    {{ $transaction->created_at->diffForHumans() }}
                </p>
            @endif
        </div>
    </div>

    <!-- Métadonnées supplémentaires (version étendue seulement) -->
    @if($showDetails && !$compact && $transaction->metadata && is_array($transaction->metadata) && count($transaction->metadata) > 0)
        <div class="mt-4 p-3 bg-gray-50 rounded-lg">
            <p class="text-xs font-medium text-gray-700 mb-2">Détails supplémentaires:</p>
            <div class="text-xs text-gray-600 space-y-1">
                @foreach($transaction->metadata as $key => $value)
                    <div class="flex justify-between">
                        <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                        <span>{{ is_array($value) ? json_encode($value) : Str::limit($value, 50) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Actions rapides (version étendue seulement) -->
    @if($showDetails && !$compact)
        <div class="mt-3 flex items-center space-x-3">
            @if($transaction->package_id && Route::has('client.packages.show'))
                <a href="{{ route('client.packages.show', $transaction->package_id) }}" 
                   class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                    📦 Voir le colis
                </a>
            @endif
            
            @if($transaction->status === 'FAILED')
                <span class="text-xs text-red-600 font-medium">❌ Échec</span>
            @elseif($transaction->status === 'PENDING')
                <span class="text-xs text-orange-600 font-medium">⏳ En attente</span>
            @endif
        </div>
    @endif
</div>