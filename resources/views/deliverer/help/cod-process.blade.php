@extends('layouts.deliverer')

@section('title', 'Guide Processus COD')

@section('content')
<div x-data="codProcessGuide" class="max-w-4xl mx-auto p-4 sm:p-6 space-y-8">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
        <div class="flex items-center space-x-4">
            <a href="{{ route('deliverer.help.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Guide Processus COD</h1>
                <p class="text-gray-600 dark:text-gray-400">Maîtrisez la gestion des paiements à la livraison.</p>
            </div>
        </div>
    </div>

    <!-- COD Overview -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
            <i class="fas fa-info-circle text-blue-500 mr-3"></i>
            Qu'est-ce que le COD ?
        </h2>
        <div class="bg-blue-50 dark:bg-gray-700/50 rounded-lg p-4 mb-6">
            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                <strong>COD (Cash On Delivery)</strong> ou "Paiement à la livraison" est un mode de paiement où le client paie
                le montant du colis au moment de la réception. En tant que livreur, vous collectez l'argent et
                l'ajoutez à votre portefeuille électronique.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="bg-green-100 dark:bg-green-900/50 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-money-bill-wave text-green-600 dark:text-green-400"></i>
                </div>
                <h3 class="font-semibold text-gray-800 dark:text-gray-200">Collecte</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Encaissement du montant exact</p>
            </div>
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="bg-blue-100 dark:bg-blue-900/50 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-mobile-alt text-blue-600 dark:text-blue-400"></i>
                </div>
                <h3 class="font-semibold text-gray-800 dark:text-gray-200">Confirmation</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Validation dans l'application</p>
            </div>
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="bg-purple-100 dark:bg-purple-900/50 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-receipt text-purple-600 dark:text-purple-400"></i>
                </div>
                <h3 class="font-semibold text-gray-800 dark:text-gray-200">Reçu</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Génération automatique du reçu</p>
            </div>
        </div>
    </div>

    <!-- Step-by-step Process -->
    <div class="space-y-4">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 flex items-center">
            <i class="fas fa-list-ol text-orange-500 mr-3"></i>
            Processus étape par étape
        </h2>

        <template x-for="(step, index) in codSteps" :key="index">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
                <button @click="toggleStep(index)"
                        class="w-full p-5 text-left flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-xl transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="bg-orange-500 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg"
                             x-text="index + 1"></div>
                        <div>
                            <h3 class="font-semibold text-gray-800 dark:text-gray-200" x-text="step.title"></h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400" x-text="step.description"></p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="{ 'rotate-180': openSteps.includes(index) }"></i>
                </button>

                <div x-show="openSteps.includes(index)"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="px-5 pb-5">
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border-l-4 border-orange-400 dark:border-orange-500">
                        <div x-html="step.content"></div>

                        <!-- Action Buttons -->
                        <template x-if="step.hasAction">
                            <div class="mt-4 flex space-x-3">
                                <button @click="simulateStep(step.actionType)" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors text-sm font-semibold">
                                    <i class="fas fa-play mr-2"></i>
                                    Simuler cette étape
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Payment Methods -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-credit-card text-green-600 mr-3"></i>
            Moyens de paiement acceptés
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <template x-for="method in paymentMethods" :key="method.id">
                <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center space-x-3 mb-3">
                        <div :class="method.iconBg + ' p-2 rounded-lg'">
                            <i :class="method.icon"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900" x-text="method.title"></h3>
                    </div>
                    <p class="text-sm text-gray-600 mb-3" x-text="method.description"></p>
                    <div class="space-y-2">
                        <template x-for="tip in method.tips" :key="tip">
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-check-circle text-green-500 mt-1 text-xs"></i>
                                <span class="text-xs text-gray-600" x-text="tip"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Important Rules -->
    <div class="bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 p-6">
        <h2 class="text-xl font-semibold text-red-800 dark:text-red-300 mb-4 flex items-center">
            <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
            Règles importantes
        </h2>
        <div class="space-y-4">
            <template x-for="rule in importantRules" :key="rule.id">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-l-4 border-red-500 shadow-sm">
                    <div class="flex items-start space-x-4">
                        <i :class="rule.icon + ' text-red-500 mt-1 text-lg'"></i>
                        <div>
                            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-1" x-text="rule.title"></h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400" x-text="rule.description"></p>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- COD Calculator -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
            <i class="fas fa-calculator text-blue-500 mr-3"></i>
            Calculateur COD
        </h2>
        <p class="text-gray-500 dark:text-gray-400 mb-4">Vérifiez rapidement les montants à collecter.</p>

        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Montant du colis (DT)</label>
                    <input type="number"
                           x-model="calculator.packageAmount"
                           @input="calculateCOD"
                           placeholder="Ex: 5000"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Frais de livraison (DT)</label>
                    <input type="number"
                           x-model="calculator.deliveryFee"
                           @input="calculateCOD"
                           placeholder="Ex: 300"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-t-4 border-green-500 shadow-md">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-lg font-semibold text-gray-800 dark:text-gray-200">Montant total à collecter :</span>
                    <span class="text-2xl font-bold text-green-600 dark:text-green-400" x-text="calculator.total.toFixed(3) + ' DT'"></span>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <div class="flex justify-between">
                        <span>Colis : <span x-text="parseFloat(calculator.packageAmount || 0).toFixed(3)"></span> DT</span>
                        <span>Livraison : <span x-text="parseFloat(calculator.deliveryFee || 0).toFixed(3)"></span> DT</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
            <i class="fas fa-question-circle text-purple-500 mr-3"></i>
            Questions fréquentes
        </h2>
        <div class="space-y-3">
            <template x-for="faq in codFaqs" :key="faq.id">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <button @click="toggleFaq(faq.id)" class="w-full py-4 text-left flex items-center justify-between">
                        <span class="font-medium text-gray-800 dark:text-gray-200" x-text="faq.question"></span>
                        <i class="fas fa-plus text-gray-500 text-sm transition-transform" :class="{ 'rotate-45': openFaqs.includes(faq.id) }"></i>
                    </button>
                    <div x-show="openFaqs.includes(faq.id)"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         class="pb-4">
                        <p class="text-gray-600 dark:text-gray-400 text-sm" x-text="faq.answer"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Emergency Contact -->
    <div class="bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
            <i class="fas fa-headset text-orange-500 mr-3"></i>
            En cas de problème
        </h2>
        <p class="text-gray-500 dark:text-gray-400 mb-4">Si vous rencontrez des difficultés avec un paiement COD, n'hésitez pas à nous contacter.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="tel:+213123456789"
               class="flex items-center space-x-4 bg-white dark:bg-gray-700 p-4 rounded-lg border dark:border-gray-600 hover:shadow-lg hover:border-orange-500 dark:hover:border-orange-500 transition-all">
                <div class="bg-red-100 dark:bg-red-900/50 p-3 rounded-full">
                    <i class="fas fa-phone text-red-600 dark:text-red-400 text-lg"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800 dark:text-gray-200">Urgence COD</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">+216 XX XXX XXX</p>
                </div>
            </a>
            <button @click="reportIssue"
                    class="flex items-center space-x-4 bg-white dark:bg-gray-700 p-4 rounded-lg border dark:border-gray-600 hover:shadow-lg hover:border-orange-500 dark:hover:border-orange-500 transition-all">
                <div class="bg-orange-100 dark:bg-orange-900/50 p-3 rounded-full">
                    <i class="fas fa-exclamation-triangle text-orange-600 dark:text-orange-400 text-lg"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800 dark:text-gray-200">Signaler un problème</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Via l'application</p>
                </div>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('codProcessGuide', () => ({
        openSteps: [0],
        openFaqs: [],
        calculator: {
            packageAmount: '',
            deliveryFee: '',
            total: 0.000
        },

        codSteps: [
            {
                title: 'Vérification du colis COD',
                description: 'Identifier un colis COD avant la livraison',
                hasAction: true,
                actionType: 'verify',
                content: `
                    <div class="space-y-3">
                        <p class="text-gray-800 dark:text-gray-200"><strong>Avant de partir en livraison :</strong></p>
                        <ul class="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-4">
                            <li>Vérifiez la mention "COD" sur l'étiquette du colis</li>
                            <li>Notez le montant exact à collecter</li>
                            <li>Assurez-vous d'avoir suffisamment de monnaie pour le rendu</li>
                            <li>Vérifiez votre équipement (TPE mobile si nécessaire)</li>
                        </ul>
                        <div class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 p-3 rounded-lg">
                            <p class="text-sm"><i class="fas fa-info-circle mr-2"></i>Le montant COD est toujours visible dans l'app avant la livraison.</p>
                        </div>
                    </div>
                `
            },
            {
                title: 'Arrivée chez le client',
                description: 'Présentation et explication du processus',
                hasAction: true,
                actionType: 'arrival',
                content: `
                    <div class="space-y-3">
                        <p class="text-gray-800 dark:text-gray-200"><strong>À l'arrivée :</strong></p>
                        <ol class="list-decimal list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-4">
                            <li>Présentez-vous et montrez votre badge</li>
                            <li>Annoncez la livraison du colis</li>
                            <li>Informez du montant COD à payer</li>
                            <li>Expliquez les moyens de paiement acceptés</li>
                        </ol>
                        <div class="bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 p-3 rounded-lg">
                            <p class="text-sm"><i class="fas fa-exclamation-triangle mr-2"></i>Toujours annoncer le montant AVANT de remettre le colis.</p>
                        </div>
                    </div>
                `
            },
            {
                title: 'Collecte du paiement',
                description: 'Encaissement sécurisé du montant',
                hasAction: true,
                actionType: 'collect',
                content: `
                    <div class="space-y-3">
                        <p class="text-gray-800 dark:text-gray-200"><strong>Processus de collecte :</strong></p>
                        <ol class="list-decimal list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-4">
                            <li>Vérifiez les billets reçus (authenticité)</li>
                            <li>Comptez le montant exact</li>
                            <li>Calculez et rendez la monnaie si nécessaire</li>
                            <li>Confirmez le montant collecté avec le client</li>
                        </ol>
                        <div class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 p-3 rounded-lg">
                            <p class="text-sm"><i class="fas fa-shield-alt mr-2"></i>En cas de doute sur l'authenticité des billets, contactez le support.</p>
                        </div>
                    </div>
                `
            },
            {
                title: 'Confirmation dans l\'app',
                description: 'Validation électronique du paiement',
                hasAction: true,
                actionType: 'confirm',
                content: `
                    <div class="space-y-3">
                        <p class="text-gray-800 dark:text-gray-200"><strong>Dans l'application :</strong></p>
                        <ol class="list-decimal list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-4">
                            <li>Scannez le colis pour confirmer la livraison</li>
                            <li>Sélectionnez "Paiement COD reçu"</li>
                            <li>Saisissez le montant collecté</li>
                            <li>Confirmez la transaction</li>
                        </ol>
                        <p class="text-gray-800 dark:text-gray-200 mt-3"><strong>Le système va automatiquement :</strong></p>
                        <ul class="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-4">
                            <li>Ajouter le montant à votre portefeuille</li>
                            <li>Générer un reçu pour le client</li>
                            <li>Mettre à jour le statut du colis</li>
                        </ul>
                    </div>
                `
            },
            {
                title: 'Remise du reçu',
                description: 'Finalisation avec le client',
                hasAction: true,
                actionType: 'receipt',
                content: `
                    <div class="space-y-3">
                        <p class="text-gray-800 dark:text-gray-200"><strong>Finalisation :</strong></p>
                        <ol class="list-decimal list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400 ml-4">
                            <li>Remettez le colis au client</li>
                            <li>Imprimez ou envoyez le reçu par SMS/email</li>
                            <li>Demandez une signature si requise</li>
                            <li>Prenez une photo de preuve si nécessaire</li>
                        </ol>
                        <div class="bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200 p-3 rounded-lg">
                            <p class="text-sm"><i class="fas fa-receipt mr-2"></i>Le reçu contient tous les détails de la transaction pour la traçabilité.</p>
                        </div>
                    </div>
                `
            }
        ],

        paymentMethods: [
            {
                id: 'cash',
                title: 'Espèces (DT)',
                description: 'Paiement en dinars algériens, méthode la plus courante',
                icon: 'fas fa-money-bill-wave text-green-600',
                iconBg: 'bg-green-100',
                tips: [
                    'Accepter uniquement les billets en bon état',
                    'Vérifier l\'authenticité des gros billets',
                    'Toujours avoir de la monnaie pour le rendu'
                ]
            },
            {
                id: 'card',
                title: 'Carte bancaire',
                description: 'Paiement par TPE mobile (si disponible)',
                icon: 'fas fa-credit-card text-blue-600',
                iconBg: 'bg-blue-100',
                tips: [
                    'Vérifier la charge du TPE avant la tournée',
                    'Demander le code PIN au client',
                    'Imprimer le ticket de transaction'
                ]
            },
            {
                id: 'mobile',
                title: 'Paiement mobile',
                description: 'CIB, Baridi Mob, autres wallets mobiles',
                icon: 'fas fa-mobile-alt text-purple-600',
                iconBg: 'bg-purple-100',
                tips: [
                    'Scanner le QR code de votre wallet',
                    'Vérifier la réception du paiement',
                    'Conserver la preuve de transaction'
                ]
            },
            {
                id: 'check',
                title: 'Chèque (rare)',
                description: 'Accepté uniquement sur autorisation spéciale',
                icon: 'fas fa-file-invoice text-orange-600',
                iconBg: 'bg-orange-100',
                tips: [
                    'Vérifier l\'identité du signataire',
                    'Noter les références du chèque',
                    'Contacter le superviseur pour validation'
                ]
            }
        ],

        importantRules: [
            {
                id: 'exact-amount',
                title: 'Montant exact obligatoire',
                description: 'Vous devez collecter exactement le montant indiqué dans l\'application. Aucune remise ou négociation n\'est autorisée.',
                icon: 'fas fa-equals'
            },
            {
                id: 'before-delivery',
                title: 'Paiement avant remise',
                description: 'Le paiement doit TOUJOURS être effectué AVANT la remise du colis au client. Aucune exception.',
                icon: 'fas fa-hand-holding-usd'
            },
            {
                id: 'receipt-mandatory',
                title: 'Reçu obligatoire',
                description: 'Un reçu doit être généré et remis au client pour chaque transaction COD, même de faible montant.',
                icon: 'fas fa-receipt'
            },
            {
                id: 'no-personal-money',
                title: 'Pas d\'argent personnel',
                description: 'N\'utilisez jamais votre argent personnel pour compléter un paiement COD ou rendre la monnaie.',
                icon: 'fas fa-ban'
            }
        ],

        codFaqs: [
            {
                id: 'partial-payment',
                question: 'Que faire si le client n\'a pas le montant complet ?',
                answer: 'Ne livrez pas le colis. Proposez de reprogrammer la livraison quand il aura le montant complet. Contactez le client/expéditeur pour négocier si nécessaire.'
            },
            {
                id: 'fake-money',
                question: 'Comment reconnaître les faux billets ?',
                answer: 'Vérifiez le papier, les reliefs, les couleurs changeantes et les hologrammes. En cas de doute, refusez poliment et proposez un autre moyen de paiement.'
            },
            {
                id: 'no-change',
                question: 'Je n\'ai pas de monnaie à rendre, que faire ?',
                answer: 'Demandez au client s\'il a l\'appoint. Sinon, cherchez de la monnaie chez un commerçant proche. En dernier recours, reportez la livraison.'
            },
            {
                id: 'dispute',
                question: 'Le client conteste le montant COD',
                answer: 'Montrez-lui le montant dans l\'application. Si il persiste, contactez le service client en direct. Ne négociez jamais le prix vous-même.'
            },
            {
                id: 'wallet-delay',
                question: 'Quand l\'argent apparaît-il dans mon portefeuille ?',
                answer: 'Immédiatement après confirmation dans l\'app. Si ce n\'est pas le cas, vérifiez votre connexion et re-synchronisez les données.'
            }
        ],

        init() {
            this.calculateCOD();
        },

        toggleStep(index) {
            if (this.openSteps.includes(index)) {
                this.openSteps = this.openSteps.filter(i => i !== index);
            } else {
                this.openSteps.push(index);
            }
        },

        toggleFaq(id) {
            if (this.openFaqs.includes(id)) {
                this.openFaqs = this.openFaqs.filter(i => i !== id);
            } else {
                this.openFaqs.push(id);
            }
        },

        calculateCOD() {
            const packageAmount = parseFloat(this.calculator.packageAmount) || 0;
            const deliveryFee = parseFloat(this.calculator.deliveryFee) || 0;
            this.calculator.total = (packageAmount + deliveryFee);
        },

        simulateStep(type) {
            const messages = {
                verify: 'Simulation : Vérification d\'un colis COD de 3500 DA + 300 DA de livraison = 3800 DA total',
                arrival: 'Simulation : "Bonjour, je suis Ahmed de Al-Amena Delivery. J\'ai un colis pour vous avec un montant COD de 3800 DA"',
                collect: 'Simulation : Le client donne 4000 DA, vous rendez 200 DA de monnaie',
                confirm: 'Simulation : Scan du colis → Sélection "COD reçu" → Saisie 3800 DA → Confirmation',
                receipt: 'Simulation : Génération du reçu et remise du colis au client'
            };

            alert(messages[type] || 'Simulation en cours...');
        },

        reportIssue() {
            // Open issue reporting modal or navigate to support
            const issues = [
                'Client refuse de payer',
                'Montant COD incorrect dans l\'app',
                'Problème avec le TPE',
                'Faux billets suspectés',
                'Autre problème'
            ];

            const issue = prompt(`Sélectionnez le type de problème :\n${issues.map((issue, index) => `${index + 1}. ${issue}`).join('\n')}\n\nTapez le numéro correspondant :`);

            if (issue && issue >= 1 && issue <= issues.length) {
                alert(`Problème "${issues[issue - 1]}" signalé. Notre équipe va vous contacter sous peu.`);
            }
        }
    }));
});
</script>
@endpush