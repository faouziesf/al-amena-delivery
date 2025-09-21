@props([
    'package' => null,
    'showScanner' => true,
    'showProfile' => true,
    'showEmergency' => true,
    'layout' => 'grid', // grid, row, floating
    'size' => 'default' // small, default, large
])

@php
    $sizeClasses = [
        'small' => 'p-2 text-sm',
        'default' => 'p-3 text-base',
        'large' => 'p-4 text-lg'
    ];

    $iconSizes = [
        'small' => 'w-4 h-4',
        'default' => 'w-5 h-5',
        'large' => 'w-6 h-6'
    ];

    $containerClasses = [
        'grid' => 'grid grid-cols-2 lg:grid-cols-4 gap-3',
        'row' => 'flex flex-wrap gap-2',
        'floating' => 'fixed bottom-6 right-6 z-40'
    ];

    $buttonClass = $sizeClasses[$size] ?? $sizeClasses['default'];
    $iconClass = $iconSizes[$size] ?? $iconSizes['default'];
@endphp

<div {{ $attributes->merge(['class' => $containerClasses[$layout]]) }}
     x-data="actionButtons({
        package: {{ $package ? json_encode($package) : 'null' }},
        showScanner: {{ $showScanner ? 'true' : 'false' }},
        showProfile: {{ $showProfile ? 'true' : 'false' }},
        showEmergency: {{ $showEmergency ? 'true' : 'false' }},
        layout: '{{ $layout }}',
        size: '{{ $size }}'
     })">

    @if($layout === 'floating')
    <!-- Floating Action Button Menu -->
    <div class="relative" x-data="{ open: false }">
        <!-- Main FAB -->
        <button @click="open = !open"
                class="w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center justify-center">
            <svg class="w-6 h-6 transition-transform duration-200" :class="{ 'rotate-45': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
        </button>

        <!-- Action Menu -->
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             @click.away="open = false"
             class="absolute bottom-16 right-0 space-y-3">

            @if($showScanner)
            <!-- Scanner -->
            <button @click="$dispatch('open-scanner'); open = false"
                    class="w-12 h-12 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center justify-center">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"/>
                </svg>
            </button>
            @endif

            <!-- Wallet -->
            <a href="{{ route('deliverer.wallet.index') }}"
               class="w-12 h-12 bg-purple-600 hover:bg-purple-700 text-white rounded-full shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center justify-center">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </a>

            <!-- Packages -->
            <a href="{{ route('deliverer.packages.index') }}"
               class="w-12 h-12 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center justify-center">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </a>

            @if($showEmergency)
            <!-- Emergency -->
            <button @click="handleEmergency()"
                    class="w-12 h-12 bg-red-600 hover:bg-red-700 text-white rounded-full shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center justify-center">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.99-.833-2.732 0L4.08 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </button>
            @endif
        </div>
    </div>

    @else
    <!-- Grid/Row Layout -->

    @if($showScanner)
    <!-- Scanner Button -->
    <button @click="$dispatch('open-scanner')"
            class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold transition-all transform hover:scale-105 shadow-sm hover:shadow-md flex items-center justify-center space-x-2 {{ $buttonClass }}">
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"/>
        </svg>
        @if($layout !== 'row' || $size !== 'small')
        <span>Scanner</span>
        @endif
    </button>
    @endif

    <!-- Packages -->
    <a href="{{ route('deliverer.packages.index') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition-all transform hover:scale-105 shadow-sm hover:shadow-md flex items-center justify-center space-x-2 {{ $buttonClass }}">
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        @if($layout !== 'row' || $size !== 'small')
        <span>Colis</span>
        @endif
    </a>

    <!-- Wallet -->
    <a href="{{ route('deliverer.wallet.index') }}"
       class="bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-semibold transition-all transform hover:scale-105 shadow-sm hover:shadow-md flex items-center justify-center space-x-2 {{ $buttonClass }}">
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
        </svg>
        @if($layout !== 'row' || $size !== 'small')
        <span>Portefeuille</span>
        @endif
    </a>

    @if($showProfile)
    <!-- Profile -->
    <a href="{{ route('deliverer.profile.index') }}"
       class="bg-gray-600 hover:bg-gray-700 text-white rounded-xl font-semibold transition-all transform hover:scale-105 shadow-sm hover:shadow-md flex items-center justify-center space-x-2 {{ $buttonClass }}">
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        @if($layout !== 'row' || $size !== 'small')
        <span>Profil</span>
        @endif
    </a>
    @endif

    <!-- Batch Scanner -->
    <button @click="$dispatch('open-batch-scanner')"
            class="bg-yellow-600 hover:bg-yellow-700 text-white rounded-xl font-semibold transition-all transform hover:scale-105 shadow-sm hover:shadow-md flex items-center justify-center space-x-2 {{ $buttonClass }}">
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        @if($layout !== 'row' || $size !== 'small')
        <span>Lot</span>
        @endif
    </button>

    <!-- RunSheets -->
    <a href="{{ route('deliverer.runsheets.index') }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold transition-all transform hover:scale-105 shadow-sm hover:shadow-md flex items-center justify-center space-x-2 {{ $buttonClass }}">
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        @if($layout !== 'row' || $size !== 'small')
        <span>Feuilles</span>
        @endif
    </a>

    <!-- Notifications -->
    <a href="{{ route('deliverer.notifications.index') }}"
       class="bg-orange-600 hover:bg-orange-700 text-white rounded-xl font-semibold transition-all transform hover:scale-105 shadow-sm hover:shadow-md flex items-center justify-center space-x-2 {{ $buttonClass }} relative">
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.828 4.828a9 9 0 0112.728 0m-2.829 2.829a6 6 0 00-8.485 0m2.829 2.828a3 3 0 004.243 0m-6.072-6.071a12 12 0 016.072 0"/>
        </svg>
        @if($layout !== 'row' || $size !== 'small')
        <span>Alertes</span>
        @endif
        <!-- Notification Badge -->
        <span x-show="notificationCount > 0"
              class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"
              x-text="notificationCount > 9 ? '9+' : notificationCount"></span>
    </a>

    @if($showEmergency)
    <!-- Emergency Button -->
    <button @click="handleEmergency()"
            class="bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold transition-all transform hover:scale-105 shadow-sm hover:shadow-md flex items-center justify-center space-x-2 {{ $buttonClass }}">
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.99-.833-2.732 0L4.08 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
        @if($layout !== 'row' || $size !== 'small')
        <span>Urgence</span>
        @endif
    </button>
    @endif

    @endif
