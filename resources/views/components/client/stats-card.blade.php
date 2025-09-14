@props(['title', 'value', 'subtitle' => null, 'icon', 'color' => 'purple', 'trend' => null, 'href' => null])

@php
    $colorClasses = [
        'purple' => 'bg-purple-gradient',
        'blue' => 'bg-blue-500',
        'green' => 'bg-green-500', 
        'orange' => 'bg-orange-500',
        'red' => 'bg-red-500',
        'indigo' => 'bg-indigo-500',
        'pink' => 'bg-pink-500',
        'gray' => 'bg-gray-500'
    ];
    
    $bgClass = $colorClasses[$color] ?? $colorClasses['purple'];
    
    $cardElement = $href ? 'a' : 'div';
    $cardAttributes = $href ? ['href' => $href] : [];
@endphp

<{{ $cardElement }} 
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'bg-white rounded-2xl shadow-sm border border-purple-100 p-6 card-hover']) }}>
    
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <div class="h-12 w-12 rounded-xl {{ $bgClass }} flex items-center justify-center shadow-md">
                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    {!! $icon !!}
                </svg>
            </div>
        </div>
        
        <div class="ml-4 flex-1">
            <p class="text-sm font-medium text-gray-500">{{ $title }}</p>
            
            <div class="flex items-baseline">
                <p class="text-2xl font-bold text-{{ $color }}-900">{{ $value }}</p>
                
                @if($trend)
                    @php
                        $trendValue = $trend['value'];
                        $trendDirection = $trend['direction']; // 'up', 'down', 'neutral'
                        $trendColor = match($trendDirection) {
                            'up' => 'text-green-600',
                            'down' => 'text-red-600', 
                            'neutral' => 'text-gray-600',
                            default => 'text-gray-600'
                        };
                        $trendIcon = match($trendDirection) {
                            'up' => 'M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941',
                            'down' => 'M2.25 6L9 12.75l4.306-4.307a11.95 11.95 0 015.814 5.519l2.74 1.22m0 0l-5.94 2.28m5.94-2.28l-2.28-5.941',
                            default => 'M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15'
                        };
                    @endphp
                    
                    <span class="ml-2 flex items-center text-sm {{ $trendColor }}">
                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $trendIcon }}" />
                        </svg>
                        {{ $trendValue }}
                    </span>
                @endif
            </div>
            
            @if($subtitle)
                <p class="text-xs text-gray-600 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
        
        @if($href)
            <div class="ml-2">
                <svg class="h-5 w-5 text-gray-400 group-hover:text-{{ $color }}-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </div>
        @endif
    </div>
</{{ $cardElement }}>