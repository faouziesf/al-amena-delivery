@extends('layouts.deliverer')

@section('title', 'Centre d\'aide')

@section('content')
<div x-data="helpCenter" class="max-w-4xl mx-auto p-4 space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl p-6">
        <h1 class="text-2xl font-bold mb-2">Centre d'aide</h1>
        <p class="text-blue-100">Trouvez rapidement les réponses à vos questions</p>
    </div>

    <!-- Search Bar -->
    <div class="relative">
        <input type="text"
               x-model="searchQuery"
               @input="filterSections"
               placeholder="Rechercher une question ou un sujet..."
               class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
            <i class="fas fa-search text-gray-400"></i>
        </div>
        <button x-show="searchQuery"
                @click="clearSearch"
                class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('deliverer.help.qr-scanner') }}"
           class="bg-white p-4 rounded-xl shadow-sm border hover:shadow-md transition-shadow">
            <div class="flex items-center space-x-3">
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-qrcode text-green-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Guide Scanner</h3>
                    <p class="text-sm text-gray-600">Comment utiliser le scanner QR</p>
                </div>
            </div>
        </a>

        <a href="{{ route('deliverer.help.cod-process') }}"
           class="bg-white p-4 rounded-xl shadow-sm border hover:shadow-md transition-shadow">
            <div class="flex items-center space-x-3">
                <div class="bg-yellow-100 p-3 rounded-lg">
                    <i class="fas fa-money-bill-wave text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Processus COD</h3>
                    <p class="text-sm text-gray-600">Gestion des paiements à la livraison</p>
                </div>
            </div>
        </a>

        <button @click="contactSupport"
                class="bg-white p-4 rounded-xl shadow-sm border hover:shadow-md transition-shadow text-left">
            <div class="flex items-center space-x-3">
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i class="fas fa-headset text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Support Direct</h3>
                    <p class="text-sm text-gray-600">Contacter notre équipe</p>
                </div>
            </div>
        </button>
    </div>

    <!-- FAQ Sections -->
    <div class="space-y-4">
        <template x-for="section in filteredSections" :key="section.id">
            <div class="bg-white rounded-xl shadow-sm border">
                <button @click="toggleSection(section.id)"
                        class="w-full p-4 text-left flex items-center justify-between hover:bg-gray-50 rounded-xl">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 rounded-lg" :class="section.iconBg">
                            <i :class="section.icon + ' text-lg'"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900" x-text="section.title"></h3>
                            <p class="text-sm text-gray-600" x-text="section.subtitle"></p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': openSections.includes(section.id) }"></i>
                </button>

                <div x-show="openSections.includes(section.id)"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="px-4 pb-4">
                    <div class="space-y-3">
                        <template x-for="faq in section.faqs" :key="faq.id">
                            <div class="border-l-2 border-gray-200 pl-4">
                                <button @click="toggleFaq(faq.id)"
                                        class="w-full text-left font-medium text-gray-900 hover:text-blue-600 flex items-center justify-between">
                                    <span x-text="faq.question"></span>
                                    <i class="fas fa-plus text-sm transition-transform" :class="{ 'rotate-45': openFaqs.includes(faq.id) }"></i>
                                </button>
                                <div x-show="openFaqs.includes(faq.id)"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     class="mt-2 text-gray-600 text-sm leading-relaxed"
                                     x-html="faq.answer">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- No Results -->
    <div x-show="filteredSections.length === 0 && searchQuery"
         class="text-center py-12">
        <div class="bg-gray-100 p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
            <i class="fas fa-search text-gray-400 text-xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun résultat trouvé</h3>
        <p class="text-gray-600 mb-4">Essayez d'autres mots-clés ou contactez le support</p>
        <button @click="contactSupport"
                class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
            Contacter le support
        </button>
    </div>

    <!-- Contact Section -->
    <div class="bg-gray-50 rounded-xl p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Besoin d'aide supplémentaire ?</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center space-x-3">
                <div class="bg-green-100 p-2 rounded-lg">
                    <i class="fas fa-phone text-green-600"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-900">Hotline 24/7</p>
                    <a href="tel:+213123456789" class="text-green-600 hover:underline">+213 123 456 789</a>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <div class="bg-blue-100 p-2 rounded-lg">
                    <i class="fas fa-envelope text-blue-600"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-900">Email support</p>
                    <a href="mailto:support@alamena.dz" class="text-blue-600 hover:underline">support@alamena.dz</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('helpCenter', () => ({
        searchQuery: '',
        openSections: ['general'],
        openFaqs: [],
        filteredSections: [],

        sections: [
            {
                id: 'general',
                title: 'Questions générales',
                subtitle: 'Utilisation de base de l\'application',
                icon: 'fas fa-question-circle text-blue-600',
                iconBg: 'bg-blue-100',
                faqs: [
                    {
                        id: 'login',
                        question: 'Comment me connecter à l\'application ?',
                        answer: 'Utilisez votre numéro de téléphone et votre mot de passe fournis lors de votre inscription. En cas d\'oubli, contactez votre superviseur.'
                    },
                    {
                        id: 'packages',
                        question: 'Comment voir mes colis assignés ?',
                        answer: 'Allez dans l\'onglet "Colis" pour voir tous vos colis assignés. Vous pouvez les filtrer par statut (en attente, collectés, livrés).'
                    },
                    {
                        id: 'runsheets',
                        question: 'Qu\'est-ce qu\'une feuille de route ?',
                        answer: 'Une feuille de route regroupe plusieurs colis pour une tournée organisée. Elle vous aide à optimiser vos livraisons.'
                    }
                ]
            },
            {
                id: 'scanning',
                title: 'Scanner QR/Code-barres',
                subtitle: 'Utilisation du scanner intégré',
                icon: 'fas fa-qrcode text-green-600',
                iconBg: 'bg-green-100',
                faqs: [
                    {
                        id: 'scan-package',
                        question: 'Comment scanner un colis ?',
                        answer: 'Appuyez sur le bouton Scanner, pointez la caméra vers le QR code ou code-barres. Assurez-vous d\'avoir une bonne luminosité.'
                    },
                    {
                        id: 'scan-issues',
                        question: 'Le scanner ne fonctionne pas, que faire ?',
                        answer: 'Vérifiez que vous avez autorisé l\'accès à la caméra. Nettoyez l\'objectif et assurez-vous d\'avoir assez de lumière. Vous pouvez aussi saisir manuellement le code.'
                    },
                    {
                        id: 'manual-input',
                        question: 'Comment saisir un code manuellement ?',
                        answer: 'Dans le scanner, appuyez sur "Saisie manuelle" et tapez le code du colis. Validez avec le bouton de confirmation.'
                    }
                ]
            },
            {
                id: 'delivery',
                title: 'Processus de livraison',
                subtitle: 'Étapes de collecte et livraison',
                icon: 'fas fa-truck text-orange-600',
                iconBg: 'bg-orange-100',
                faqs: [
                    {
                        id: 'pickup-process',
                        question: 'Comment collecter un colis ?',
                        answer: 'Scannez le colis, confirmez la collecte dans l\'app. Prenez une photo si nécessaire et vérifiez les informations.'
                    },
                    {
                        id: 'delivery-process',
                        question: 'Comment livrer un colis ?',
                        answer: 'Arrivé chez le destinataire, scannez le colis, confirmez la livraison et obtenez la signature du client si requise.'
                    },
                    {
                        id: 'delivery-issues',
                        question: 'Que faire si le client n\'est pas présent ?',
                        answer: 'Marquez le colis comme "Non livré - Absent". Vous pouvez programmer une nouvelle tentative ou laisser un avis de passage.'
                    }
                ]
            },
            {
                id: 'payment',
                title: 'Paiements et COD',
                subtitle: 'Gestion des encaissements',
                icon: 'fas fa-money-bill-wave text-yellow-600',
                iconBg: 'bg-yellow-100',
                faqs: [
                    {
                        id: 'cod-collection',
                        question: 'Comment encaisser un COD ?',
                        answer: 'Collectez le montant exact, confirmez dans l\'app et générez un reçu pour le client. L\'argent sera ajouté à votre solde.'
                    },
                    {
                        id: 'wallet-balance',
                        question: 'Comment voir mon solde ?',
                        answer: 'Consultez votre portefeuille dans l\'onglet "Wallet". Vous y verrez votre solde disponible, en attente et l\'historique.'
                    },
                    {
                        id: 'payment-transfer',
                        question: 'Comment transférer mon argent ?',
                        answer: 'Dans le portefeuille, utilisez l\'option "Retrait" pour transférer vers votre compte bancaire ou mobile money.'
                    }
                ]
            },
            {
                id: 'technical',
                title: 'Problèmes techniques',
                subtitle: 'Résolution des bugs et erreurs',
                icon: 'fas fa-tools text-red-600',
                iconBg: 'bg-red-100',
                faqs: [
                    {
                        id: 'app-slow',
                        question: 'L\'application est lente, que faire ?',
                        answer: 'Fermez les autres applications, vérifiez votre connexion internet. Redémarrez l\'app si nécessaire.'
                    },
                    {
                        id: 'sync-issues',
                        question: 'Mes données ne se synchronisent pas',
                        answer: 'Vérifiez votre connexion internet. Forcez la synchronisation en tirant vers le bas sur l\'écran principal.'
                    },
                    {
                        id: 'offline-mode',
                        question: 'Comment utiliser l\'app hors ligne ?',
                        answer: 'L\'app fonctionne partiellement hors ligne. Vos actions seront synchronisées dès que la connexion sera rétablie.'
                    }
                ]
            }
        ],

        init() {
            this.filteredSections = this.sections;
        },

        toggleSection(sectionId) {
            if (this.openSections.includes(sectionId)) {
                this.openSections = this.openSections.filter(id => id !== sectionId);
            } else {
                this.openSections.push(sectionId);
            }
        },

        toggleFaq(faqId) {
            if (this.openFaqs.includes(faqId)) {
                this.openFaqs = this.openFaqs.filter(id => id !== faqId);
            } else {
                this.openFaqs.push(faqId);
            }
        },

        filterSections() {
            if (!this.searchQuery.trim()) {
                this.filteredSections = this.sections;
                return;
            }

            const query = this.searchQuery.toLowerCase();
            this.filteredSections = this.sections.filter(section => {
                const titleMatch = section.title.toLowerCase().includes(query);
                const subtitleMatch = section.subtitle.toLowerCase().includes(query);
                const faqMatch = section.faqs.some(faq =>
                    faq.question.toLowerCase().includes(query) ||
                    faq.answer.toLowerCase().includes(query)
                );

                return titleMatch || subtitleMatch || faqMatch;
            }).map(section => ({
                ...section,
                faqs: section.faqs.filter(faq =>
                    faq.question.toLowerCase().includes(query) ||
                    faq.answer.toLowerCase().includes(query)
                )
            }));
        },

        clearSearch() {
            this.searchQuery = '';
            this.filteredSections = this.sections;
        },

        contactSupport() {
            const options = [
                { text: 'Appeler le support', action: () => window.location.href = 'tel:+213123456789' },
                { text: 'Envoyer un email', action: () => window.location.href = 'mailto:support@alamena.dz' },
                { text: 'Chat en ligne', action: () => this.openChat() }
            ];

            // Simple modal for contact options
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
            modal.innerHTML = `
                <div class="bg-white rounded-xl p-6 max-w-sm w-full">
                    <h3 class="text-lg font-semibold mb-4">Contacter le support</h3>
                    <div class="space-y-3">
                        ${options.map((option, index) => `
                            <button onclick="contactActions[${index}]()"
                                    class="w-full text-left p-3 rounded-lg hover:bg-gray-50 border">
                                ${option.text}
                            </button>
                        `).join('')}
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()"
                            class="w-full mt-4 p-3 bg-gray-200 rounded-lg hover:bg-gray-300">
                        Annuler
                    </button>
                </div>
            `;

            window.contactActions = options.map(opt => opt.action);
            document.body.appendChild(modal);
        },

        openChat() {
            // Integration with chat system
            console.log('Opening chat...');
            alert('Chat en cours de développement. Utilisez le téléphone pour le moment.');
        }
    }));
});
</script>
@endpush