</div>

@push('scripts')
<script>
function actionButtons(options = {}) {
    return {
        // Configuration
        package: options.package || null,
        showScanner: options.showScanner !== false,
        showProfile: options.showProfile !== false,
        showEmergency: options.showEmergency !== false,
        layout: options.layout || 'grid',
        size: options.size || 'default',

        // State
        notificationCount: 0,
        loading: false,

        async init() {
            await this.loadNotificationCount();

            // Check notifications every minute
            setInterval(() => {
                this.loadNotificationCount();
            }, 60000);

            // Listen for notification updates
            this.$watch('notificationCount', (count) => {
                this.updateFavicon(count);
            });
        },

        async loadNotificationCount() {
            try {
                const response = await fetch('/deliverer/api/notifications/unread-count', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.notificationCount = data.count || 0;
                }

            } catch (error) {
                console.error('Erreur chargement notifications:', error);
            }
        },

        async handleEmergency() {
            const confirmed = confirm(
                'Êtes-vous sûr de vouloir déclencher une alerte d\'urgence ?\n\n' +
                'Cette action enverra une notification immédiate au centre de contrôle.'
            );

            if (!confirmed) return;

            this.loading = true;

            try {
                const response = await fetch('/deliverer/emergency/trigger', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify({
                        type: 'general',
                        location: await this.getCurrentLocation(),
                        message: 'Alerte d\'urgence déclenchée depuis l\'application'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert('Alerte d\'urgence envoyée avec succès!\nLe centre de contrôle a été notifié.');
                } else {
                    alert('Erreur lors de l\'envoi de l\'alerte: ' + (data.message || 'Erreur inconnue'));
                }

            } catch (error) {
                console.error('Erreur urgence:', error);
                alert('Erreur de connexion. Veuillez contacter directement le centre de contrôle.');
            } finally {
                this.loading = false;
            }
        },

        async getCurrentLocation() {
            return new Promise((resolve) => {
                if (!navigator.geolocation) {
                    resolve(null);
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        resolve({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                            accuracy: position.coords.accuracy
                        });
                    },
                    (error) => {
                        console.error('Erreur géolocalisation:', error);
                        resolve(null);
                    },
                    {
                        timeout: 5000,
                        enableHighAccuracy: false,
                        maximumAge: 300000 // 5 minutes
                    }
                );
            });
        },

        updateFavicon(count) {
            try {
                const favicon = document.querySelector('link[rel="icon"]');
                if (!favicon) return;

                // Create canvas to draw badge
                const canvas = document.createElement('canvas');
                canvas.width = 32;
                canvas.height = 32;
                const ctx = canvas.getContext('2d');

                // Draw base icon (you might want to load the actual favicon)
                ctx.fillStyle = '#3b82f6';
                ctx.fillRect(0, 0, 32, 32);

                // Draw notification badge
                if (count > 0) {
                    ctx.fillStyle = '#ef4444';
                    ctx.beginPath();
                    ctx.arc(24, 8, 8, 0, 2 * Math.PI);
                    ctx.fill();

                    ctx.fillStyle = 'white';
                    ctx.font = 'bold 10px Arial';
                    ctx.textAlign = 'center';
                    ctx.fillText(count > 9 ? '9+' : count.toString(), 24, 12);
                }

                // Update favicon
                favicon.href = canvas.toDataURL();

            } catch (error) {
                console.error('Erreur mise à jour favicon:', error);
            }
        },

        // Utility methods for package-specific actions
        async acceptPackage(packageId) {
            return this.performPackageAction('accept', packageId);
        },

        async pickupPackage(packageId) {
            return this.performPackageAction('pickup', packageId);
        },

        async deliverPackage(packageId) {
            return this.performPackageAction('deliver', packageId);
        },

        async performPackageAction(action, packageId) {
            try {
                const response = await fetch(`/deliverer/packages/${packageId}/${action}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.$dispatch('package-action-success', { action, packageId, data });
                    return true;
                } else {
                    this.$dispatch('package-action-error', { action, packageId, error: data.message });
                    return false;
                }

            } catch (error) {
                console.error(`Erreur action ${action}:`, error);
                this.$dispatch('package-action-error', { action, packageId, error: error.message });
                return false;
            }
        }
    }
}

// Global event listeners for action buttons
document.addEventListener('alpine:init', () => {
    // Listen for package actions
    document.addEventListener('package-action', async (event) => {
        const { action, package: packageId } = event.detail;

        const actionButtons = Alpine.$data(document.querySelector('[x-data*="actionButtons"]'));
        if (actionButtons) {
            await actionButtons.performPackageAction(action, packageId);
        }
    });

    // Listen for scanner events
    document.addEventListener('scan-package', (event) => {
        const { code } = event.detail;
        Alpine.$dispatch('open-scanner', { mode: 'single', code });
    });
});
</script>
@endpush