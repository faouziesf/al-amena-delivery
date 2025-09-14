@props(['status', 'size' => 'sm'])

@php
    $statusConfig = [
        'CREATED' => [
            'label' => 'Créé',
            'color' => 'blue',
            'bgColor' => 'bg-blue-100',
            'textColor' => 'text-blue-800',
            'ringColor' => 'ring-blue-600/10'
        ],
        'AVAILABLE' => [
            'label' => 'Disponible',
            'color' => 'indigo', 
            'bgColor' => 'bg-indigo-100',
            'textColor' => 'text-indigo-800',
            'ringColor' => 'ring-indigo-600/10'
        ],
        'ACCEPTED' => [
            'label' => 'Accepté',
            'color' => 'purple',
            'bgColor' => 'bg-purple-100', 
            'textColor' => 'text-purple-800',
            'ringColor' => 'ring-purple-600/10'
        ],
        'PICKED_UP' => [
            'label' => 'Collecté',
            'color' => 'orange',
            'bgColor' => 'bg-orange-100',
            'textColor' => 'text-orange-800', 
            'ringColor' => 'ring-orange-600/10'
        ],
        'DELIVERED' => [
            'label' => 'Livré',
            'color' => 'green',
            'bgColor' => 'bg-green-100',
            'textColor' => 'text-green-800',
            'ringColor' => 'ring-green-600/20'
        ],
        'PAID' => [
            'label' => 'Payé',
            'color' => 'emerald',
            'bgColor' => 'bg-emerald-100',
            'textColor' => 'text-emerald-800', 
            'ringColor' => 'ring-emerald-600/20'
        ],
        'REFUSED' => [
            'label' => 'Refusé',
            'color' => 'yellow',
            'bgColor' => 'bg-yellow-100',
            'textColor' => 'text-yellow-800',
            'ringColor' => 'ring-yellow-600/20'
        ],
        'RETURNED' => [
            'label' => 'Retourné',
            'color' => 'red',
            'bgColor' => 'bg-red-100',
            'textColor' => 'text-red-800',
            'ringColor' => 'ring-red-600/10'
        ],
        'UNAVAILABLE' => [
            'label' => 'Non disponible',
            'color' => 'amber',
            'bgColor' => 'bg-amber-100',
            'textColor' => 'text-amber-800',
            'ringColor' => 'ring-amber-600/20'
        ],
        'VERIFIED' => [
            'label' => 'Vérifié',
            'color' => 'violet',
            'bgColor' => 'bg-violet-100',
            'textColor' => 'text-violet-800', 
            'ringColor' => 'ring-violet-600/10'
        ],
        'CANCELLED' => [
            'label' => 'Annulé',
            'color' => 'gray',
            'bgColor' => 'bg-gray-100',
            'textColor' => 'text-gray-800',
            'ringColor' => 'ring-gray-600/10'
        ]
    ];

    $config = $statusConfig[$status] ?? $statusConfig['CREATED'];
    
    $sizeClasses = match($size) {
        'xs' => 'px-2 py-1 text-xs',
        'sm' => 'px-2.5 py-1.5 text-xs',
        'md' => 'px-3 py-2 text-sm',
        'lg' => 'px-4 py-2.5 text-sm',
        default => 'px-2.5 py-1.5 text-xs'
    };
@endphp

<span {{ $attributes->merge([
    'class' => "inline-flex items-center rounded-full font-medium ring-1 ring-inset {$config['bgColor']} {$config['textColor']} {$config['ringColor']} {$sizeClasses}"
]) }}>
    <!-- Dot indicator -->
    <svg class="h-1.5 w-1.5 mr-1.5" fill="currentColor" viewBox="0 0 6 6" aria-hidden="true">
        <circle cx="3" cy="3" r="3" />
    </svg>
    
    {{ $config['label'] }}
</span>