@extends('layouts.deliverer')

@section('title', 'Guide Scanner QR')

@section('content')
<div x-data="qrScannerGuide" class="max-w-4xl mx-auto p-4 space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl p-6">
        <div class="flex items-center space-x-4">
            <a href="{{ route('deliverer.help.index') }}" class="text-green-100 hover:text-white">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold mb-2">Guide Scanner QR</h1>
                <p class="text-green-100">Maîtrisez l'utilisation du scanner intégré</p>
            </div>
        </div>
    </div>

    <!-- Quick Start -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-rocket text-green-600 mr-3"></i>
            Démarrage rapide
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <div class="bg-green-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-green-600 font-bold">1</span>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Ouvrir le scanner</h3>
                <p class="text-sm text-gray-600">Appuyez sur le bouton "Scanner" dans l'interface</p>
            </div>
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <div class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-blue-600 font-bold">2</span>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Pointer et scanner</h3>
                <p class="text-sm text-gray-600">Dirigez la caméra vers le code QR ou code-barres</p>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <div class="bg-purple-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-purple-600 font-bold">3</span>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Confirmer l'action</h3>
                <p class="text-sm text-gray-600">Validez l'opération demandée (collecte, livraison...)</p>
            </div>
        </div>
    </div>

    <!-- Step-by-step Guide -->
    <div class="space-y-4">
        <h2 class="text-xl font-bold text-gray-900 flex items-center">
            <i class="fas fa-list-ol text-blue-600 mr-3"></i>
            Guide détaillé
        </h2>

        <template x-for="(step, index) in steps" :key="index">
            <div class="bg-white rounded-xl shadow-sm border">
                <button @click="toggleStep(index)"
                        class="w-full p-6 text-left flex items-center justify-between hover:bg-gray-50 rounded-xl">
                    <div class="flex items-center space-x-4">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold"
                             x-text="index + 1"></div>
                        <div>
                            <h3 class="font-semibold text-gray-900" x-text="step.title"></h3>
                            <p class="text-sm text-gray-600" x-text="step.description"></p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': openSteps.includes(index) }"></i>
                </button>

                <div x-show="openSteps.includes(index)"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="px-6 pb-6">
                    <div class="bg-gray-50 rounded-lg p-4 space-y-4">
                        <!-- Content -->
                        <div x-html="step.content"></div>

                        <!-- Demo Button -->
                        <template x-if="step.hasDemo">
                            <button @click="startDemo(step.demoType)"
                                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-play mr-2"></i>
                                Essayer maintenant
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Troubleshooting -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-tools text-red-600 mr-3"></i>
            Résolution des problèmes
        </h2>
        <div class="space-y-4">
            <template x-for="issue in troubleshooting" :key="issue.id">
                <div class="border-l-4 border-red-400 pl-4">
                    <h3 class="font-semibold text-gray-900 mb-2" x-text="issue.problem"></h3>
                    <div class="text-sm text-gray-600 space-y-2">
                        <template x-for="solution in issue.solutions" :key="solution">
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-check-circle text-green-500 mt-1"></i>
                                <span x-text="solution"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Tips and Best Practices -->
    <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl p-6 border border-yellow-200">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-lightbulb text-yellow-600 mr-3"></i>
            Conseils et bonnes pratiques
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <template x-for="tip in tips" :key="tip.id">
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <div class="flex items-start space-x-3">
                        <div class="bg-yellow-100 p-2 rounded-lg">
                            <i :class="tip.icon + ' text-yellow-600'"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2" x-text="tip.title"></h3>
                            <p class="text-sm text-gray-600" x-text="tip.description"></p>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Practice Mode -->
    <div class="bg-blue-50 rounded-xl p-6 border border-blue-200">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-graduation-cap text-blue-600 mr-3"></i>
            Mode entraînement
        </h2>
        <p class="text-gray-600 mb-4">Entraînez-vous avec des codes QR de test pour maîtriser le scanner sans affecter vos vrais colis.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <button @click="startPractice('pickup')"
                    class="bg-green-600 text-white p-4 rounded-lg hover:bg-green-700 transition-colors text-left">
                <h3 class="font-semibold mb-2">Entraînement collecte</h3>
                <p class="text-sm text-green-100">Pratiquez la collecte de colis</p>
            </button>
            <button @click="startPractice('delivery')"
                    class="bg-blue-600 text-white p-4 rounded-lg hover:bg-blue-700 transition-colors text-left">
                <h3 class="font-semibold mb-2">Entraînement livraison</h3>
                <p class="text-sm text-blue-100">Pratiquez la livraison de colis</p>
            </button>
        </div>
    </div>

    <!-- Demo Modal -->
    <div x-show="showDemo"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
         @click.away="closeDemo">
        <div class="bg-white rounded-xl p-6 max-w-md w-full">
            <div class="text-center">
                <div class="bg-green-100 p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-qrcode text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Mode démo activé</h3>
                <p class="text-gray-600 mb-6" x-text="demoMessage"></p>
                <div class="flex space-x-3">
                    <button @click="openScanner"
                            class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        Ouvrir le scanner
                    </button>
                    <button @click="closeDemo"
                            class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('qrScannerGuide', () => ({
        openSteps: [0],
        showDemo: false,
        demoMessage: '',

        steps: [
            {
                title: 'Accéder au scanner',
                description: 'Comment ouvrir l\'interface de scan',
                hasDemo: true,
                demoType: 'access',
                content: `
                    <div class="space-y-3">
                        <p><strong>Méthode 1 :</strong> Depuis l'écran principal, appuyez sur le bouton vert "Scanner"</p>
                        <p><strong>Méthode 2 :</strong> Utilisez le bouton d'action flottant (FAB) en bas à droite</p>
                        <p><strong>Méthode 3 :</strong> Dans la liste des colis, appuyez sur l'icône scanner à côté d'un colis</p>
                        <div class="bg-yellow-100 p-3 rounded-lg">
                            <p class="text-sm"><i class="fas fa-info-circle mr-2"></i>Assurez-vous d'avoir autorisé l'accès à la caméra lors de la première utilisation</p>
                        </div>
                    </div>
                `
            },
            {
                title: 'Positionner la caméra',
                description: 'Techniques pour un scan optimal',
                hasDemo: false,
                content: `
                    <div class="space-y-3">
                        <p><strong>Distance recommandée :</strong> 10-30 cm du code</p>
                        <p><strong>Éclairage :</strong> Évitez les reflets et assurez-vous d'avoir assez de lumière</p>
                        <p><strong>Stabilité :</strong> Tenez fermement le téléphone, évitez les tremblements</p>
                        <p><strong>Angle :</strong> Gardez la caméra perpendiculaire au code</p>
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <p class="text-sm"><i class="fas fa-lightbulb mr-2"></i>Utilisez le flash intégré dans les environnements sombres</p>
                        </div>
                    </div>
                `
            },
            {
                title: 'Scanner le code',
                description: 'Process de scan automatique',
                hasDemo: true,
                demoType: 'scan',
                content: `
                    <div class="space-y-3">
                        <p>Le scanner détecte automatiquement les codes QR et codes-barres dans le champ de vision.</p>
                        <p><strong>Indicateurs visuels :</strong></p>
                        <ul class="list-disc list-inside space-y-1 text-sm ml-4">
                            <li>Cadre vert : Code détecté et valide</li>
                            <li>Cadre rouge : Code non reconnu</li>
                            <li>Vibration : Confirmation de scan réussi</li>
                        </ul>
                        <p><strong>Types de codes supportés :</strong> QR Code, Code 128, EAN, UPC</p>
                    </div>
                `
            },
            {
                title: 'Saisie manuelle',
                description: 'Alternative au scan automatique',
                hasDemo: true,
                demoType: 'manual',
                content: `
                    <div class="space-y-3">
                        <p>Si le scan automatique ne fonctionne pas, utilisez la saisie manuelle :</p>
                        <ol class="list-decimal list-inside space-y-1 text-sm ml-4">
                            <li>Appuyez sur "Saisie manuelle" dans le scanner</li>
                            <li>Tapez le code du colis (généralement 10-15 caractères)</li>
                            <li>Vérifiez le code saisi</li>
                            <li>Confirmez avec le bouton de validation</li>
                        </ol>
                        <div class="bg-green-100 p-3 rounded-lg">
                            <p class="text-sm"><i class="fas fa-keyboard mr-2"></i>L'historique des codes récents est disponible pour une saisie rapide</p>
                        </div>
                    </div>
                `
            },
            {
                title: 'Actions post-scan',
                description: 'Que faire après un scan réussi',
                hasDemo: false,
                content: `
                    <div class="space-y-3">
                        <p>Après un scan réussi, plusieurs actions sont possibles :</p>
                        <ul class="list-disc list-inside space-y-1 text-sm ml-4">
                            <li><strong>Collecte :</strong> Marquer le colis comme collecté</li>
                            <li><strong>Livraison :</strong> Confirmer la livraison au destinataire</li>
                            <li><strong>Transit :</strong> Enregistrer le passage dans un hub</li>
                            <li><strong>Retour :</strong> Marquer comme non livrable</li>
                        </ul>
                        <p>Chaque action génère automatiquement un historique de traçabilité.</p>
                    </div>
                `
            }
        ],

        troubleshooting: [
            {
                id: 'camera-access',
                problem: 'Le scanner ne s\'ouvre pas',
                solutions: [
                    'Vérifiez que l\'autorisation caméra est accordée dans les paramètres',
                    'Redémarrez l\'application',
                    'Vérifiez que l\'appareil photo n\'est pas utilisé par une autre app'
                ]
            },
            {
                id: 'scan-fails',
                problem: 'Les codes ne sont pas détectés',
                solutions: [
                    'Nettoyez l\'objectif de la caméra',
                    'Améliorez l\'éclairage ou utilisez le flash',
                    'Rapprochez-vous ou éloignez-vous du code',
                    'Utilisez la saisie manuelle en alternative'
                ]
            },
            {
                id: 'slow-scan',
                problem: 'Le scan est très lent',
                solutions: [
                    'Fermez les autres applications en arrière-plan',
                    'Vérifiez la connexion internet',
                    'Redémarrez l\'appareil si nécessaire'
                ]
            }
        ],

        tips: [
            {
                id: 'lighting',
                icon: 'fas fa-sun',
                title: 'Optimisez l\'éclairage',
                description: 'Un bon éclairage améliore considérablement la vitesse et précision du scan.'
            },
            {
                id: 'steady',
                icon: 'fas fa-hand-rock',
                title: 'Tenez fermement',
                description: 'Une main stable évite les flous et accélère la détection des codes.'
            },
            {
                id: 'clean',
                icon: 'fas fa-spray-can',
                title: 'Nettoyez régulièrement',
                description: 'Un objectif propre garantit une qualité d\'image optimale pour le scan.'
            },
            {
                id: 'backup',
                icon: 'fas fa-keyboard',
                title: 'Maîtrisez la saisie manuelle',
                description: 'La saisie manuelle est un excellent plan B quand le scan automatique échoue.'
            }
        ],

        init() {
            // Auto-open first step
        },

        toggleStep(index) {
            if (this.openSteps.includes(index)) {
                this.openSteps = this.openSteps.filter(i => i !== index);
            } else {
                this.openSteps.push(index);
            }
        },

        startDemo(type) {
            const messages = {
                access: 'Le scanner va s\'ouvrir en mode démo. Vous pouvez explorer l\'interface sans affecter vos vrais colis.',
                scan: 'Vous allez voir le processus de scan en action avec des codes de test.',
                manual: 'Interface de saisie manuelle avec des exemples de codes valides.'
            };

            this.demoMessage = messages[type] || 'Mode démo activé';
            this.showDemo = true;
        },

        startPractice(type) {
            this.startDemo(type);
        },

        closeDemo() {
            this.showDemo = false;
        },

        openScanner() {
            this.closeDemo();
            // Dispatch event to open scanner component
            this.$dispatch('open-scanner', { mode: 'demo' });
        }
    }));
});
</script>
@endpush