<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion - Al-Amena Delivery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'pale-purple': '#E0D4FF',
                        'purple': {
                            50: '#F5F3FF', 100: '#EDE9FE', 200: '#DDD6FE', 300: '#C4B5FD',
                            400: '#A78BFA', 500: '#8B5CF6', 600: '#7C3AED', 700: '#6D28D9',
                            800: '#5B21B6', 900: '#4C1D95'
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'slide-in': 'slide-in 0.8s ease-out',
                        'fade-in': 'fade-in 1s ease-out',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        'float': {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' }
                        },
                        'slide-in': {
                            '0%': { transform: 'translateX(-100%)', opacity: '0' },
                            '100%': { transform: 'translateX(0)', opacity: '1' }
                        },
                        'fade-in': {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-900 via-purple-800 to-indigo-900 overflow-hidden">
    <!-- Background Animated Elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-purple-500 rounded-full opacity-20 animate-float"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-indigo-500 rounded-full opacity-20 animate-float" style="animation-delay: -3s;"></div>
        <div class="absolute top-1/2 left-1/4 w-64 h-64 bg-pink-500 rounded-full opacity-10 animate-float" style="animation-delay: -1.5s;"></div>
        <div class="absolute top-1/4 right-1/3 w-32 h-32 bg-yellow-400 rounded-full opacity-5 animate-pulse-slow" style="animation-delay: -2s;"></div>
        <div class="absolute bottom-1/4 left-1/2 w-48 h-48 bg-cyan-400 rounded-full opacity-5 animate-pulse-slow" style="animation-delay: -4s;"></div>
    </div>

    <!-- Main Container -->
    <div class="relative min-h-screen flex items-center justify-center p-4">
        
        <!-- Left Side - Branding -->
        <div class="hidden lg:flex lg:w-1/2 items-center justify-center animate-slide-in">
            <div class="text-center text-white">
                <!-- Logo -->
                <div class="mb-8">
                    <div class="inline-flex items-center justify-center w-32 h-32 bg-white bg-opacity-20 backdrop-blur-sm rounded-full mb-6 animate-float shadow-2xl">
                        <svg class="w-16 h-16 text-white drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h1 class="text-6xl font-bold mb-4 bg-gradient-to-r from-white via-purple-200 to-pink-200 bg-clip-text text-transparent drop-shadow-lg">
                        Al-Amena
                    </h1>
                    <h2 class="text-4xl font-light text-purple-200 drop-shadow-md">
                        Delivery
                    </h2>
                </div>

                <div class="space-y-4 text-purple-100">
                    <p class="text-xl font-medium">
                        🚀 La plateforme de livraison intelligente
                    </p>
                    <p class="text-lg font-light opacity-90">
                        Gestion complète • Sécurité maximale • Performance optimale
                    </p>
                </div>

                <!-- Features avec animations -->
                <div class="mt-12 grid grid-cols-1 gap-6">
                    <div class="flex items-center space-x-4 transform hover:scale-105 transition-transform duration-300">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-purple-800 bg-opacity-40 rounded-xl flex items-center justify-center backdrop-blur-sm shadow-lg">
                            <svg class="w-6 h-6 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div class="text-left">
                            <h3 class="font-semibold text-white text-lg">🔒 Sécurité Financière</h3>
                            <p class="text-purple-200 text-sm opacity-80">Système anti-panne avec récupération automatique</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4 transform hover:scale-105 transition-transform duration-300">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-600 to-indigo-800 bg-opacity-40 rounded-xl flex items-center justify-center backdrop-blur-sm shadow-lg">
                            <svg class="w-6 h-6 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div class="text-left">
                            <h3 class="font-semibold text-white text-lg">⚡ Performance Temps Réel</h3>
                            <p class="text-purple-200 text-sm opacity-80">Suivi instantané et notifications intelligentes</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4 transform hover:scale-105 transition-transform duration-300">
                        <div class="w-12 h-12 bg-gradient-to-br from-pink-600 to-pink-800 bg-opacity-40 rounded-xl flex items-center justify-center backdrop-blur-sm shadow-lg">
                            <svg class="w-6 h-6 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                        </div>
                        <div class="text-left">
                            <h3 class="font-semibold text-white text-lg">💰 Wallet Intégré</h3>
                            <p class="text-purple-200 text-sm opacity-80">Gestion financière automatisée et transparente</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 max-w-md animate-fade-in">
            <!-- Mobile Logo -->
            <div class="lg:hidden text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white bg-opacity-20 backdrop-blur-sm rounded-full mb-4 shadow-2xl">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2 bg-gradient-to-r from-white to-purple-200 bg-clip-text text-transparent">Al-Amena Delivery</h1>
                <p class="text-purple-200">Connectez-vous à votre espace</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white bg-opacity-10 backdrop-blur-md rounded-2xl shadow-2xl p-8 border border-white border-opacity-20 transform hover:scale-[1.02] transition-all duration-300">
                <div class="hidden lg:block text-center mb-8">
                    <h2 class="text-3xl font-bold text-white mb-2 bg-gradient-to-r from-white to-purple-200 bg-clip-text text-transparent">Connexion</h2>
                    <p class="text-purple-200 opacity-90">Accédez à votre espace professionnel</p>
                </div>

                <!-- Laravel Session Messages avec style moderne -->
                @if (session('status'))
                <div class="bg-green-500 bg-opacity-20 text-green-200 border border-green-400 border-opacity-50 rounded-xl p-4 mb-6 backdrop-blur-sm">
                    <p class="text-center text-sm">{{ session('status') }}</p>
                </div>
                @endif

                @if ($errors->any())
                <div class="bg-red-500 bg-opacity-20 text-red-200 border border-red-400 border-opacity-50 rounded-xl p-4 mb-6 backdrop-blur-sm">
                    @foreach ($errors->all() as $error)
                        <p class="text-center text-sm">❌ {{ $error }}</p>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf
                    
                    <!-- Email Field avec design moderne -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-purple-200 mb-2">
                            📧 Adresse Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                            </div>
                            <input 
                                type="email" 
                                id="email" 
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="username"
                                class="w-full pl-12 pr-4 py-4 bg-white bg-opacity-10 border border-purple-300 border-opacity-30 rounded-xl text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent focus:bg-opacity-15 transition-all duration-300 backdrop-blur-sm"
                                placeholder="votre@email.com">
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Field avec design moderne -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-purple-200 mb-2">
                            🔐 Mot de passe
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                id="password" 
                                name="password"
                                required
                                autocomplete="current-password"
                                class="w-full pl-12 pr-4 py-4 bg-white bg-opacity-10 border border-purple-300 border-opacity-30 rounded-xl text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent focus:bg-opacity-15 transition-all duration-300 backdrop-blur-sm"
                                placeholder="••••••••">
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me avec style moderne -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                id="remember" 
                                name="remember"
                                class="w-4 h-4 text-purple-600 bg-white bg-opacity-20 border-purple-300 rounded focus:ring-purple-500 focus:ring-2 backdrop-blur-sm">
                            <label for="remember" class="ml-3 text-sm text-purple-200">
                                💾 Se souvenir de moi
                            </label>
                        </div>
                        @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-purple-300 hover:text-white transition-colors underline">
                            🔄 Mot de passe oublié ?
                        </a>
                        @endif
                    </div>

                    <!-- Submit Button avec design moderne -->
                    <button 
                        type="submit"
                        class="w-full py-4 px-6 bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-600 hover:from-purple-700 hover:via-purple-800 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-xl hover:shadow-2xl transform hover:scale-[1.02] transition-all duration-300 flex items-center justify-center space-x-2 backdrop-blur-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        <span>🚀 Se connecter</span>
                    </button>
                </form>

                <!-- Account Types Info avec design moderne -->
                <div class="mt-8 p-6 bg-gradient-to-r from-white to-purple-100 bg-opacity-5 rounded-xl border border-white border-opacity-10 backdrop-blur-sm">
                    <p class="text-purple-200 text-sm font-medium mb-4 text-center">🎯 Comptes de test disponibles :</p>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center p-2 rounded-lg bg-white bg-opacity-5 hover:bg-opacity-10 transition-all">
                            <span class="flex items-center text-purple-200">
                                <div class="w-3 h-3 bg-red-400 rounded-full mr-3 animate-pulse"></div>
                                👑 Superviseur
                            </span>
                            <span class="font-mono text-purple-300 text-xs">supervisor@alamena.tn</span>
                        </div>
                        <div class="flex justify-between items-center p-2 rounded-lg bg-white bg-opacity-5 hover:bg-opacity-10 transition-all">
                            <span class="flex items-center text-purple-200">
                                <div class="w-3 h-3 bg-orange-400 rounded-full mr-3 animate-pulse" style="animation-delay: 0.5s;"></div>
                                🏢 Commercial
                            </span>
                            <span class="font-mono text-purple-300 text-xs">commercial@alamena.tn</span>
                        </div>
                        <div class="flex justify-between items-center p-2 rounded-lg bg-white bg-opacity-5 hover:bg-opacity-10 transition-all">
                            <span class="flex items-center text-purple-200">
                                <div class="w-3 h-3 bg-green-400 rounded-full mr-3 animate-pulse" style="animation-delay: 1s;"></div>
                                🚚 Livreur
                            </span>
                            <span class="font-mono text-purple-300 text-xs">livreur1@alamena.tn</span>
                        </div>
                        <div class="flex justify-between items-center p-2 rounded-lg bg-white bg-opacity-5 hover:bg-opacity-10 transition-all">
                            <span class="flex items-center text-purple-200">
                                <div class="w-3 h-3 bg-blue-400 rounded-full mr-3 animate-pulse" style="animation-delay: 1.5s;"></div>
                                👥 Client
                            </span>
                            <span class="font-mono text-purple-300 text-xs">sarra@boutique.tn</span>
                        </div>
                        <div class="text-center mt-4 pt-3 border-t border-purple-400 border-opacity-30">
                            <span class="text-purple-300">🔑 Mot de passe pour tous : </span>
                            <span class="font-mono font-bold text-white bg-purple-600 bg-opacity-30 px-3 py-1 rounded-lg">password</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer avec style moderne -->
            <div class="mt-6 text-center text-purple-300 text-sm opacity-80">
                <p>&copy; 2025 Al-Amena Delivery. Tous droits réservés.</p>
                <p class="mt-1">🔐 Plateforme sécurisée de gestion de livraison</p>
            </div>
        </div>
    </div>

    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        /* Custom input styles */
        input::placeholder {
            color: rgba(196, 181, 253, 0.6);
        }

        /* Focus states avec effets modernes */
        input:focus {
            background-color: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.3);
        }

        /* Smooth transitions */
        * {
            transition-property: all;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 300ms;
        }

        /* Hover effects */
        .hover-glow:hover {
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.4);
        }
    </style>
</body>
</html>