<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AL-AMENA - Livreur Transit</title>
    <script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Configuration Tailwind pour mobile avec design pale purple */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

        :root {
            --purple-50: #faf7ff;
            --purple-100: #f3f1ff;
            --purple-200: #e9e5ff;
            --purple-300: #d9d1ff;
            --purple-400: #c4b5fd;
            --purple-500: #a78bfa;
            --purple-600: #9333ea;
            --purple-700: #7c3aed;
            --purple-800: #6b21a8;
            --purple-900: #581c87;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-secondary: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            --gradient-accent: linear-gradient(135deg, #d299c2 0%, #fef9d3 100%);
            --shadow-purple: 0 20px 25px -5px rgba(167, 139, 250, 0.1), 0 10px 10px -5px rgba(167, 139, 250, 0.04);
            --shadow-purple-lg: 0 25px 50px -12px rgba(167, 139, 250, 0.25);
        }

        body {
            font-family: 'Poppins', sans-serif;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
            background: var(--gradient-secondary);
            min-height: 100vh;
        }

        /* Styles pour boutons tactiles avec thème purple */
        .btn-primary {
            background: var(--gradient-primary);
            @apply text-white font-semibold py-6 px-8 rounded-2xl text-lg shadow-lg transform transition-all duration-300 active:scale-95;
            box-shadow: var(--shadow-purple);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-purple-lg);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            @apply text-white font-semibold py-6 px-8 rounded-2xl text-lg shadow-lg transform transition-all duration-300 active:scale-95;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            @apply text-white font-semibold py-4 px-6 rounded-xl text-base shadow-lg transform transition-all duration-300 active:scale-95;
        }

        .btn-scan {
            background: var(--gradient-primary);
            @apply text-white font-bold py-8 px-12 rounded-3xl text-2xl shadow-2xl transform transition-all duration-300 active:scale-95;
            min-height: 120px;
            min-width: 280px;
            box-shadow: var(--shadow-purple-lg);
        }

        .btn-scan:hover {
            transform: translateY(-3px);
        }

        /* Glass morphism cards */
        .glass-card {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: var(--shadow-purple);
        }

        .glass-card-strong {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(167, 139, 250, 0.2);
            box-shadow: var(--shadow-purple-lg);
        }

        /* Animation pulse pour scanner */
        .pulse-scanner {
            animation: pulse-scanner 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse-scanner {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(167, 139, 250, 0.7);
            }
            50% {
                opacity: 0.9;
                transform: scale(1.05);
                box-shadow: 0 0 0 20px rgba(167, 139, 250, 0);
            }
        }

        /* Floating animation */
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        /* Status indicators avec thème purple */
        .status-loading {
            background: linear-gradient(135deg, #fef3c7 0%, #fcd34d 100%);
            @apply border-yellow-400 text-yellow-800;
        }
        .status-success {
            background: linear-gradient(135deg, #d1fae5 0%, #10b981 100%);
            @apply border-green-400 text-green-800;
        }
        .status-error {
            background: linear-gradient(135deg, #fee2e2 0%, #ef4444 100%);
            @apply border-red-400 text-red-800;
        }

        /* Gradient text */
        .gradient-text {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Purple gradients */
        .bg-purple-gradient {
            background: var(--gradient-primary);
        }

        .bg-purple-light-gradient {
            background: linear-gradient(135deg, var(--purple-100) 0%, var(--purple-200) 100%);
        }

        /* Custom shadows */
        .shadow-purple {
            box-shadow: var(--shadow-purple);
        }

        .shadow-purple-lg {
            box-shadow: var(--shadow-purple-lg);
        }

        /* Masquer scrollbar mais garder fonctionnalité */
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        /* Animations d'entrée */
        .slide-up {
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Responsive touch targets */
        @media (max-width: 640px) {
            .btn-primary, .btn-success, .btn-danger {
                @apply py-4 px-6 text-base;
                min-height: 56px;
            }
        }
    </style>
</head>
<body class="min-h-screen">
    <div id="app">
        <!-- Loading Splash avec design purple moderne -->
        <div v-if="isLoading" class="fixed inset-0 bg-purple-gradient flex items-center justify-center z-50">
            <div class="text-center text-white">
                <div class="mb-8 relative">
                    <!-- Logo avec effet glow -->
                    <div class="w-24 h-24 mx-auto bg-white bg-opacity-20 rounded-full flex items-center justify-center mb-6 float-animation">
                        <svg class="w-14 h-14 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"></path>
                            <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V8a1 1 0 00-1-1h-3z"></path>
                        </svg>
                    </div>
                    <!-- Rings d'animation autour du logo -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-32 h-32 border-2 border-white border-opacity-30 rounded-full animate-ping"></div>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-40 h-40 border border-white border-opacity-20 rounded-full animate-pulse"></div>
                    </div>
                </div>

                <h1 class="text-4xl font-bold mb-3 tracking-wide">AL-AMENA DELIVERY</h1>
                <p class="text-xl opacity-90 font-medium">Livreur Transit</p>

                <div class="mt-12">
                    <!-- Barre de progression moderne -->
                    <div class="w-64 h-1 bg-white bg-opacity-30 rounded-full mx-auto overflow-hidden">
                        <div class="h-full bg-white rounded-full animate-pulse shadow-lg"></div>
                    </div>
                    <p class="mt-6 text-lg opacity-80 font-medium">Initialisation...</p>
                </div>

                <!-- Dots décoratifs -->
                <div class="absolute bottom-16 left-1/2 transform -translate-x-1/2">
                    <div class="flex space-x-2">
                        <div class="w-2 h-2 bg-white rounded-full opacity-60 animate-bounce"></div>
                        <div class="w-2 h-2 bg-white rounded-full opacity-60 animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-white rounded-full opacity-60 animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notifications modernes -->
        <div class="fixed top-4 left-4 right-4 z-40 space-y-3">
            <div v-for="toast in toasts" :key="toast.id"
                 :class="getToastClass(toast.type)"
                 class="slide-up transform">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center mr-4"
                         :class="toast.type === 'success' ? 'bg-green-100' : toast.type === 'error' ? 'bg-red-100' : 'bg-yellow-100'">
                        <span class="text-xl" v-text="toast.icon"></span>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-base leading-relaxed" v-text="toast.message"></p>
                    </div>
                    <!-- Petite barre de progression pour l'auto-dismiss -->
                    <div class="absolute bottom-0 left-0 h-1 bg-white bg-opacity-30 rounded-b-xl"
                         style="animation: progress 4s linear">
                    </div>
                </div>
            </div>
        </div>

        <style>
        @keyframes progress {
            from { width: 100%; }
            to { width: 0%; }
        }
        </style>

        <!-- ÉCRAN 1: CONNEXION moderne avec purple design -->
        <div v-if="currentScreen === 'login'" class="min-h-screen flex items-center justify-center p-6 relative">
            <!-- Background décoratif -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-4 -right-4 w-72 h-72 bg-purple-400 bg-opacity-20 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-8 -left-8 w-96 h-96 bg-purple-300 bg-opacity-15 rounded-full blur-3xl"></div>
            </div>

            <div class="w-full max-w-md relative z-10">
                <div class="glass-card-strong rounded-3xl shadow-purple-lg p-8 slide-up">
                    <!-- Logo avec design moderne -->
                    <div class="text-center mb-10">
                        <div class="w-24 h-24 bg-purple-gradient rounded-full flex items-center justify-center mx-auto mb-6 float-animation shadow-purple-lg">
                            <svg class="w-14 h-14 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"></path>
                                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V8a1 1 0 00-1-1h-3z"></path>
                            </svg>
                        </div>
                        <h1 class="text-3xl font-bold gradient-text mb-2">AL-AMENA DELIVERY</h1>
                        <p class="text-gray-600 text-lg font-medium">Livreur Transit</p>
                    </div>

                    <!-- Formulaire moderne -->
                    <form @submit.prevent="login" class="space-y-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Nom d'utilisateur</label>
                            <input v-model="loginForm.username" type="text" required
                                   class="w-full px-4 py-4 text-lg border-2 border-purple-200 rounded-xl focus:ring-4 focus:ring-purple-300 focus:ring-opacity-50 focus:border-purple-400 transition-all duration-300 bg-white bg-opacity-80 backdrop-blur-sm">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Mot de passe</label>
                            <input v-model="loginForm.password" type="password" required
                                   class="w-full px-4 py-4 text-lg border-2 border-purple-200 rounded-xl focus:ring-4 focus:ring-purple-300 focus:ring-opacity-50 focus:border-purple-400 transition-all duration-300 bg-white bg-opacity-80 backdrop-blur-sm">
                        </div>

                        <button type="submit" :disabled="loginLoading"
                                class="w-full btn-primary mt-8 relative overflow-hidden">
                            <span v-if="loginLoading" class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Connexion en cours...
                            </span>
                            <span v-else class="font-bold tracking-wider">SE CONNECTER</span>
                        </button>
                    </form>

                    <!-- Petite décoration en bas -->
                    <div class="mt-8 flex justify-center">
                        <div class="flex space-x-2">
                            <div class="w-2 h-2 bg-purple-400 rounded-full"></div>
                            <div class="w-2 h-2 bg-purple-300 rounded-full"></div>
                            <div class="w-2 h-2 bg-purple-200 rounded-full"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ÉCRAN 2: TABLEAU DE BORD moderne avec purple design -->
        <div v-if="currentScreen === 'dashboard'" class="min-h-screen">
            <!-- Header avec gradient moderne -->
            <div class="bg-purple-gradient text-white p-6 shadow-purple-lg relative overflow-hidden">
                <!-- Décoration background -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-white bg-opacity-10 rounded-full -mr-16 -mt-16"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-white bg-opacity-5 rounded-full -ml-12 -mb-12"></div>

                <div class="flex items-center justify-between relative z-10">
                    <div class="fade-in">
                        <h1 class="text-2xl font-bold mb-1">Bonjour <span v-text="user.name" class="font-black"></span> ✨</h1>
                        <p class="text-purple-100 font-medium" v-text="getCurrentDate()"></p>
                    </div>
                    <button @click="logout"
                            class="bg-white bg-opacity-20 hover:bg-opacity-30 px-4 py-2 rounded-xl backdrop-blur-sm transition-all duration-300 font-medium">
                        🚪 Déconnexion
                    </button>
                </div>
            </div>

            <!-- Contenu principal moderne -->
            <div class="p-6 space-y-6">
                <!-- Carte tournée du jour avec design moderne -->
                <div v-if="todayRoute" class="glass-card-strong rounded-3xl shadow-purple-lg p-8 text-center slide-up relative overflow-hidden">
                    <!-- Décoration arrière-plan -->
                    <div class="absolute top-0 right-0 w-20 h-20 bg-purple-400 bg-opacity-10 rounded-full -mr-10 -mt-10"></div>
                    <div class="absolute bottom-0 left-0 w-16 h-16 bg-green-400 bg-opacity-10 rounded-full -ml-8 -mb-8"></div>

                    <div class="mb-8 relative z-10">
                        <div class="w-20 h-20 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center mx-auto mb-6 float-animation shadow-lg">
                            <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">🎯 Tournée du jour</h2>

                        <!-- Route avec design moderne -->
                        <div class="bg-purple-light-gradient rounded-2xl p-6 mb-6">
                            <div class="text-4xl font-black gradient-text mb-3">
                                <span v-text="todayRoute.from"></span> → <span v-text="todayRoute.to"></span>
                            </div>
                            <div class="flex items-center justify-center space-x-4 text-gray-600">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-purple-400 bg-opacity-20 rounded-full flex items-center justify-center mr-2">
                                        📦
                                    </div>
                                    <span class="font-semibold"><span v-text="todayRoute.boxes_count"></span> boîtes</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button @click="startRoute" class="btn-primary w-full relative overflow-hidden">
                        <span class="flex items-center justify-center">
                            🚛 <span class="ml-2 font-bold tracking-wider">COMMENCER LA TOURNÉE</span>
                        </span>
                    </button>
                </div>

                <!-- Carte aucune tournée avec design moderne -->
                <div v-else class="glass-card-strong rounded-3xl shadow-purple-lg p-8 text-center slide-up relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-gray-400 bg-opacity-10 rounded-full -mr-8 -mt-8"></div>

                    <div class="mb-6 relative z-10">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6 opacity-60">
                            <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-3">😴 Aucune tournée assignée</h2>
                        <p class="text-gray-600 font-medium">Aucune tournée n'est programmée pour aujourd'hui.</p>
                        <p class="text-purple-600 text-sm mt-2">Revenez plus tard ou contactez votre superviseur.</p>
                    </div>
                </div>

                <!-- Menu d'accès rapide moderne -->
                <div class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">🔧 Actions rapides</h3>

                    <div class="grid grid-cols-2 gap-4">
                        <button @click="currentScreen = 'history'"
                                class="glass-card rounded-2xl shadow-purple p-6 text-center hover:shadow-purple-lg transition-all duration-300 transform hover:scale-105">
                            <div class="w-14 h-14 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                                <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <p class="font-bold text-gray-800">📊 Historique</p>
                            <p class="text-xs text-gray-600 mt-1">Voir mes tournées</p>
                        </button>

                        <button @click="refreshRoute"
                                class="glass-card rounded-2xl shadow-purple p-6 text-center hover:shadow-purple-lg transition-all duration-300 transform hover:scale-105">
                            <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                                <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <p class="font-bold text-gray-800">🔄 Actualiser</p>
                            <p class="text-xs text-gray-600 mt-1">Recharger données</p>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ÉCRAN 3: TOURNÉE ACTIVE avec design purple moderne -->
        <div v-if="currentScreen === 'active-route'" class="min-h-screen">
            <!-- Header fixe avec gradient purple -->
            <div class="bg-purple-gradient text-white p-4 shadow-purple-lg sticky top-0 z-30 relative overflow-hidden">
                <!-- Décoration background -->
                <div class="absolute top-0 right-0 w-20 h-20 bg-white bg-opacity-10 rounded-full -mr-10 -mt-10"></div>

                <div class="flex items-center justify-between relative z-10">
                    <button @click="currentScreen = 'dashboard'"
                            class="p-3 bg-white bg-opacity-20 rounded-xl hover:bg-opacity-30 transition-all duration-300">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <div class="text-center">
                        <h1 class="text-xl font-bold tracking-wide">
                            <span v-text="activeRoute.from"></span> → <span v-text="activeRoute.to"></span>
                        </h1>
                        <p class="text-purple-100 text-sm font-medium">🚛 Tournée en cours</p>
                    </div>
                    <button @click="showRouteMenu = !showRouteMenu"
                            class="p-3 bg-white bg-opacity-20 rounded-xl hover:bg-opacity-30 transition-all duration-300">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Zone principale de scan moderne -->
            <div class="p-6 space-y-6">
                <!-- Status de la tournée avec design moderne -->
                <div class="glass-card-strong rounded-3xl shadow-purple-lg p-8 slide-up relative overflow-hidden">
                    <!-- Décoration background -->
                    <div class="absolute top-0 right-0 w-16 h-16 bg-purple-400 bg-opacity-10 rounded-full -mr-8 -mt-8"></div>

                    <div class="text-center relative z-10">
                        <div class="text-sm text-purple-600 font-semibold mb-3" v-text="routeStatus.location"></div>
                        <div class="text-3xl font-black gradient-text mb-6" v-text="routeStatus.action"></div>

                        <!-- Bouton scanner principal moderne -->
                        <button @click="openScanner"
                                :class="['btn-scan mx-auto flex items-center justify-center mb-8 relative overflow-hidden', scannerActive ? 'pulse-scanner' : '']"
                                :disabled="scannerLoading">
                            <div class="text-center relative z-10">
                                <div class="text-5xl mb-3" v-text="routeStatus.icon"></div>
                                <div class="font-bold tracking-wider" v-text="routeStatus.buttonText"></div>
                            </div>
                            <!-- Animation ring quand actif -->
                            <div v-if="scannerActive" class="absolute inset-0 border-4 border-white border-opacity-30 rounded-3xl animate-ping"></div>
                        </button>

                        <!-- Input manuel moderne -->
                        <div class="bg-purple-light-gradient rounded-2xl p-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">⌨️ Saisie manuelle</label>
                            <input v-model="manualCode" @keyup.enter="processManualScan"
                                   placeholder="Tapez le code de la boîte..."
                                   class="w-full px-4 py-4 text-center border-2 border-purple-200 rounded-xl focus:ring-4 focus:ring-purple-300 focus:ring-opacity-50 focus:border-purple-400 transition-all duration-300 bg-white bg-opacity-90 backdrop-blur-sm font-mono text-lg">
                        </div>
                    </div>
                </div>

                <!-- Manifeste en direct moderne -->
                <div class="glass-card-strong rounded-3xl shadow-purple-lg p-6 slide-up relative overflow-hidden">
                    <!-- Décoration background -->
                    <div class="absolute bottom-0 left-0 w-12 h-12 bg-green-400 bg-opacity-10 rounded-full -ml-6 -mb-6"></div>

                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold gradient-text flex items-center">
                                📋 Manifeste Live
                            </h3>
                            <span class="bg-purple-gradient text-white text-sm font-bold px-4 py-2 rounded-full shadow-lg" v-text="loadedBoxes.length + ' boîtes'"></span>
                        </div>

                        <!-- État vide moderne -->
                        <div v-if="loadedBoxes.length === 0" class="text-center py-10">
                            <div class="w-20 h-20 bg-purple-light-gradient rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg class="w-12 h-12 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-gray-800 mb-2">🚛 Camion vide</h4>
                            <p class="text-gray-600 font-medium">Scannez des boîtes pour commencer le chargement</p>
                        </div>

                        <!-- Liste des boîtes avec design moderne -->
                        <div v-else class="space-y-3 max-h-80 overflow-y-auto hide-scrollbar">
                            <div v-for="box in loadedBoxes" :key="box.code"
                                 class="flex items-center justify-between p-4 bg-purple-light-gradient rounded-2xl slide-up transform hover:scale-105 transition-all duration-300">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center shadow-lg">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-800 font-mono" v-text="box.code"></div>
                                        <div class="text-sm text-gray-600 font-medium">
                                            🎯 <span v-text="box.destination"></span> • 📦 <span v-text="box.packages_count"></span> colis
                                        </div>
                                        <div class="text-xs text-purple-600 font-medium">
                                            ⏰ Chargé à <span v-text="box.loaded_at"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="w-4 h-4 bg-gradient-to-br from-green-400 to-green-600 rounded-full shadow-lg animate-pulse"></div>
                            </div>
                        </div>

                        <!-- Bouton terminer tournée moderne -->
                        <div v-if="loadedBoxes.length === 0 && hasStartedRoute" class="mt-8">
                            <button @click="finishRoute" class="w-full btn-success relative overflow-hidden">
                                <span class="flex items-center justify-center">
                                    ✅ <span class="ml-2 font-bold tracking-wider">TERMINER LA TOURNÉE</span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ÉCRAN 4: HISTORIQUE -->
        <div v-if="currentScreen === 'history'" class="min-h-screen bg-gray-50">
            <!-- Header -->
            <div class="bg-purple-600 text-white p-4 shadow-lg">
                <div class="flex items-center">
                    <button @click="currentScreen = 'dashboard'" class="p-2 mr-3">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <h1 class="text-xl font-bold">Historique des Tournées</h1>
                </div>
            </div>

            <!-- Liste historique -->
            <div class="p-6">
                <div class="space-y-4">
                    <div v-for="route in routeHistory" :key="route.id"
                         class="bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-3">
                            <div class="font-bold text-lg text-gray-800">
                                <span v-text="route.from"></span> → <span v-text="route.to"></span>
                            </div>
                            <span :class="getStatusClass(route.status)" v-text="getStatusText(route.status)">
                            </span>
                        </div>
                        <div class="text-sm text-gray-600 mb-2">
                            📅 <span v-text="route.date"></span> • 📦 <span v-text="route.boxes_count"></span> boîtes
                        </div>
                        <div class="text-xs text-gray-500">
                            Durée: <span v-text="route.duration || 'N/A'"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scanner Modal (Simulation caméra) -->
        <div v-if="showScanner" class="fixed inset-0 bg-black z-50">
            <div class="relative h-full">
                <!-- Simulation caméra -->
                <div class="absolute inset-0 bg-gradient-to-b from-gray-900 to-black flex items-center justify-center">
                    <div class="text-center text-white">
                        <div class="w-64 h-64 border-4 border-white border-dashed rounded-xl mb-6 flex items-center justify-center">
                            <div class="animate-pulse">
                                <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="text-xl mb-8">Positionnez le code dans le cadre</p>

                        <!-- Simulation détection automatique -->
                        <div v-if="scannerActive" class="space-y-4">
                            <div class="animate-pulse text-yellow-400">
                                🔍 Détection en cours...
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Boutons de contrôle -->
                <div class="absolute bottom-6 left-6 right-6">
                    <div class="flex justify-center space-x-4">
                        <button @click="simulateSuccessfulScan" class="btn-success">
                            ✅ Simuler Scan Réussi
                        </button>
                        <button @click="closeScanner" class="btn-danger">
                            ❌ Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const { createApp } = Vue;

        createApp({
            data() {
                return {
                    // États généraux
                    isLoading: true,
                    currentScreen: 'login', // login, dashboard, active-route, history
                    toasts: [],

                    // Authentification
                    user: null,
                    userToken: null,
                    loginForm: {
                        username: 'transit@al-amena.com',
                        password: '123456'
                    },
                    loginLoading: false,

                    // Tournées
                    todayRoute: null,
                    activeRoute: null,
                    routeHistory: [],
                    hasStartedRoute: false,

                    // Scanner
                    showScanner: false,
                    scannerActive: false,
                    scannerLoading: false,
                    manualCode: '',

                    // Manifeste
                    loadedBoxes: [],

                    // Menu
                    showRouteMenu: false
                }
            },

            computed: {
                routeStatus() {
                    if (!this.activeRoute) return {};

                    const isAtOrigin = this.loadedBoxes.length === 0;

                    return {
                        location: isAtOrigin ? `📍 Dépôt ${this.activeRoute.from}` : `📍 En route vers ${this.activeRoute.to}`,
                        action: isAtOrigin ? 'CHARGEMENT' : 'DÉCHARGEMENT',
                        icon: isAtOrigin ? '➕' : '✔️',
                        buttonText: isAtOrigin ? 'SCANNER POUR CHARGER' : 'SCANNER POUR DÉCHARGER'
                    };
                }
            },

            mounted() {
                this.initApp();
            },

            methods: {
                // Initialisation
                async initApp() {
                    // Simulation du chargement initial
                    await this.delay(2000);

                    // Vérifier si utilisateur connecté (localStorage)
                    const savedUser = localStorage.getItem('transit_user');
                    const savedToken = localStorage.getItem('transit_token');
                    if (savedUser && savedToken) {
                        this.user = JSON.parse(savedUser);
                        this.userToken = savedToken;

                        // Restaurer l'état de la session
                        await this.restoreAppState();

                        // Commencer la synchronisation périodique
                        this.startPeriodicSync();
                    } else {
                        this.currentScreen = 'login';
                    }

                    this.isLoading = false;
                },

                // Restaurer l'état de l'application
                async restoreAppState() {
                    try {
                        // Charger la tournée du jour
                        await this.loadTodayRoute();

                        // Si nous avons une tournée active, vérifier son statut
                        if (this.todayRoute && this.todayRoute.status === 'IN_PROGRESS') {
                            this.hasStartedRoute = true;
                            this.activeRoute = this.todayRoute;
                            this.currentScreen = 'active-route';

                            // Charger le manifeste actuel
                            await this.loadCurrentManifest();
                        } else {
                            this.currentScreen = 'dashboard';
                        }

                        // Charger l'historique
                        await this.loadRouteHistory();

                    } catch (error) {
                        console.error('Erreur restauration état:', error);
                        this.currentScreen = 'dashboard';
                    }
                },

                // Synchronisation périodique
                startPeriodicSync() {
                    // Demander permission pour les notifications
                    this.requestNotificationPermission();

                    // Synchroniser toutes les 30 secondes si on a une tournée active
                    setInterval(async () => {
                        if (this.hasStartedRoute && this.userToken) {
                            try {
                                const oldManifestCount = this.loadedBoxes.length;
                                await this.loadTodayRoute();
                                await this.loadCurrentManifest();

                                // Détecter changements dans le manifeste
                                const newManifestCount = this.loadedBoxes.length;
                                if (newManifestCount !== oldManifestCount) {
                                    this.showNotification(
                                        'Manifeste mis à jour',
                                        `${newManifestCount} boîte(s) dans votre manifeste`,
                                        'update'
                                    );
                                }
                            } catch (error) {
                                console.error('Erreur sync périodique:', error);
                            }
                        }
                    }, 30000);

                    // Synchroniser l'historique toutes les 2 minutes
                    setInterval(async () => {
                        if (this.userToken) {
                            try {
                                await this.loadRouteHistory();
                            } catch (error) {
                                console.error('Erreur sync historique:', error);
                            }
                        }
                    }, 120000);
                },

                // Gestion des notifications
                async requestNotificationPermission() {
                    if ('Notification' in window && Notification.permission === 'default') {
                        try {
                            const permission = await Notification.requestPermission();
                            if (permission === 'granted') {
                                this.showToast('🔔 Notifications activées', 'success');
                            }
                        } catch (error) {
                            console.error('Erreur permission notifications:', error);
                        }
                    }
                },

                showNotification(title, body, type = 'info') {
                    // Notification système si permission accordée
                    if ('Notification' in window && Notification.permission === 'granted') {
                        const notification = new Notification(title, {
                            body: body,
                            icon: '/images/logo-transit.png',
                            badge: '/images/logo-transit.png',
                            tag: 'transit-driver',
                            requireInteraction: type === 'urgent'
                        });

                        // Auto-fermer après 5 secondes sauf si urgent
                        if (type !== 'urgent') {
                            setTimeout(() => notification.close(), 5000);
                        }
                    }

                    // Toast dans l'interface
                    this.showToast(`🔔 ${title}: ${body}`, type === 'urgent' ? 'error' : 'info');

                    // Vibration pour attirer l'attention
                    this.triggerNotificationVibration();
                },

                // Authentification
                async login() {
                    this.loginLoading = true;

                    try {
                        const response = await fetch('/api/transit-driver/login', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify({
                                username: this.loginForm.username,
                                password: this.loginForm.password
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.user = data.user;
                            this.userToken = data.token;
                            localStorage.setItem('transit_user', JSON.stringify(this.user));
                            localStorage.setItem('transit_token', this.userToken);
                            this.showToast('✅ Connexion réussie!', 'success');
                            this.currentScreen = 'dashboard';
                            await this.loadTodayRoute();
                        } else {
                            this.showToast(`❌ ${data.message}`, 'error');
                        }
                    } catch (error) {
                        console.error('Erreur de connexion:', error);
                        this.showToast('❌ Erreur de connexion', 'error');
                    }

                    this.loginLoading = false;
                },

                async logout() {
                    if (this.userToken) {
                        try {
                            await fetch('/api/transit-driver/logout', {
                                method: 'POST',
                                headers: {
                                    'Authorization': `Bearer ${this.userToken}`,
                                    'Content-Type': 'application/json'
                                }
                            });
                        } catch (error) {
                            console.error('Erreur lors de la déconnexion:', error);
                        }
                    }

                    localStorage.removeItem('transit_user');
                    localStorage.removeItem('transit_token');
                    this.user = null;
                    this.userToken = null;
                    this.currentScreen = 'login';
                    this.showToast('👋 Déconnecté', 'success');
                },

                // Gestion des tournées
                async loadTodayRoute() {
                    if (!this.userToken) return;

                    try {
                        const response = await fetch('/api/transit-driver/ma-tournee', {
                            headers: {
                                'Authorization': `Bearer ${this.userToken}`,
                                'Content-Type': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.todayRoute = data.route;
                        } else {
                            this.todayRoute = null;
                            this.showToast('❌ Erreur lors du chargement', 'error');
                        }
                    } catch (error) {
                        console.error('Erreur loadTodayRoute:', error);
                        this.showToast('❌ Erreur de connexion', 'error');
                    }
                },

                async refreshRoute() {
                    this.showToast('🔄 Actualisation...', 'info');
                    await this.loadTodayRoute();
                    this.showToast('✅ Tournée actualisée', 'success');
                },

                async startRoute() {
                    if (!this.userToken || !this.todayRoute) return;

                    try {
                        const response = await fetch('/api/transit-driver/start-route', {
                            method: 'POST',
                            headers: {
                                'Authorization': `Bearer ${this.userToken}`,
                                'Content-Type': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.activeRoute = { ...this.todayRoute, ...data.route };
                            this.hasStartedRoute = true;
                            this.currentScreen = 'active-route';

                            // Charger le manifeste actuel (boîtes déjà chargées s'il y en a)
                            await this.loadCurrentManifest();

                            this.showToast('🚛 Tournée démarrée!', 'success');
                        } else {
                            this.showToast(`❌ ${data.message}`, 'error');
                        }
                    } catch (error) {
                        console.error('Erreur startRoute:', error);
                        this.showToast('❌ Erreur lors du démarrage', 'error');
                    }
                },

                async finishRoute() {
                    if (this.loadedBoxes.length > 0) {
                        this.showToast('❌ Déchargez toutes les boîtes d\'abord', 'error');
                        return;
                    }

                    if (!this.userToken) return;

                    try {
                        this.showToast('🔄 Finalisation...', 'info');

                        const response = await fetch('/api/transit-driver/finish-route', {
                            method: 'POST',
                            headers: {
                                'Authorization': `Bearer ${this.userToken}`,
                                'Content-Type': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Recharger l'historique
                            await this.loadRouteHistory();

                            this.activeRoute = null;
                            this.hasStartedRoute = false;
                            this.currentScreen = 'dashboard';
                            this.todayRoute = null;

                            this.showToast('🎉 Tournée terminée avec succès!', 'success');
                        } else {
                            this.showToast(`❌ ${data.message}`, 'error');
                        }
                    } catch (error) {
                        console.error('Erreur finishRoute:', error);
                        this.showToast('❌ Erreur lors de la finalisation', 'error');
                    }
                },

                // Gestion du scanner
                openScanner() {
                    this.showScanner = true;
                    this.scannerActive = true;
                },

                closeScanner() {
                    this.showScanner = false;
                    this.scannerActive = false;
                },

                async processManualScan() {
                    if (!this.manualCode.trim()) return;
                    await this.processScan(this.manualCode.trim());
                    this.manualCode = '';
                },

                async processScan(boxCode) {
                    this.scannerLoading = true;
                    this.closeScanner();

                    // Déterminer l'action basée sur le contexte de la tournée
                    const hasLoadedBoxes = this.loadedBoxes.length > 0;

                    // Si on a des boîtes chargées et qu'on scanne une boîte de notre manifeste = déchargement
                    // Si on n'a pas de boîtes ou qu'on scanne une nouvelle boîte = chargement
                    const isUnloading = hasLoadedBoxes && this.loadedBoxes.some(box => box.code === boxCode);

                    if (isUnloading) {
                        await this.processUnloadingScan(boxCode);
                    } else {
                        await this.processLoadingScan(boxCode);
                    }

                    this.scannerLoading = false;
                },

                // Charger le manifeste actuel depuis le serveur
                async loadCurrentManifest() {
                    if (!this.userToken) return;

                    try {
                        const response = await fetch('/api/transit-driver/manifeste', {
                            headers: {
                                'Authorization': `Bearer ${this.userToken}`,
                                'Content-Type': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.loadedBoxes = data.manifest.map(box => ({
                                code: box.code,
                                destination: box.destination_governorate,
                                packages_count: box.packages_count,
                                loaded_at: new Date().toLocaleTimeString('fr-FR'),
                                status: box.status
                            }));
                        }
                    } catch (error) {
                        console.error('Erreur loadCurrentManifest:', error);
                    }
                },

                async processLoadingScan(boxCode) {
                    if (!this.userToken) {
                        this.showToast('❌ Erreur d\'authentification', 'error');
                        return;
                    }

                    this.showToast('🔄 Vérification de la boîte...', 'info');

                    try {
                        const response = await fetch('/api/transit-driver/scanner/charger', {
                            method: 'POST',
                            headers: {
                                'Authorization': `Bearer ${this.userToken}`,
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ box_code: boxCode })
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Ajouter la boîte au manifeste local
                            const loadedBox = {
                                code: data.box.code,
                                destination: data.box.destination_governorate,
                                packages_count: data.box.packages_count,
                                loaded_at: new Date().toLocaleTimeString('fr-FR'),
                                status: 'loaded'
                            };

                            this.loadedBoxes.push(loadedBox);
                            this.showToast(data.message, 'success');
                            this.triggerSuccessVibration();
                        } else {
                            this.showToast(`❌ ${data.message}`, 'error');
                            this.triggerErrorVibration();
                        }
                    } catch (error) {
                        console.error('Erreur processLoadingScan:', error);
                        this.showToast('❌ Erreur lors du chargement', 'error');
                        this.triggerErrorVibration();
                    }
                },

                async processUnloadingScan(boxCode) {
                    if (!this.userToken) {
                        this.showToast('❌ Erreur d\'authentification', 'error');
                        return;
                    }

                    this.showToast('🔄 Déchargement...', 'info');

                    try {
                        const response = await fetch('/api/transit-driver/scanner/decharger', {
                            method: 'POST',
                            headers: {
                                'Authorization': `Bearer ${this.userToken}`,
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ box_code: boxCode })
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Retirer la boîte du manifeste local
                            const boxIndex = this.loadedBoxes.findIndex(box => box.code === boxCode);
                            if (boxIndex > -1) {
                                this.loadedBoxes.splice(boxIndex, 1);
                            }
                            this.showToast(data.message, 'success');
                            this.triggerSuccessVibration();
                        } else {
                            this.showToast(`❌ ${data.message}`, 'error');
                            this.triggerErrorVibration();
                        }
                    } catch (error) {
                        console.error('Erreur processUnloadingScan:', error);
                        this.showToast('❌ Erreur lors du déchargement', 'error');
                        this.triggerErrorVibration();
                    }
                },

                // Historique
                async loadRouteHistory() {
                    if (!this.userToken) return;

                    try {
                        const response = await fetch('/api/transit-driver/historique', {
                            headers: {
                                'Authorization': `Bearer ${this.userToken}`,
                                'Content-Type': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.routeHistory = data.history;
                        } else {
                            this.routeHistory = [];
                        }
                    } catch (error) {
                        console.error('Erreur loadRouteHistory:', error);
                        this.routeHistory = [];
                    }
                },

                // Utilitaires
                showToast(message, type = 'info') {
                    const icons = {
                        success: '✅',
                        error: '❌',
                        info: '🔄'
                    };

                    const toast = {
                        id: Date.now(),
                        message,
                        type,
                        icon: icons[type] || '📢'
                    };

                    this.toasts.push(toast);

                    // Auto-remove après 4 secondes
                    setTimeout(() => {
                        const index = this.toasts.findIndex(t => t.id === toast.id);
                        if (index > -1) {
                            this.toasts.splice(index, 1);
                        }
                    }, 4000);
                },

                delay(ms) {
                    return new Promise(resolve => setTimeout(resolve, ms));
                },

                getCurrentDate() {
                    return new Date().toLocaleDateString('fr-FR', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                },

                getStatusClass(status) {
                    const baseClasses = 'px-3 py-1 rounded-full text-sm font-medium';
                    if (status === 'completed') {
                        return `${baseClasses} bg-green-100 text-green-800`;
                    } else if (status === 'cancelled') {
                        return `${baseClasses} bg-red-100 text-red-800`;
                    } else {
                        return `${baseClasses} bg-yellow-100 text-yellow-800`;
                    }
                },

                getStatusText(status) {
                    if (status === 'completed') {
                        return 'Terminée';
                    } else if (status === 'cancelled') {
                        return 'Annulée';
                    } else {
                        return 'En cours';
                    }
                },

                getToastClass(type) {
                    const baseClasses = 'p-4 rounded-xl shadow-lg border-l-4 transform transition-all duration-300';
                    if (type === 'success') {
                        return `${baseClasses} status-success`;
                    } else if (type === 'error') {
                        return `${baseClasses} status-error`;
                    } else {
                        return `${baseClasses} status-loading`;
                    }
                },

                // Feedback haptique (vibrations)
                triggerSuccessVibration() {
                    if ('vibrate' in navigator) {
                        // Pattern: court-pause-court pour succès
                        navigator.vibrate([100, 50, 100]);
                    }
                },

                triggerErrorVibration() {
                    if ('vibrate' in navigator) {
                        // Pattern: long-pause-long pour erreur
                        navigator.vibrate([200, 100, 200, 100, 200]);
                    }
                },

                triggerNotificationVibration() {
                    if ('vibrate' in navigator) {
                        // Pattern: simple pour notification
                        navigator.vibrate(150);
                    }
                }
            }
        }).mount('#app');
    </script>
</body>
</html>