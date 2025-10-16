<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>@yield('title', 'Dashboard') - Al-Amena Livreur</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA Meta -->
    <meta name="theme-color" content="#2563EB">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    <link rel="icon" type="image/png" href="/images/icons/icon-32x32.png">

    <!-- Scripts & Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [data-tailwind-warning] { display: none !important; }

        /* MODERN SOFT UI DESIGN SYSTEM */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        html {
            scroll-behavior: smooth;
            -webkit-tap-highlight-color: transparent;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-optical-sizing: auto;
        }

        /* CSS Variables - Advanced Modern Color System */
        :root {
            /* Palette Principale Modernisée */
            --primary: #6366F1;
            --primary-light: #818CF8;
            --primary-dark: #4F46E5;
            --accent: #06B6D4;
            --accent-light: #67E8F9;
            --accent-dark: #0891B2;

            /* Couleurs Sémantiques */
            --success: #10B981;
            --success-light: #34D399;
            --error: #EF4444;
            --error-light: #F87171;
            --warning: #F59E0B;
            --warning-light: #FBBF24;
            --info: #3B82F6;
            --info-light: #60A5FA;

            /* Gradients Modernes */
            --gradient-primary: linear-gradient(135deg, var(--primary), var(--primary-light));
            --gradient-accent: linear-gradient(135deg, var(--accent), var(--accent-light));
            --gradient-success: linear-gradient(135deg, var(--success), var(--success-light));
            --gradient-glass: linear-gradient(135deg, rgba(255,255,255,0.25), rgba(255,255,255,0.05));
            --gradient-surface: linear-gradient(135deg, #FFFFFF, #F8FAFC);

            /* Surfaces avec profondeur */
            --surface: #FFFFFF;
            --surface-elevated: #FAFBFC;
            --surface-glass: rgba(255, 255, 255, 0.85);
            --background: linear-gradient(135deg, #F1F5F9, #E2E8F0);
            --background-pattern: radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.5) 0%, transparent 50%);

            /* Thème sombre */
            --background-dark: linear-gradient(135deg, #0F172A, #1E293B);
            --surface-dark: #1E293B;
            --surface-dark-elevated: #334155;

            /* Texte avec contraste amélioré */
            --text-primary: #0F172A;
            --text-secondary: #475569;
            --text-muted: #64748B;
            --text-light: #94A3B8;
            --text-on-dark: #F1F5F9;

            /* Ombres sophistiquées */
            --shadow-xs: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-soft: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-medium: 0 10px 40px rgba(0, 0, 0, 0.12);
            --shadow-strong: 0 20px 60px rgba(0, 0, 0, 0.16);
            --shadow-glass: 0 8px 32px rgba(31, 38, 135, 0.15);
            --shadow-colored: 0 8px 32px rgba(99, 102, 241, 0.25);

            /* Bordures */
            --border-light: rgba(0, 0, 0, 0.06);
            --border-medium: rgba(0, 0, 0, 0.12);
            --border-glass: rgba(255, 255, 255, 0.18);

            /* Rayons de bordure modernes */
            --radius-xs: 8px;
            --radius-sm: 12px;
            --radius-md: 16px;
            --radius-lg: 20px;
            --radius-xl: 24px;
            --radius-2xl: 32px;

            /* Safe Areas */
            --safe-area-inset-top: env(safe-area-inset-top);
            --safe-area-inset-bottom: env(safe-area-inset-bottom);
        }

        /* Dark Mode Support */
        @media (prefers-color-scheme: dark) {
            :root {
                --surface: var(--surface-dark);
                --background: var(--background-dark);
                --text-primary: #F7FAFC;
                --text-secondary: #CBD5E0;
                --text-muted: #A0AEC0;
            }
        }

        .safe-top {
            padding-top: max(1rem, env(safe-area-inset-top));
        }

        .safe-bottom {
            padding-bottom: max(1rem, env(safe-area-inset-bottom));
        }

        /* Body avec safe area */
        body {
            padding-top: env(safe-area-inset-top);
            padding-bottom: env(safe-area-inset-bottom);
        }

        /* Page wrapper */
        .page-wrapper {
            min-height: calc(100vh - env(safe-area-inset-top) - env(safe-area-inset-bottom));
        }

        /* Composants UI Modernes */
        .soft-card {
            background: var(--gradient-surface);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-soft);
            border: 1px solid var(--border-light);
            backdrop-filter: blur(20px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .soft-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--gradient-glass);
            opacity: 0.6;
        }

        .soft-card:hover {
            box-shadow: var(--shadow-medium);
            transform: translateY(-4px) scale(1.02);
            border-color: var(--border-medium);
        }

        .glass-card {
            background: var(--surface-glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-glass);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-glass);
        }

        .soft-button {
            border-radius: var(--radius-md);
            font-weight: 600;
            letter-spacing: 0.025em;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-xs);
            position: relative;
            overflow: hidden;
        }

        .soft-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .soft-button:hover::before {
            left: 100%;
        }

        .soft-button:active {
            transform: scale(0.95);
            box-shadow: var(--shadow-xs);
        }

        .primary-button {
            background: var(--gradient-primary);
            color: white;
            box-shadow: var(--shadow-colored);
        }

        .accent-button {
            background: var(--gradient-accent);
            color: white;
        }

        /* Micro-interactions avancées */
        .interactive {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .interactive:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: var(--shadow-medium);
        }

        .interactive:active {
            transform: scale(0.96);
            transition: transform 0.1s ease;
        }

        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-strong);
        }

        .hover-glow:hover {
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
        }

        .ripple {
            position: relative;
            overflow: hidden;
        }

        .ripple::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .ripple:active::after {
            width: 300px;
            height: 300px;
        }

        /* Animations sophistiquées */
        .fade-in {
            animation: fadeIn 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .fade-in-up {
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .slide-up {
            animation: slideUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .slide-in-right {
            animation: slideInRight 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .scale-in {
            animation: scaleIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .bounce-in {
            animation: bounceIn 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .pulse-soft {
            animation: pulseSoft 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .float {
            animation: float 6s ease-in-out infinite;
        }

        .shimmer {
            animation: shimmer 2s linear infinite;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes bounceIn {
            0% { opacity: 0; transform: scale(0.3); }
            50% { opacity: 1; transform: scale(1.1); }
            100% { opacity: 1; transform: scale(1); }
        }

        @keyframes pulseSoft {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.03); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        @keyframes shimmer {
            0% { background-position: -468px 0; }
            100% { background-position: 468px 0; }
        }

        /* Navigation moderne avec indicateurs */
        .tab-indicator {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            height: 4px;
            background: var(--gradient-primary);
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.4);
        }

        .nav-item {
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-item::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--gradient-accent);
            border-radius: 2px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateX(-50%);
        }

        .nav-item:hover::before {
            width: 80%;
        }

        .nav-item.active::before {
            width: 100%;
            height: 3px;
        }

        /* Menu burger animation */
        .menu-line {
            transition: all 0.3s ease;
            transform-origin: center;
        }

        .menu-open .line1 { transform: rotate(45deg) translate(5px, 5px); }
        .menu-open .line2 { opacity: 0; }
        .menu-open .line3 { transform: rotate(-45deg) translate(7px, -6px); }

        /* Overlay */
        .menu-overlay {
            backdrop-filter: blur(8px);
            transition: all 0.3s ease;
        }

        /* Navigation inférieure moderne */
        .bottom-nav-shadow {
            box-shadow: var(--shadow-strong);
            backdrop-filter: blur(20px);
            border-top: 1px solid var(--border-glass);
        }

        .bottom-nav-blur {
            background: var(--surface-glass);
            backdrop-filter: blur(24px) saturate(180%);
        }

        .nav-background {
            background: linear-gradient(180deg,
                rgba(255, 255, 255, 0.95) 0%,
                rgba(255, 255, 255, 1) 100%);
        }

        /* Safe mobile padding */
        .pb-safe-mobile {
            padding-bottom: max(5rem, calc(5rem + env(safe-area-inset-bottom)));
        }

        @media (min-width: 768px) {
            .pb-safe-mobile {
                padding-bottom: 1rem;
            }
        }

        /* Espacement du contenu pour mobile */
        .content-mobile-spacing {
            margin-bottom: max(6rem, calc(6rem + env(safe-area-inset-bottom)));
        }

        @media (min-width: 768px) {
            .content-mobile-spacing {
                margin-bottom: 0;
            }
        }

        /* Glassmorphism avancé */
        .glass {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .glass-dark {
            background: rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .glass-colorful {
            background: linear-gradient(135deg,
                rgba(99, 102, 241, 0.15),
                rgba(6, 182, 212, 0.15));
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Effets visuels avancés */
        .gradient-border {
            position: relative;
            background: var(--surface);
            border-radius: var(--radius-lg);
        }

        .gradient-border::before {
            content: '';
            position: absolute;
            inset: 0;
            padding: 2px;
            background: var(--gradient-primary);
            border-radius: inherit;
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask-composite: xor;
            -webkit-mask-composite: xor;
        }

        .neon-glow {
            box-shadow:
                0 0 5px rgba(99, 102, 241, 0.5),
                0 0 10px rgba(99, 102, 241, 0.3),
                0 0 15px rgba(99, 102, 241, 0.2),
                0 0 20px rgba(99, 102, 241, 0.1);
        }

        .text-gradient {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50" x-data="{ menuOpen: false, loading: false }">

    <!-- Header Corrigé -->
    <header class="sticky top-0 z-40 bg-blue-600 shadow-lg safe-top">
        <div class="flex items-center justify-between px-4 py-3">
            <!-- Menu Burger Simplifié -->
            <button @click="menuOpen = !menuOpen"
                    class="w-10 h-10 rounded-lg bg-blue-700 hover:bg-blue-800 flex items-center justify-center transition-colors"
                    :class="{ 'menu-open': menuOpen }">
                <div class="w-5 h-5 flex flex-col justify-center space-y-1">
                    <span class="menu-line line1 block h-0.5 w-5 bg-white rounded transition-all"></span>
                    <span class="menu-line line2 block h-0.5 w-5 bg-white rounded transition-all"></span>
                    <span class="menu-line line3 block h-0.5 w-5 bg-white rounded transition-all"></span>
                </div>
            </button>

            <!-- Titre -->
            <div class="flex-1 text-center">
                <h1 class="text-lg font-bold text-white truncate">@yield('title', 'Al-Amena')</h1>
            </div>

            <!-- Scanner Multiple -->
            <a href="{{ route('deliverer.scan.multi') }}"
               class="w-10 h-10 rounded-lg bg-blue-700 hover:bg-blue-800 flex items-center justify-center transition-colors">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
            </a>
        </div>
    </header>

    <!-- Menu overlay -->
    <div x-show="menuOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="menuOpen = false"
         class="fixed inset-0 bg-black bg-opacity-50 menu-overlay z-50"
         style="display: none;">
    </div>

    <!-- Menu Sidebar Corrigé avec Safe Areas iPhone -->
    <div x-show="menuOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed top-0 left-0 h-full w-80 bg-white shadow-xl z-50 overflow-y-auto"
         style="display: none; padding-bottom: calc(1rem + env(safe-area-inset-bottom));">

        <!-- Menu Header -->
        <div class="bg-blue-600 text-white p-4 safe-top">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">{{ auth()->user()->name }}</h2>
                    <p class="text-blue-200 text-sm">Livreur</p>
                </div>
                <button @click="menuOpen = false" class="p-2 hover:bg-blue-700 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Navigation menu -->
        <nav class="p-4">
            <!-- Accès rapides -->
            <div class="mb-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Accès rapides</h3>
                <div class="space-y-2">
                    <a href="{{ route('deliverer.tournee') }}"
                       class="flex items-center p-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-all"
                       @click="menuOpen = false">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                        </svg>
                        <span class="font-medium">Ma Tournée</span>
                    </a>

                    <a href="{{ route('deliverer.scan.simple') }}"
                       class="flex items-center p-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-all"
                       @click="menuOpen = false">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="font-medium">Scanner Unique</span>
                    </a>

                    <a href="{{ route('deliverer.scan.multi') }}"
                       class="flex items-center p-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-all"
                       @click="menuOpen = false">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        <span class="font-medium">Scanner Multiple</span>
                    </a>

                    <a href="{{ route('deliverer.wallet') }}"
                       class="flex items-center p-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-all"
                       @click="menuOpen = false">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span class="font-medium">Mon Wallet</span>
                    </a>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="mb-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Actions rapides</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-center">
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-2">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-green-700">Livraisons</span>
                        <div class="text-lg font-bold text-green-600">12</div>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-2">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-blue-700">Collectes</span>
                        <div class="text-lg font-bold text-blue-600">5</div>
                    </div>
                </div>
            </div>

            <!-- Wallet rapide - Données Réelles -->
            <a href="{{ route('deliverer.wallet.optimized') }}" class="block bg-gradient-to-r from-purple-50 to-blue-50 border border-purple-200 rounded-lg p-4 mb-6 hover:shadow-md transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Solde Wallet</p>
                        <p class="text-lg font-bold text-purple-600" x-data x-init="
                            fetch('/deliverer/api/wallet/balance')
                                .then(r => r.json())
                                .then(data => $el.textContent = (data.balance || 0).toFixed(2) + ' TND')
                                .catch(() => $el.textContent = '0.00 TND')
                        ">Chargement...</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
            </a>

            <!-- Déconnexion -->
            <div class="border-t pt-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center w-full p-3 text-red-600 rounded-lg hover:bg-red-50 transition-all active:scale-98">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="font-medium">Déconnexion</span>
                    </button>
                </form>
            </div>
        </nav>
    </div>

    <!-- Main content -->
    <main class="min-h-screen pb-safe-mobile">
        @yield('content')
    </main>

    <!-- Bottom Navigation Minimisée -->
    <nav class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-gray-200 shadow-lg"
         style="padding-bottom: max(1rem, calc(1rem + env(safe-area-inset-bottom)));">

        <!-- Tab Indicator Line -->
        <div class="absolute top-0 left-0 right-0 h-0.5">
            <div class="h-full bg-blue-500 transition-all duration-300 ease-out"
                 :style="getCurrentTabPosition()"></div>
        </div>

        <div class="flex items-center">
            <!-- Onglet TOURNÉE -->
            <a href="{{ route('deliverer.tournee') }}"
               class="flex-1 flex flex-col items-center py-2 px-2 transition-colors
                      {{ request()->routeIs('deliverer.tournee') ? 'text-blue-600' : 'text-gray-500' }}">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center mb-1
                           {{ request()->routeIs('deliverer.tournee') ? 'bg-blue-100' : 'bg-gray-100' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <span class="text-xs font-medium">
                    Tournée
                </span>
            </a>

            <!-- Onglet WALLET -->
            <a href="{{ route('deliverer.wallet') }}"
               class="flex-1 flex flex-col items-center py-2 px-2 transition-colors
                      {{ request()->routeIs('deliverer.wallet') ? 'text-green-600' : 'text-gray-500' }}">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center mb-1
                           {{ request()->routeIs('deliverer.wallet') ? 'bg-green-100' : 'bg-gray-100' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium">
                    Wallet
                </span>
            </a>
        </div>
    </nav>


    <!-- Loading Overlay Moderne -->
    <div x-show="loading"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 backdrop-blur-md flex items-center justify-center z-50"
         style="display: none; background: rgba(0, 0, 0, 0.4);">
        <div class="glass-card p-8 text-center scale-in max-w-sm mx-4" style="box-shadow: var(--shadow-strong);">
            <!-- Loading Animation Sophistiquée -->
            <div class="relative mb-6">
                <div class="w-16 h-16 mx-auto relative">
                    <div class="absolute inset-0 rounded-full border-4 border-gray-200"></div>
                    <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-primary animate-spin"></div>
                    <div class="absolute inset-2 rounded-full border-2 border-transparent border-t-accent animate-spin" style="animation-direction: reverse; animation-duration: 1.5s;"></div>
                </div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-4 h-4 bg-primary rounded-full animate-pulse"></div>
                </div>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">Chargement en cours</h3>
            <p class="text-gray-600 text-sm">Veuillez patienter...</p>
            <!-- Progress Bar -->
            <div class="w-full bg-gray-200 rounded-full h-1 mt-4 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-primary to-accent rounded-full shimmer" style="width: 100%; animation-duration: 1.5s;"></div>
            </div>
        </div>
    </div>

    <!-- Global Scripts -->
    <script>
        // PWA Installation
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
        });

        // Network status
        window.addEventListener('online', () => {
            document.body.classList.remove('offline');
        });

        window.addEventListener('offline', () => {
            document.body.classList.add('offline');
        });

        // Prevent double-tap zoom
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function (event) {
            const now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);

        // Système de Toast Ultra-Moderne
        function showToast(message, type = 'success', duration = 4000) {
            const toast = document.createElement('div');
            const bgGradient = type === 'success' ? 'var(--gradient-success)' :
                              type === 'error' ? 'var(--gradient-error)' :
                              type === 'warning' ? 'var(--gradient-warning)' :
                              'var(--gradient-primary)';

            toast.className = 'fixed top-6 right-6 z-50 px-6 py-4 rounded-2xl text-white font-bold transform transition-all duration-500 ease-out glass-card border border-white/20 backdrop-blur-xl';
            toast.style.background = bgGradient;
            toast.style.boxShadow = 'var(--shadow-strong)';
            toast.style.transform = 'translateX(100%) scale(0.8) rotate(5deg)';
            toast.style.opacity = '0';
            toast.style.minWidth = '280px';
            toast.style.maxWidth = '400px';

            // Icon + Message avec animations
            toast.innerHTML = `
                <div class="flex items-center space-x-4 relative">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
                        ${type === 'success' ?
                            '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>' :
                            type === 'error' ?
                            '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>' :
                            type === 'warning' ?
                            '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>' :
                            '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                        }
                    </div>
                    <div class="flex-1">
                        <span class="block text-sm font-bold">${message}</span>
                        <div class="w-full bg-white/20 rounded-full h-1 mt-2 overflow-hidden">
                            <div class="h-full bg-white rounded-full transition-all duration-${duration} ease-linear"
                                 style="width: 100%; animation: shrink ${duration}ms linear;"></div>
                        </div>
                    </div>
                    <button onclick="this.parentElement.parentElement.parentElement.style.transform='translateX(100%) scale(0.8)'; this.parentElement.parentElement.parentElement.style.opacity='0';"
                            class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm hover:bg-white/30 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            `;

            // CSS pour l'animation de la barre de progression
            const style = document.createElement('style');
            style.textContent = `
                @keyframes shrink {
                    from { width: 100%; }
                    to { width: 0%; }
                }
            `;
            document.head.appendChild(style);

            document.body.appendChild(toast);

            // Animation d'entrée sophistiquée
            requestAnimationFrame(() => {
                toast.style.transform = 'translateX(0) scale(1) rotate(0deg)';
                toast.style.opacity = '1';
            });

            // Animation de sortie automatique
            setTimeout(() => {
                toast.style.transform = 'translateX(100%) scale(0.8) rotate(-5deg)';
                toast.style.opacity = '0';
                setTimeout(() => {
                    if (document.body.contains(toast)) {
                        document.body.removeChild(toast);
                        document.head.removeChild(style);
                    }
                }, 500);
            }, duration);
        }

        // Fonctions toast spécialisées
        function showSuccessToast(message) { showToast(message, 'success'); }
        function showErrorToast(message) { showToast(message, 'error'); }
        function showWarningToast(message) { showToast(message, 'warning'); }
        function showInfoToast(message) { showToast(message, 'info'); }

        // Tab indicator position pour bottom nav
        function getCurrentTabPosition() {
            const isRunSheet = window.location.pathname.includes('run-sheet');
            const isWallet = window.location.pathname.includes('wallet-optimized');

            if (isRunSheet) {
                return 'width: 50%; left: 0;';
            } else if (isWallet) {
                return 'width: 50%; left: 50%;';
            }
            return 'width: 0; left: 0;';
        }

        // Système de thèmes dynamiques
        class ThemeManager {
            constructor() {
                this.currentTheme = localStorage.getItem('deliverer-theme') || 'default';
                this.themes = {
                    default: {
                        primary: '#6366F1',
                        primaryLight: '#818CF8',
                        accent: '#06B6D4',
                        accentLight: '#67E8F9'
                    },
                    ocean: {
                        primary: '#0891B2',
                        primaryLight: '#06B6D4',
                        accent: '#3B82F6',
                        accentLight: '#60A5FA'
                    },
                    sunset: {
                        primary: '#F59E0B',
                        primaryLight: '#FBBF24',
                        accent: '#EF4444',
                        accentLight: '#F87171'
                    },
                    forest: {
                        primary: '#10B981',
                        primaryLight: '#34D399',
                        accent: '#059669',
                        accentLight: '#6EE7B7'
                    }
                };
                this.applyTheme(this.currentTheme);
            }

            applyTheme(themeName) {
                const theme = this.themes[themeName];
                if (!theme) return;

                const root = document.documentElement;
                Object.entries(theme).forEach(([key, value]) => {
                    root.style.setProperty(`--${key.replace(/([A-Z])/g, '-$1').toLowerCase()}`, value);
                });

                this.currentTheme = themeName;
                localStorage.setItem('deliverer-theme', themeName);

                // Animation de transition du thème
                document.body.style.transition = 'all 0.5s ease';
                setTimeout(() => {
                    document.body.style.transition = '';
                }, 500);
            }

            toggleTheme() {
                const themes = Object.keys(this.themes);
                const currentIndex = themes.indexOf(this.currentTheme);
                const nextIndex = (currentIndex + 1) % themes.length;
                this.applyTheme(themes[nextIndex]);

                showSuccessToast(`Thème "${themes[nextIndex]}" activé`);
            }

            getAvailableThemes() {
                return Object.keys(this.themes);
            }
        }

        // Initialisation du gestionnaire de thèmes
        window.themeManager = new ThemeManager();

        // Performance monitoring
        const performanceObserver = new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                if (entry.entryType === 'navigation') {
                    console.log(`Navigation: ${entry.duration.toFixed(2)}ms`);
                }
                if (entry.entryType === 'paint') {
                    console.log(`${entry.name}: ${entry.startTime.toFixed(2)}ms`);
                }
            }
        });

        try {
            performanceObserver.observe({ entryTypes: ['navigation', 'paint'] });
        } catch (e) {
            console.log('Performance Observer non supporté');
        }

        // Intersection Observer pour les animations
        const animationObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-up');
                }
            });
        }, { threshold: 0.1 });

        // Observer tous les éléments avec la classe 'animate-on-scroll'
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                animationObserver.observe(el);
            });
        });
    </script>

    <!-- PWA Manager - PRODUCTION READY (Chargé de façon asynchrone) -->
    <script>
        // Charger PWA Manager de façon asynchrone pour ne pas bloquer
        if (typeof pwaManager === 'undefined') {
            const pwaScript = document.createElement('script');
            pwaScript.src = '/js/pwa-manager.js';
            pwaScript.async = true;
            document.head.appendChild(pwaScript);
        }
    </script>
    <script>
        // Configuration PWA avancée
        document.addEventListener('DOMContentLoaded', function() {
            // Setup Pull-to-refresh
            pwaManager.setupPullToRefresh(async () => {
                showToast('Actualisation...', 'info', 2000);
                await new Promise(resolve => setTimeout(resolve, 1000));
                window.location.reload();
            });

            // Enregistrer Service Worker
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('✅ SW registered:', registration.scope);
                    })
                    .catch(error => {
                        console.error('❌ SW registration failed:', error);
                    });
            }

            // Notification Permission
            if ('Notification' in window && Notification.permission === 'default') {
                setTimeout(() => {
                    if (confirm('Activer les notifications pour recevoir vos nouveaux pickups ?')) {
                        Notification.requestPermission().then(permission => {
                            if (permission === 'granted') {
                                showToast('Notifications activées !', 'success');
                            }
                        });
                    }
                }, 3000);
            }

            // Battery Status (économie d'énergie)
            if ('getBattery' in navigator) {
                navigator.getBattery().then(battery => {
                    if (battery.level < 0.20 && !battery.charging) {
                        showToast('Batterie faible - Mode économie activé', 'warning');
                        // Réduire fréquence de rafraîchissement
                        document.body.classList.add('power-save-mode');
                    }
                });
            }
        });

        // Fonction globale pour les requêtes API avec gestion offline
        window.apiRequest = async function(url, options = {}) {
            try {
                const response = await fetch(url, {
                    ...options,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        ...options.headers
                    },
                    credentials: 'include'
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Erreur serveur');
                }

                return data;
            } catch (error) {
                if (!navigator.onLine) {
                    showToast('Action mise en queue (hors ligne)', 'warning');
                    // Stocker pour synchronisation
                    return { queued: true };
                }
                throw error;
            }
        };
    </script>

    @stack('scripts')
</body>
</html>