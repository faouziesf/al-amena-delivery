<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Al-Amena Delivery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
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
    </div>

    <!-- Main Container -->
    <div class="relative min-h-screen flex items-center justify-center p-4" x-data="loginForm()">
        
        <!-- Left Side - Branding -->
        <div class="hidden lg:flex lg:w-1/2 items-center justify-center animate-slide-in">
            <div class="text-center text-white">
                <!-- Logo -->
                <div class="mb-8">
                    <div class="inline-flex items-center justify-center w-32 h-32 bg-white bg-opacity-20 backdrop-blur-sm rounded-full mb-6 animate-float">
                        <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h1 class="text-5xl font-bold mb-4 bg-gradient-to-r from-white to-purple-200 bg-clip-text text-transparent">
                        Al-Amena
                    </h1>
                    <h2 class="text-3xl font-light text-purple-200">
                        Delivery
                    </h2>
                </div>

                <div class="space-y-4 text-purple-100">
                    <p class="text-xl">
                        La plateforme de livraison intelligente
                    </p>
                    <p class="text-lg font-light">
                        Gestion complète • Sécurité maximale • Performance optimale
                    </p>
                </div>

                <!-- Features -->
                <div class="mt-12 grid grid-cols-1 gap-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-purple-600 bg-opacity-30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div class="text-left">
                            <h3 class="font-semibold text-white">Sécurité Financière</h3>
                            <p class="text-purple-200 text-sm">Système anti-panne avec récupération automatique</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-purple-600 bg-opacity-30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div class="text-left">
                            <h3 class="font-semibold text-white">Performance Temps Réel</h3>
                            <p class="text-purple-200 text-sm">Suivi instantané et notifications intelligentes</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-purple-600 bg-opacity-30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                        </div>
                        <div class="text-left">
                            <h3 class="font-semibold text-white">Wallet Intégré</h3>
                            <p class="text-purple-200 text-sm">Gestion financière automatisée et transparente</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 max-w-md animate-fade-in">
            <!-- Mobile Logo -->
            <div class="lg:hidden text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white bg-opacity-20 backdrop-blur-sm rounded-full mb-4">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Al-Amena Delivery</h1>
                <p class="text-purple-200">Connectez-vous à votre espace</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white bg-opacity-10 backdrop-blur-md rounded-2xl shadow-2xl p-8 border border-white border-opacity-20">
                <div class="hidden lg:block text-center mb-8">
                    <h2 class="text-3xl font-bold text-white mb-2">Connexion</h2>
                    <p class="text-purple-200">Accédez à votre espace professionnel</p>
                </div>

                <!-- Status Messages -->
                <div x-show="message" x-text="message" 
                     :class="messageType === 'error' ? 'bg-red-500 bg-opacity-20 text-red-200 border border-red-400' : 'bg-green-500 bg-opacity-20 text-green-200 border border-green-400'"
                     class="rounded-lg p-4 mb-6 text-center"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100">
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-purple-200 mb-2">
                            Adresse Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                            </div>
                            <input 
                                type="email" 
                                id="email" 
                                name="email"
                                x-model="form.email"
                                required
                                autocomplete="username"
                                class="w-full pl-10 pr-4 py-3 bg-white bg-opacity-10 border border-purple-300 border-opacity-30 rounded-lg text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition-all duration-300"
                                placeholder="votre@email.com">
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-purple-200 mb-2">
                            Mot de passe
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input 
                                :type="showPassword ? 'text' : 'password'" 
                                id="password" 
                                name="password"
                                x-model="form.password"
                                required
                                autocomplete="current-password"
                                class="w-full pl-10 pr-12 py-3 bg-white bg-opacity-10 border border-purple-300 border-opacity-30 rounded-lg text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition-all duration-300"
                                placeholder="••••••••">
                            <button 
                                type="button" 
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-purple-300 hover:text-white transition-colors">
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                id="remember" 
                                name="remember"
                                x-model="form.remember"
                                class="w-4 h-4 text-purple-600 bg-white bg-opacity-20 border-purple-300 rounded focus:ring-purple-500 focus:ring-2">
                            <label for="remember" class="ml-2 text-sm text-purple-200">
                                Se souvenir de moi
                            </label>
                        </div>
                        <a href="#" class="text-sm text-purple-300 hover:text-white transition-colors">
                            Mot de passe oublié ?
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        :disabled="loading"
                        class="w-full py-3 px-4 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                        :class="loading ? 'cursor-not-allowed' : 'cursor-pointer'">
                        <span x-show="!loading" class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            Se connecter
                        </span>
                        <span x-show="loading" class="flex items-center justify-center">
                            <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Connexion en cours...
                        </span>
                    </button>
                </form>

                <!-- Account Types Info -->
                <div class="mt-8 p-4 bg-white bg-opacity-5 rounded-lg border border-white border-opacity-10">
                    <p class="text-purple-200 text-xs font-medium mb-3 text-center">Types de comptes disponibles :</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-blue-400 rounded-full animate-pulse"></div>
                            <span class="text-xs text-purple-200">Client</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse" style="animation-delay: 0.5s;"></div>
                            <span class="text-xs text-purple-200">Livreur</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-orange-400 rounded-full animate-pulse" style="animation-delay: 1s;"></div>
                            <span class="text-xs text-purple-200">Commercial</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-red-400 rounded-full animate-pulse" style="animation-delay: 1.5s;"></div>
                            <span class="text-xs text-purple-200">Superviseur</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-6 text-center text-purple-300 text-sm">
                <p>&copy; 2025 Al-Amena Delivery. Tous droits réservés.</p>
                <p class="mt-1">Plateforme sécurisée de gestion de livraison</p>
            </div>
        </div>
    </div>

    <script>
        function loginForm() {
            return {
                form: {
                    email: '',
                    password: '',
                    remember: false
                },
                showPassword: false,
                loading: false,
                message: '',
                messageType: '',

                async submitForm() {
                    if (this.loading) return;
                    
                    // Validation basique
                    if (!this.form.email || !this.form.password) {
                        this.showMessage('Veuillez remplir tous les champs', 'error');
                        return;
                    }

                    this.loading = true;
                    this.message = '';

                    try {
                        const formData = new FormData();
                        formData.append('email', this.form.email);
                        formData.append('password', this.form.password);
                        if (this.form.remember) {
                            formData.append('remember', '1');
                        }
                        
                        // Ajouter le token CSRF
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        if (csrfToken) {
                            formData.append('_token', csrfToken);
                        }

                        const response = await fetch('/login', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        });

                        if (response.ok) {
                            this.showMessage('Connexion réussie ! Redirection...', 'success');
                            setTimeout(() => {
                                window.location.href = '/dashboard';
                            }, 1500);
                        } else {
                            const errorData = await response.json().catch(() => ({}));
                            this.showMessage(errorData.message || 'Identifiants incorrects', 'error');
                        }
                    } catch (error) {
                        this.showMessage('Erreur de connexion. Veuillez réessayer.', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                showMessage(text, type) {
                    this.message = text;
                    this.messageType = type;
                    setTimeout(() => {
                        this.message = '';
                    }, 5000);
                }
            }
        }
    </script>
</body>
</html>