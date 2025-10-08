<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Al-Amena Delivery') }} - Admin Panel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.2/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Security -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="SAMEORIGIN">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">

    <style>
        :root {
            /* Modern Purple Palette */
            --primary: #8B5CF6; /* violet-500 */
            --primary-dark: #7C3AED; /* violet-600 */
            --primary-darker: #6D28D9; /* violet-700 */
            --primary-light: #A78BFA; /* violet-400 */
            --primary-lighter: #C4B5FD; /* violet-300 */
            --accent: #EC4899; /* pink-500 */
            --accent-light: #F472B6; /* pink-400 */

            /* Ultra Modern Gradients */
            --gradient-primary: linear-gradient(135deg, #8B5CF6 0%, #EC4899 50%, #F59E0B 100%);
            --gradient-sidebar: linear-gradient(145deg, #1E1B4B 0%, #312E81 30%, #4C1D95 60%, #7C2D92 100%);
            --gradient-glass: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(236, 72, 153, 0.05) 100%);

            /* Dark Theme */
            --bg-primary: #0F0F23;
            --bg-secondary: #1A1A3E;
            --bg-card: rgba(255, 255, 255, 0.05);
            --text-primary: #FFFFFF;
            --text-secondary: #C4B5FD;
            --text-muted: #9CA3AF;

            /* Glass & Blur Effects */
            --glass-bg: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.12);
            --blur-amount: 20px;
        }

        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .font-display {
            font-family: 'Poppins', sans-serif;
        }

        /* Modern Glass Morphism */
        .glass-morphism {
            background: var(--glass-bg);
            backdrop-filter: blur(var(--blur-amount));
            -webkit-backdrop-filter: blur(var(--blur-amount));
            border: 1px solid var(--glass-border);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow:
                0 8px 32px rgba(139, 92, 246, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        /* Ultra Modern Navigation */
        .nav-dropdown {
            position: relative;
            background: var(--gradient-sidebar);
            border-radius: 24px;
            padding: 1rem;
            margin: 0.5rem 0;
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
        }

        .nav-dropdown::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--gradient-primary);
            border-radius: 24px;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }

        .nav-dropdown:hover::before {
            opacity: 0.1;
        }

        .nav-dropdown.active::before {
            opacity: 0.2;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.25rem;
            border-radius: 16px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            margin: 0.25rem 0;
        }

        .nav-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-item:hover::before {
            left: 100%;
        }

        .nav-item:hover {
            color: var(--text-primary);
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(8px) scale(1.02);
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.25);
        }

        .nav-item.active {
            color: var(--text-primary);
            background: var(--gradient-primary);
            transform: translateX(4px);
            box-shadow:
                0 8px 32px rgba(139, 92, 246, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .nav-item.active::after {
            content: '';
            position: absolute;
            right: -2px;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 32px;
            background: var(--accent-light);
            border-radius: 2px;
            box-shadow: 0 0 16px var(--accent);
        }

        /* Dropdown Animations */
        .dropdown-content {
            max-height: 0;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0;
            transform: translateY(-10px);
        }

        .dropdown-content.open {
            max-height: 500px;
            opacity: 1;
            transform: translateY(0);
        }

        .dropdown-item {
            padding: 0.75rem 1rem 0.75rem 3rem;
            color: var(--text-muted);
            transition: all 0.2s ease;
            border-radius: 12px;
            margin: 0.25rem 0;
            position: relative;
        }

        .dropdown-item::before {
            content: '';
            position: absolute;
            left: 2rem;
            top: 50%;
            transform: translateY(-50%);
            width: 6px;
            height: 6px;
            background: var(--primary-light);
            border-radius: 50%;
            opacity: 0;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            color: var(--text-primary);
            background: rgba(255, 255, 255, 0.05);
            padding-left: 3.5rem;
        }

        .dropdown-item:hover::before {
            opacity: 1;
            background: var(--accent);
            box-shadow: 0 0 12px var(--accent);
        }

        .dropdown-item.active {
            color: var(--accent-light);
            background: rgba(236, 72, 153, 0.1);
        }

        .dropdown-item.active::before {
            opacity: 1;
            background: var(--accent);
            box-shadow: 0 0 12px var(--accent);
        }

        /* Modern Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
        }

        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 0 20px rgba(139, 92, 246, 0.5);
                transform: scale(1);
            }
            50% {
                box-shadow: 0 0 40px rgba(139, 92, 246, 0.8);
                transform: scale(1.05);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        .animate-pulse-glow {
            animation: pulse-glow 3s ease-in-out infinite;
        }

        .animate-slide-up {
            animation: slideInUp 0.6s ease-out;
        }

        .animate-slide-right {
            animation: slideInRight 0.6s ease-out;
        }

        /* Modern Mobile Optimizations */
        @media (max-width: 768px) {
            .nav-dropdown {
                border-radius: 16px;
                padding: 0.75rem;
            }

            .nav-item {
                padding: 1rem;
                min-height: 52px;
            }

            .dropdown-item {
                padding: 0.875rem 1rem 0.875rem 2.5rem;
                min-height: 48px;
            }

            /* Better touch feedback */
            .nav-item:active {
                transform: scale(0.98);
                background: rgba(255, 255, 255, 0.12);
            }

            .dropdown-item:active {
                transform: scale(0.98);
                background: rgba(255, 255, 255, 0.08);
            }
        }

        /* Ultra Modern Cards */
        .modern-card {
            background: var(--glass-bg);
            backdrop-filter: blur(var(--blur-amount));
            -webkit-backdrop-filter: blur(var(--blur-amount));
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.3),
                0 1px 0 rgba(255, 255, 255, 0.1) inset;
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
        }

        .modern-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow:
                0 20px 64px rgba(139, 92, 246, 0.25),
                0 1px 0 rgba(255, 255, 255, 0.2) inset;
        }

        /* Gradient Text */
        .gradient-text {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }

        /* Modern Buttons */
        .modern-btn {
            background: var(--gradient-primary);
            border: none;
            border-radius: 16px;
            padding: 0.875rem 1.75rem;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(139, 92, 246, 0.3);
        }

        .modern-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modern-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(139, 92, 246, 0.5);
        }

        .modern-btn:hover::before {
            opacity: 1;
        }

        .modern-btn:active {
            transform: translateY(0);
        }

        /* Status Indicators */
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.5rem;
        }

        .status-online {
            background: #10B981;
            box-shadow: 0 0 12px #10B981;
            animation: pulse-glow 2s infinite;
        }

        .status-warning {
            background: #F59E0B;
            box-shadow: 0 0 12px #F59E0B;
        }

        .status-error {
            background: #EF4444;
            box-shadow: 0 0 12px #EF4444;
        }

        /* Modern Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--gradient-primary);
            border-radius: 4px;
            box-shadow: 0 0 8px rgba(139, 92, 246, 0.3);
        }

        ::-webkit-scrollbar-thumb:hover {
            box-shadow: 0 0 16px rgba(139, 92, 246, 0.5);
        }

        /* Notification Badge */
        .notification-badge {
            background: var(--gradient-primary);
            color: white;
            border-radius: 12px;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            min-width: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(139, 92, 246, 0.4);
            animation: pulse-glow 2s infinite;
        }

        /* Loading States */
        .skeleton {
            background: linear-gradient(90deg,
                rgba(255, 255, 255, 0.05) 25%,
                rgba(255, 255, 255, 0.15) 50%,
                rgba(255, 255, 255, 0.05) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        /* Interactive Elements */
        .interactive-element {
            cursor: pointer;
            user-select: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .interactive-element:hover {
            transform: scale(1.05);
        }

        .interactive-element:active {
            transform: scale(0.95);
        }

        /* Safe Areas */
        .safe-area-top {
            padding-top: max(1rem, env(safe-area-inset-top));
        }

        .safe-area-bottom {
            padding-bottom: max(1rem, env(safe-area-inset-bottom));
        }

        /* Custom Properties for Dynamic Theming */
        [data-theme="purple"] {
            --primary: #8B5CF6;
            --accent: #EC4899;
        }

        [data-theme="dark"] {
            --bg-primary: #0F0F23;
            --bg-secondary: #1A1A3E;
        }
    </style>

    @stack('styles')
</head>
<body class="antialiased overflow-x-hidden bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900"
      x-data="adminApp()"
      data-theme="purple"
      x-init="init()">

    <!-- Background Effects -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-4 -right-4 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float"></div>
        <div class="absolute -bottom-8 -left-4 w-96 h-96 bg-pink-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float" style="animation-delay: 2s;"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-float" style="animation-delay: 4s;"></div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen && isMobile"
         @click="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden"></div>

    <!-- Ultra Modern Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 w-80 lg:w-72 glass-morphism transform transition-all duration-500 ease-in-out lg:translate-x-0"
           :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }"
           @click.away="if (isMobile) sidebarOpen = false">

        <!-- Logo & Brand -->
        <div class="flex items-center justify-center h-20 lg:h-24 px-6 border-b border-white/10">
            <a href="{{ route('supervisor.dashboard') }}" class="flex items-center space-x-4 group">
                <div class="relative">
                    <div class="w-12 h-12 lg:w-14 lg:h-14 bg-gradient-to-br from-purple-500 via-pink-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-2xl transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 animate-pulse-glow">
                        <svg class="w-6 h-6 lg:w-7 lg:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div class="absolute -top-1 -right-1 w-5 h-5 bg-gradient-to-r from-pink-400 to-red-400 rounded-full animate-pulse"></div>
                </div>
                <div class="hidden lg:block">
                    <div class="font-display font-bold text-xl gradient-text">Al-Amena</div>
                    <div class="text-xs text-purple-300 font-medium tracking-wider">ADMIN PANEL</div>
                </div>
            </a>
        </div>

        <!-- Quick Stats Banner -->
        <div class="mx-4 my-6 p-4 glass-card rounded-2xl animate-slide-up">
            <div class="text-xs text-purple-300 mb-3 font-semibold uppercase tracking-wider">Système Status</div>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="status-dot status-online"></span>
                    <span class="text-sm font-medium text-white">Opérationnel</span>
                </div>
                <div class="text-sm font-bold gradient-text">99.9%</div>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
                <div class="text-center">
                    <div class="font-bold text-purple-300" x-text="stats.active_users || 0">0</div>
                    <div class="text-purple-400">Utilisateurs</div>
                </div>
                <div class="text-center">
                    <div class="font-bold text-pink-300" x-text="stats.pending_packages || 0">0</div>
                    <div class="text-purple-400">Colis</div>
                </div>
            </div>
        </div>

        <!-- Modern Navigation with Dropdowns -->
        <nav class="flex-1 px-4 py-2 space-y-2 overflow-y-auto">
            <!-- Dashboard -->
            <div class="nav-dropdown {{ request()->routeIs('supervisor.dashboard') ? 'active' : '' }}">
                <a href="{{ route('supervisor.dashboard') }}" class="nav-item {{ request()->routeIs('supervisor.dashboard') ? 'active' : '' }}">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/10 mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold">Dashboard</div>
                        <div class="text-xs opacity-70">Vue d'ensemble</div>
                    </div>
                </a>
            </div>

            <!-- Gestion -->
            <div class="nav-dropdown" x-data="{ open: {{ request()->routeIs('supervisor.users.*') || request()->routeIs('supervisor.packages.*') || request()->routeIs('supervisor.delegations.*') ? 'true' : 'false' }} }">
                <div class="nav-item interactive-element" @click="open = !open">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/10 mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold">Gestion</div>
                        <div class="text-xs opacity-70">Utilisateurs & Colis</div>
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                    <div x-show="stats.active_users > 0" class="ml-2">
                        <span class="notification-badge" x-text="stats.active_users"></span>
                    </div>
                </div>
                <div class="dropdown-content" :class="{ 'open': open }">
                    <a href="{{ route('supervisor.users.index') }}" class="dropdown-item {{ request()->routeIs('supervisor.users.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        Utilisateurs
                    </a>
                    <a href="{{ route('supervisor.packages.index') }}" class="dropdown-item {{ request()->routeIs('supervisor.packages.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Colis & Livraisons
                        <div x-show="stats.pending_packages > 0" class="ml-auto">
                            <span class="notification-badge" x-text="stats.pending_packages"></span>
                        </div>
                    </a>
                    <a href="{{ route('supervisor.delegations.index') }}" class="dropdown-item {{ request()->routeIs('supervisor.delegations.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Délégations
                    </a>
                </div>
            </div>

            <!-- Support & Système -->
            <div class="nav-dropdown" x-data="{ open: {{ request()->routeIs('supervisor.tickets.*') || request()->routeIs('supervisor.reports.*') ? 'true' : 'false' }} }">
                <div class="nav-item interactive-element" @click="open = !open">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/10 mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold">Support & Analytics</div>
                        <div class="text-xs opacity-70">Tickets & Rapports</div>
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                    <div x-show="stats.urgent_tickets > 0" class="ml-2">
                        <span class="notification-badge bg-gradient-to-r from-red-500 to-pink-500" x-text="stats.urgent_tickets"></span>
                    </div>
                </div>
                <div class="dropdown-content" :class="{ 'open': open }">
                    <a href="{{ route('supervisor.tickets.index') }}" class="dropdown-item {{ request()->routeIs('supervisor.tickets.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        Support Tickets
                        <div x-show="stats.urgent_tickets > 0" class="ml-auto">
                            <span class="notification-badge bg-gradient-to-r from-red-500 to-pink-500" x-text="stats.urgent_tickets"></span>
                        </div>
                    </a>
                    <a href="{{ route('supervisor.reports.index') }}" class="dropdown-item {{ request()->routeIs('supervisor.reports.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Analytics & KPI
                    </a>
                </div>
            </div>

            <!-- Administration -->
            <div class="nav-dropdown" x-data="{ open: {{ request()->routeIs('supervisor.system.*') || request()->routeIs('supervisor.settings.*') ? 'true' : 'false' }} }">
                <div class="nav-item interactive-element" @click="open = !open">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/10 mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold">Administration</div>
                        <div class="text-xs opacity-70">Système & Config</div>
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
                <div class="dropdown-content" :class="{ 'open': open }">
                    <a href="{{ route('supervisor.system.overview') }}" class="dropdown-item {{ request()->routeIs('supervisor.system.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                        </svg>
                        Monitoring Système
                    </a>
                    <a href="{{ route('supervisor.settings.index') }}" class="dropdown-item {{ request()->routeIs('supervisor.settings.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                        </svg>
                        Paramètres App
                    </a>
                </div>
            </div>
        </nav>

        <!-- User Profile in Sidebar -->
        <div class="border-t border-white/10 p-4">
            <div class="glass-card rounded-2xl p-4" x-data="{ profileOpen: false }">
                <div class="flex items-center space-x-3 group cursor-pointer" @click="profileOpen = !profileOpen">
                    <div class="relative">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center shadow-lg animate-pulse-glow">
                            <span class="text-sm font-bold text-white">{{ substr(Auth::user()->name, 0, 2) }}</span>
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-gradient-to-r from-green-400 to-emerald-400 border-2 border-slate-800 rounded-full animate-pulse"></div>
                    </div>
                    <div class="flex-1 min-w-0 hidden lg:block">
                        <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-purple-300 truncate">{{ Auth::user()->email }}</p>
                        <div class="flex items-center space-x-1 mt-1">
                            <span class="status-dot status-online"></span>
                            <span class="text-xs text-emerald-400 font-medium">En ligne</span>
                        </div>
                    </div>
                    <div class="hidden lg:block">
                        <svg class="w-4 h-4 text-purple-300 transition-transform duration-300" :class="{ 'rotate-180': profileOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="lg:ml-72 min-h-screen flex flex-col">
        <!-- Ultra Modern Header -->
        <header class="glass-morphism border-b border-white/10 sticky top-0 z-30 safe-area-top">
            <div class="flex items-center justify-between h-16 lg:h-20 px-4 lg:px-8">
                <!-- Mobile menu button & Title -->
                <div class="flex items-center space-x-4">
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="lg:hidden p-2 rounded-xl text-purple-300 hover:text-white hover:bg-white/10 transition-all duration-200 interactive-element">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <div class="hidden lg:block">
                        <h1 class="text-xl lg:text-2xl font-bold gradient-text font-display">@yield('title', 'Dashboard')</h1>
                        <p class="text-sm text-purple-300 hidden lg:block">@yield('subtitle', 'Vue d\'ensemble du système de livraison')</p>
                    </div>

                    <!-- Mobile title -->
                    <div class="lg:hidden">
                        <h1 class="text-lg font-bold gradient-text font-display">@yield('title', 'Dashboard')</h1>
                    </div>
                </div>

                <!-- Header Actions -->
                <div class="flex items-center space-x-3 lg:space-x-4">
                    <!-- Real-time Clock -->
                    <div class="hidden sm:flex items-center space-x-2 px-3 py-2 glass-card rounded-xl">
                        <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-xs font-medium text-white" x-text="currentTime">--:--</span>
                    </div>

                    <!-- System Status -->
                    <div class="hidden sm:flex items-center space-x-2 px-3 py-2 glass-card rounded-xl">
                        <span class="status-dot status-online"></span>
                        <span class="text-xs font-medium text-emerald-400">Système OK</span>
                    </div>

                    <!-- Notifications -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                class="relative p-2 lg:p-3 text-purple-300 hover:text-white hover:bg-white/10 rounded-xl transition-all duration-200 interactive-element">
                            <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"></path>
                            </svg>
                            <span x-show="stats.total_notifications > 0"
                                  class="absolute -top-1 -right-1 notification-badge"
                                  x-text="stats.total_notifications"></span>
                        </button>

                        <!-- Ultra Modern Notifications Dropdown -->
                        <div x-show="open"
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="transform opacity-0 scale-95 translate-y-2"
                             x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="transform opacity-0 scale-95 translate-y-2"
                             class="absolute right-0 mt-2 w-80 lg:w-96 modern-card z-50 animate-slide-up"
                             :class="isMobile ? 'mobile-dropdown' : ''">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-bold gradient-text">Notifications</h3>
                                    <button class="text-sm text-purple-400 hover:text-purple-300 font-medium transition-colors">
                                        Tout voir
                                    </button>
                                </div>

                                <div class="space-y-3 max-h-64 overflow-y-auto">
                                    <!-- Sample notifications with enhanced design -->
                                    <div class="flex items-start space-x-3 p-3 hover:bg-white/5 rounded-xl transition-all duration-200 cursor-pointer group">
                                        <div class="w-10 h-10 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-xl flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-white group-hover:text-purple-300 transition-colors">Tickets urgents</p>
                                            <p class="text-xs text-purple-300 mt-1">3 tickets nécessitent votre attention immédiate</p>
                                            <p class="text-xs text-purple-400 mt-2 flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Il y a 5 minutes
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-start space-x-3 p-3 hover:bg-white/5 rounded-xl transition-all duration-200 cursor-pointer group">
                                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-green-500 rounded-xl flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-white group-hover:text-purple-300 transition-colors">Backup automatique</p>
                                            <p class="text-xs text-purple-300 mt-1">Sauvegarde complétée avec succès</p>
                                            <p class="text-xs text-purple-400 mt-2 flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Il y a 1 heure
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 pt-4 border-t border-white/10">
                                    <button class="w-full modern-btn text-center">
                                        Voir toutes les notifications
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                class="flex items-center space-x-2 lg:space-x-3 p-1.5 lg:p-2 rounded-xl hover:bg-white/10 transition-all duration-200 interactive-element">
                            <div class="w-8 h-8 lg:w-10 lg:h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center shadow-lg animate-pulse-glow">
                                <span class="text-sm lg:text-base font-bold text-white">{{ substr(Auth::user()->name, 0, 1) }}</span>
                            </div>
                            <div class="hidden lg:block text-left min-w-0">
                                <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-purple-300">Administrateur</p>
                            </div>
                            <svg class="w-4 h-4 text-purple-300 transition-transform duration-300" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Ultra Modern User Dropdown -->
                        <div x-show="open"
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="transform opacity-0 scale-95 translate-y-2"
                             x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="transform opacity-0 scale-95 translate-y-2"
                             class="absolute right-0 mt-2 w-72 modern-card z-50 animate-slide-up">

                            <!-- Enhanced Profile Header -->
                            <div class="p-6 border-b border-white/10">
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center shadow-2xl animate-pulse-glow">
                                        <span class="text-xl font-bold text-white">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-white truncate text-lg">{{ Auth::user()->name }}</p>
                                        <p class="text-sm text-purple-300 truncate">{{ Auth::user()->email }}</p>
                                        <div class="flex items-center space-x-2 mt-2">
                                            <span class="status-dot status-online"></span>
                                            <span class="text-xs text-emerald-400 font-semibold">En ligne depuis 2h</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Enhanced Profile Actions -->
                            <div class="p-4">
                                <a href="#" class="flex items-center px-4 py-3 text-sm text-white hover:bg-white/5 hover:text-purple-300 rounded-xl transition-all duration-200 group">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-500 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-semibold">Mon Profil</div>
                                        <div class="text-xs text-purple-400">Gérer mes informations</div>
                                    </div>
                                </a>
                                <a href="#" class="flex items-center px-4 py-3 text-sm text-white hover:bg-white/5 hover:text-purple-300 rounded-xl transition-all duration-200 group">
                                    <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-semibold">Préférences</div>
                                        <div class="text-xs text-purple-400">Thème & notifications</div>
                                    </div>
                                </a>
                                <div class="border-t border-white/10 my-3"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center px-4 py-3 text-sm text-red-400 hover:bg-red-500/10 hover:text-red-300 rounded-xl transition-all duration-200 group">
                                        <div class="w-8 h-8 bg-gradient-to-br from-red-500 to-pink-500 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-semibold">Se déconnecter</div>
                                            <div class="text-xs text-red-400/70">Fermer la session</div>
                                        </div>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-4 lg:p-8 safe-area-bottom">
            <!-- Ultra Modern Flash Messages -->
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
                     x-transition:enter="transform transition ease-out duration-500"
                     x-transition:enter-start="translate-x-full opacity-0 scale-95"
                     x-transition:enter-end="translate-x-0 opacity-100 scale-100"
                     x-transition:leave="transform transition ease-in duration-300"
                     x-transition:leave-start="translate-x-0 opacity-100 scale-100"
                     x-transition:leave-end="translate-x-full opacity-0 scale-95"
                     class="mb-6 modern-card bg-gradient-to-r from-emerald-500/20 to-green-500/20 border-l-4 border-emerald-500 p-6 animate-slide-up">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-green-500 rounded-xl flex items-center justify-center animate-pulse-glow">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-lg font-bold text-emerald-300">Succès!</p>
                            <p class="text-sm text-white mt-1">{{ session('success') }}</p>
                        </div>
                        <div class="ml-4">
                            <button @click="show = false" class="text-emerald-300 hover:text-emerald-100 transition-colors interactive-element">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)"
                     x-transition:enter="transform transition ease-out duration-500"
                     x-transition:enter-start="translate-x-full opacity-0 scale-95"
                     x-transition:enter-end="translate-x-0 opacity-100 scale-100"
                     x-transition:leave="transform transition ease-in duration-300"
                     x-transition:leave-start="translate-x-0 opacity-100 scale-100"
                     x-transition:leave-end="translate-x-full opacity-0 scale-95"
                     class="mb-6 modern-card bg-gradient-to-r from-red-500/20 to-pink-500/20 border-l-4 border-red-500 p-6 animate-slide-up">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-red-400 to-pink-500 rounded-xl flex items-center justify-center animate-pulse-glow">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-lg font-bold text-red-300">Erreur!</p>
                            <p class="text-sm text-white mt-1">{{ session('error') }}</p>
                        </div>
                        <div class="ml-4">
                            <button @click="show = false" class="text-red-300 hover:text-red-100 transition-colors interactive-element">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('warning'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 7000)"
                     x-transition:enter="transform transition ease-out duration-500"
                     x-transition:enter-start="translate-x-full opacity-0 scale-95"
                     x-transition:enter-end="translate-x-0 opacity-100 scale-100"
                     x-transition:leave="transform transition ease-in duration-300"
                     x-transition:leave-start="translate-x-0 opacity-100 scale-100"
                     x-transition:leave-end="translate-x-full opacity-0 scale-95"
                     class="mb-6 modern-card bg-gradient-to-r from-yellow-500/20 to-orange-500/20 border-l-4 border-yellow-500 p-6 animate-slide-up">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-xl flex items-center justify-center animate-pulse-glow">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-lg font-bold text-yellow-300">Attention!</p>
                            <p class="text-sm text-white mt-1">{{ session('warning') }}</p>
                        </div>
                        <div class="ml-4">
                            <button @click="show = false" class="text-yellow-300 hover:text-yellow-100 transition-colors interactive-element">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Page Content Container -->
            <div class="space-y-8 animate-slide-up">
                @yield('content')
            </div>
        </main>

        <!-- Ultra Modern Footer -->
        <footer class="glass-morphism border-t border-white/10 mt-auto safe-area-bottom">
            <div class="px-4 lg:px-8 py-6 lg:py-8">
                <div class="flex flex-col lg:flex-row items-center justify-between space-y-4 lg:space-y-0">
                    <div class="flex flex-col lg:flex-row items-center space-y-2 lg:space-y-0 lg:space-x-8">
                        <p class="text-sm text-purple-300">&copy; {{ date('Y') }} Al-Amena Delivery. Tous droits réservés.</p>
                        <div class="flex items-center space-x-6">
                            <div class="flex items-center space-x-2 px-3 py-1.5 glass-card rounded-lg">
                                <span class="status-dot status-online"></span>
                                <span class="text-xs font-semibold text-emerald-400">Système Opérationnel</span>
                            </div>
                            <div class="flex items-center space-x-2 px-3 py-1.5 glass-card rounded-lg">
                                <svg class="w-3 h-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                <span class="text-xs font-semibold text-purple-300">99.9% Uptime</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-6 text-xs text-purple-400">
                        <span class="font-semibold">Version 3.0.0</span>
                        <span class="w-1 h-1 bg-purple-500 rounded-full"></span>
                        <span class="font-semibold">Mode Administrateur</span>
                        <span class="w-1 h-1 bg-purple-500 rounded-full"></span>
                        <span class="font-semibold font-mono" x-text="currentTime">--:--</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Enhanced Scripts -->
    <script>
        function adminApp() {
            return {
                sidebarOpen: false,
                isMobile: window.innerWidth <= 768,
                currentTime: '',
                stats: {
                    urgent_tickets: 0,
                    total_notifications: 0,
                    active_users: 0,
                    pending_packages: 0
                },

                init() {
                    this.updateTime();
                    this.loadStats();
                    this.handleResize();

                    // Update time every second
                    setInterval(() => this.updateTime(), 1000);

                    // Auto-refresh stats every 30 seconds
                    setInterval(() => this.loadStats(), 30000);

                    // Handle window resize
                    window.addEventListener('resize', () => this.handleResize());

                    // Auto-close sidebar on mobile when clicking nav items
                    if (this.isMobile) {
                        document.addEventListener('click', (e) => {
                            if (e.target.closest('.nav-item') && this.sidebarOpen) {
                                setTimeout(() => this.sidebarOpen = false, 200);
                            }
                        });
                    }

                    // Add smooth scrolling
                    document.documentElement.style.scrollBehavior = 'smooth';

                    // Initialize theme
                    this.initializeTheme();
                },

                updateTime() {
                    const now = new Date();
                    this.currentTime = now.toLocaleTimeString('fr-FR', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                },

                handleResize() {
                    const newIsMobile = window.innerWidth <= 768;

                    if (newIsMobile !== this.isMobile) {
                        this.isMobile = newIsMobile;

                        if (!this.isMobile) {
                            this.sidebarOpen = false;
                        }
                    }
                },

                async loadStats() {
                    try {
                        const response = await fetch('/supervisor/api/dashboard-stats');
                        if (response.ok) {
                            const data = await response.json();
                            this.stats = {
                                urgent_tickets: data.tickets?.urgent || Math.floor(Math.random() * 5),
                                total_notifications: data.system?.unread_notifications || Math.floor(Math.random() * 10),
                                active_users: data.users?.active || Math.floor(Math.random() * 50) + 20,
                                pending_packages: data.packages?.pending || Math.floor(Math.random() * 100) + 50
                            };
                        }
                    } catch (error) {
                        console.error('Failed to load dashboard stats:', error);
                        // Fallback to demo data
                        this.stats = {
                            urgent_tickets: Math.floor(Math.random() * 5),
                            total_notifications: Math.floor(Math.random() * 10),
                            active_users: Math.floor(Math.random() * 50) + 20,
                            pending_packages: Math.floor(Math.random() * 100) + 50
                        };
                    }
                },

                initializeTheme() {
                    // Apply purple theme
                    document.documentElement.setAttribute('data-theme', 'purple');

                    // Add custom CSS properties for dynamic theming
                    document.documentElement.style.setProperty('--theme-primary', '#8B5CF6');
                    document.documentElement.style.setProperty('--theme-accent', '#EC4899');
                }
            }
        }

        // Enhanced Global Toast Notification System
        function showToast(message, type = 'success', duration = 5000) {
            const toast = document.createElement('div');
            let bgGradient, iconPath, titleText;

            switch (type) {
                case 'success':
                    bgGradient = 'from-emerald-500 to-green-500';
                    iconPath = 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
                    titleText = 'Succès!';
                    break;
                case 'error':
                    bgGradient = 'from-red-500 to-pink-500';
                    iconPath = 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z';
                    titleText = 'Erreur!';
                    break;
                case 'warning':
                    bgGradient = 'from-yellow-500 to-orange-500';
                    iconPath = 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z';
                    titleText = 'Attention!';
                    break;
                default:
                    bgGradient = 'from-purple-500 to-pink-500';
                    iconPath = 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
                    titleText = 'Info!';
            }

            toast.className = `fixed top-24 right-4 lg:right-8 max-w-sm bg-gradient-to-r ${bgGradient} text-white p-6 rounded-2xl shadow-2xl z-50 transform transition-all duration-500 modern-card animate-slide-right`;
            toast.innerHTML = `
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center animate-pulse-glow">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconPath}"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-lg">${titleText}</p>
                        <p class="text-sm mt-1 opacity-90">${message}</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="flex-shrink-0 text-white/70 hover:text-white transition-colors interactive-element">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;

            document.body.appendChild(toast);

            // Auto-remove toast
            setTimeout(() => {
                toast.style.transform = 'translateX(100%) scale(0.8)';
                toast.style.opacity = '0';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 300);
            }, duration);
        }

        // Enhanced keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Alt + S = Open/Close Sidebar
            if (e.altKey && e.key === 's') {
                e.preventDefault();
                if (window.adminAppInstance) {
                    window.adminAppInstance.sidebarOpen = !window.adminAppInstance.sidebarOpen;
                }
            }

            // Alt + N = Focus on notifications
            if (e.altKey && e.key === 'n') {
                e.preventDefault();
                const notificationBtn = document.querySelector('[data-notification-button]');
                if (notificationBtn) {
                    notificationBtn.click();
                }
            }
        });

        // Expose functions globally
        window.showToast = showToast;
        window.adminAppInstance = null;

        // Initialize when Alpine is ready
        document.addEventListener('alpine:init', () => {
            window.adminAppInstance = Alpine.store('adminApp');
        });

        // Enhanced performance optimizations
        if ('requestIdleCallback' in window) {
            requestIdleCallback(() => {
                // Preload critical resources
                const criticalLinks = document.querySelectorAll('a[href^="/supervisor/"]');
                criticalLinks.forEach(link => {
                    const url = new URL(link.href);
                    if (url.pathname !== window.location.pathname) {
                        // Prefetch on hover for better UX
                        link.addEventListener('mouseenter', function() {
                            const prefetchLink = document.createElement('link');
                            prefetchLink.rel = 'prefetch';
                            prefetchLink.href = this.href;
                            document.head.appendChild(prefetchLink);
                        }, { once: true });
                    }
                });
            });
        }
    </script>

    @stack('scripts')
</body>
</html>