<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Services\ActionLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class DelivererHelpController extends Controller
{
    protected $actionLogService;

    public function __construct(ActionLogService $actionLogService)
    {
        $this->actionLogService = $actionLogService;
    }

    /**
     * Centre d'aide principal
     */
    public function index()
    {
        $helpSections = [
            [
                'title' => 'Scanner QR & Codes-barres',
                'description' => 'Guide complet pour scanner efficacement',
                'icon' => 'qrcode',
                'route' => route('deliverer.help.qr-scanner'),
                'color' => 'purple'
            ],
            [
                'title' => 'Process COD & Livraisons',
                'description' => 'Procédures de collecte et livraison',
                'icon' => 'truck',
                'route' => route('deliverer.help.cod-process'),
                'color' => 'blue'
            ],
            [
                'title' => 'Gestion Wallet',
                'description' => 'Comprendre votre portefeuille livreur',
                'icon' => 'wallet',
                'route' => '#',
                'color' => 'green'
            ],
            [
                'title' => 'Urgences & Support',
                'description' => 'Contacts et procédures d\'urgence',
                'icon' => 'phone',
                'route' => '#',
                'color' => 'red'
            ]
        ];

        $faq = $this->getFAQ();
        $quickActions = $this->getQuickActions();
        $supportContacts = $this->getSupportContacts();

        return view('deliverer.help.index', compact(
            'helpSections', 
            'faq', 
            'quickActions', 
            'supportContacts'
        ));
    }

    /**
     * Guide scanner QR
     */
    public function qrScanner()
    {
        $scannerTips = [
            [
                'title' => 'Positionnement optimal',
                'description' => 'Tenez le téléphone à 15-20cm du code',
                'icon' => 'eye'
            ],
            [
                'title' => 'Éclairage',
                'description' => 'Utilisez le flash en cas de faible luminosité',
                'icon' => 'lightbulb'
            ],
            [
                'title' => 'Stabilité',
                'description' => 'Maintenez le téléphone stable pendant 2-3 secondes',
                'icon' => 'hand'
            ],
            [
                'title' => 'Saisie manuelle',
                'description' => 'Si le scan échoue, saisissez manuellement le code',
                'icon' => 'keyboard'
            ]
        ];

        $supportedFormats = [
            'QR Code' => 'Format principal pour les colis Al-Amena',
            'Code 128' => 'Codes-barres 1D standards',
            'Code 39' => 'Codes alphanumériques',
            'EAN-13' => 'Codes produits (si applicable)'
        ];

        $troubleshooting = [
            [
                'problem' => 'Le scanner ne démarre pas',
                'solution' => 'Vérifiez les permissions caméra dans les paramètres'
            ],
            [
                'problem' => 'Code non reconnu',
                'solution' => 'Nettoyez l\'objectif et essayez la saisie manuelle'
            ],
            [
                'problem' => 'Scanner lent',
                'solution' => 'Fermez les autres applications et redémarrez'
            ]
        ];

        return view('deliverer.help.qr-scanner', compact(
            'scannerTips', 
            'supportedFormats', 
            'troubleshooting'
        ));
    }

    /**
     * Guide process COD
     */
    public function codProcess()
    {
        $codSteps = [
            [
                'step' => 1,
                'title' => 'Vérification du montant',
                'description' => 'Confirmez le montant COD exact affiché dans l\'app',
                'icon' => 'calculator',
                'color' => 'blue'
            ],
            [
                'step' => 2,
                'title' => 'Collecte des espèces',
                'description' => 'Encaissez EXACTEMENT le montant indiqué',
                'icon' => 'banknotes',
                'color' => 'green'
            ],
            [
                'step' => 3,
                'title' => 'Validation dans l\'app',
                'description' => 'Confirmez la livraison et le montant collecté',
                'icon' => 'check-circle',
                'color' => 'purple'
            ],
            [
                'step' => 4,
                'title' => 'Reçu client',
                'description' => 'Remettez le reçu de livraison au client',
                'icon' => 'document',
                'color' => 'indigo'
            ]
        ];

        $codRules = [
            'Le montant COD est NON NÉGOCIABLE',
            'Aucune remise ou modification autorisée',
            'En cas de problème, contacter immédiatement le commercial',
            'Le COD doit être collecté en espèces uniquement',
            'Vérifiez l\'authenticité des billets si montant important'
        ];

        $emergencyContacts = $this->getSupportContacts();

        return view('deliverer.help.cod-process', compact(
            'codSteps', 
            'codRules', 
            'emergencyContacts'
        ));
    }

    /**
     * Guide gestion wallet
     */
    public function walletManagement()
    {
        $walletConcepts = [
            [
                'title' => 'Wallet = Caisse physique',
                'description' => 'Votre wallet représente l\'argent physique en votre possession',
                'icon' => 'wallet'
            ],
            [
                'title' => 'COD collecté = +Wallet',
                'description' => 'Chaque COD collecté s\'ajoute immédiatement à votre wallet',
                'icon' => 'plus-circle'
            ],
            [
                'title' => 'Vidange périodique',
                'description' => 'Remettez régulièrement les espèces au commercial',
                'icon' => 'arrow-down'
            ],
            [
                'title' => 'Responsabilité personnelle',
                'description' => 'Vous êtes responsable de tout manque constaté',
                'icon' => 'shield-check'
            ]
        ];

        $walletTips = [
            'Vérifiez votre solde régulièrement',
            'Signaler immédiatement toute anomalie',
            'Conservez vos reçus de vidange',
            'Ne jamais prêter ou échanger des espèces',
            'Contactez le commercial si solde > 200 DT'
        ];

        return view('deliverer.help.wallet-management', compact(
            'walletConcepts', 
            'walletTips'
        ));
    }

    /**
     * Procédures d'urgence
     */
    public function emergencyProcedures()
    {
        $emergencyTypes = [
            [
                'type' => 'Problème COD',
                'description' => 'Client refuse de payer, montant incorrect',
                'action' => 'Contacter immédiatement le commercial',
                'phone' => '+216 XX XXX XXX',
                'color' => 'red'
            ],
            [
                'type' => 'Accident/Sécurité',
                'description' => 'Accident, agression, problème de sécurité',
                'action' => 'Appeler les secours puis votre superviseur',
                'phone' => '197 / +216 XX XXX XXX',
                'color' => 'red'
            ],
            [
                'type' => 'Problème technique',
                'description' => 'App qui plante, scanner défaillant',
                'action' => 'Support technique',
                'phone' => '+216 XX XXX XXX',
                'color' => 'orange'
            ],
            [
                'type' => 'Perte/Vol',
                'description' => 'Colis perdu, vol de marchandise',
                'action' => 'Déclarer immédiatement à votre commercial',
                'phone' => '+216 XX XXX XXX',
                'color' => 'red'
            ]
        ];

        $emergencyNumbers = [
            'Police' => '197',
            'Pompiers' => '198',
            'SAMU' => '190',
            'Commercial Al-Amena' => '+216 XX XXX XXX',
            'Support technique' => '+216 XX XXX XXX'
        ];

        return view('deliverer.help.emergency-procedures', compact(
            'emergencyTypes', 
            'emergencyNumbers'
        ));
    }

    /**
     * Contacter le support
     */
    public function contactSupport(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'priority' => 'required|in:LOW,NORMAL,HIGH,URGENT',
            'category' => 'required|in:TECHNICAL,DELIVERY,COD,WALLET,APP,OTHER'
        ]);

        try {
            // Log de la demande de support
            $this->actionLogService->log(
                'SUPPORT_REQUEST_CREATED',
                'SupportTicket',
                null,
                null,
                null,
                [
                    'deliverer_id' => Auth::id(),
                    'deliverer_name' => Auth::user()->name,
                    'subject' => $validated['subject'],
                    'category' => $validated['category'],
                    'priority' => $validated['priority'],
                    'message_length' => strlen($validated['message'])
                ]
            );

            // Préparer les données pour l'email
            $supportData = [
                'deliverer' => Auth::user(),
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'priority' => $validated['priority'],
                'category' => $validated['category'],
                'submitted_at' => now(),
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip()
            ];

            // Envoyer l'email de support
            $this->sendSupportEmail($supportData);

            return response()->json([
                'success' => true,
                'message' => 'Votre demande a été envoyée au support technique. Vous recevrez une réponse dans les plus brefs délais.',
                'ticket_id' => 'SUP_' . strtoupper(uniqid())
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur envoi support', [
                'deliverer_id' => Auth::id(),
                'error' => $e->getMessage(),
                'request_data' => $validated
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi. Veuillez contacter directement le +216 XX XXX XXX'
            ], 500);
        }
    }

    /**
     * API - Rechercher dans l'aide
     */
    public function searchHelp(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 3) {
            return response()->json([
                'success' => false,
                'message' => 'Requête trop courte (minimum 3 caractères)'
            ]);
        }

        $results = $this->performHelpSearch($query);

        return response()->json([
            'success' => true,
            'results' => $results,
            'query' => $query
        ]);
    }

    /**
     * API - FAQ
     */
    public function apiFAQ()
    {
        $faq = $this->getFAQ();
        
        return response()->json([
            'success' => true,
            'faq' => $faq
        ]);
    }

    // ==================== MÉTHODES PRIVÉES ====================

    /**
     * FAQ complète
     */
    private function getFAQ()
    {
        return [
            [
                'category' => 'Scanner',
                'question' => 'Comment scanner un code QR ?',
                'answer' => 'Ouvrez le scanner depuis le bouton central, pointez la caméra vers le code à 15-20cm de distance. Le scan se fait automatiquement.'
            ],
            [
                'category' => 'Scanner',
                'question' => 'Que faire si le code ne scan pas ?',
                'answer' => 'Utilisez la saisie manuelle en tapant le code directement. Vérifiez aussi que l\'objectif de la caméra est propre.'
            ],
            [
                'category' => 'COD',
                'question' => 'Le client veut payer moins que le COD ?',
                'answer' => 'Le COD est NON NÉGOCIABLE. Contactez immédiatement votre commercial si le client refuse de payer le montant exact.'
            ],
            [
                'category' => 'COD',
                'question' => 'Comment gérer un billet suspect ?',
                'answer' => 'Vérifiez l\'authenticité selon les méthodes standards. En cas de doute, refusez poliment et proposez un autre moyen de paiement via le commercial.'
            ],
            [
                'category' => 'Livraison',
                'question' => 'Client absent, que faire ?',
                'answer' => 'Marquez "Client non disponible" dans l\'app avec la raison. Vous avez droit à 3 tentatives maximum avant retour à l\'expéditeur.'
            ],
            [
                'category' => 'Livraison',
                'question' => 'Adresse introuvable ?',
                'answer' => 'Contactez le client par téléphone. Si pas de réponse, marquez "Adresse non trouvée" et passez au colis suivant.'
            ],
            [
                'category' => 'Wallet',
                'question' => 'À quoi correspond mon wallet ?',
                'answer' => 'Votre wallet = argent physique en votre possession. Chaque COD collecté s\'y ajoute. Vous devez remettre les espèces au commercial régulièrement.'
            ],
            [
                'category' => 'Wallet',
                'question' => 'Quand vider mon wallet ?',
                'answer' => 'Dès que votre solde dépasse 200 DT ou à la demande de votre commercial. Utilisez la fonction "Demander vidange".'
            ],
            [
                'category' => 'App',
                'question' => 'L\'app plante souvent ?',
                'answer' => 'Fermez les autres applications, redémarrez votre téléphone. Si le problème persiste, contactez le support technique.'
            ],
            [
                'category' => 'App',
                'question' => 'Mode hors ligne disponible ?',
                'answer' => 'Oui, les actions principales sont sauvegardées localement et synchronisées dès que vous retrouvez la connexion.'
            ]
        ];
    }

    /**
     * Actions rapides
     */
    private function getQuickActions()
    {
        return [
            [
                'title' => 'Scanner un colis',
                'description' => 'Accès direct au scanner QR/codes-barres',
                'action' => 'openScanner()',
                'icon' => 'qrcode',
                'color' => 'purple'
            ],
            [
                'title' => 'Appeler commercial',
                'description' => 'Contact direct en cas de problème',
                'action' => 'tel:+216XXXXXXXX',
                'icon' => 'phone',
                'color' => 'green'
            ],
            [
                'title' => 'Consulter wallet',
                'description' => 'Vérifier votre solde actuel',
                'action' => route('deliverer.wallet.index'),
                'icon' => 'wallet',
                'color' => 'blue'
            ],
            [
                'title' => 'Signaler urgence',
                'description' => 'Procédure d\'urgence rapide',
                'action' => '#emergency',
                'icon' => 'exclamation-triangle',
                'color' => 'red'
            ]
        ];
    }

    /**
     * Contacts support
     */
    private function getSupportContacts()
    {
        return [
            [
                'title' => 'Commercial',
                'description' => 'Problèmes COD, livraisons',
                'phone' => '+216 XX XXX XXX',
                'hours' => '8h - 20h',
                'color' => 'blue'
            ],
            [
                'title' => 'Support technique',
                'description' => 'App, scanner, technique',
                'phone' => '+216 XX XXX XXX',
                'hours' => '9h - 18h',
                'color' => 'purple'
            ],
            [
                'title' => 'Urgences',
                'description' => 'Sécurité, accidents',
                'phone' => '197',
                'hours' => '24h/24',
                'color' => 'red'
            ]
        ];
    }

    /**
     * Recherche dans l'aide
     */
    private function performHelpSearch($query)
    {
        $faq = $this->getFAQ();
        $results = [];

        foreach ($faq as $item) {
            $searchText = strtolower($item['question'] . ' ' . $item['answer']);
            $queryLower = strtolower($query);

            if (strpos($searchText, $queryLower) !== false) {
                $results[] = [
                    'type' => 'faq',
                    'category' => $item['category'],
                    'title' => $item['question'],
                    'content' => $item['answer'],
                    'relevance' => $this->calculateRelevance($searchText, $queryLower)
                ];
            }
        }

        // Trier par pertinence
        usort($results, function($a, $b) {
            return $b['relevance'] <=> $a['relevance'];
        });

        return array_slice($results, 0, 10); // Top 10 résultats
    }

    /**
     * Calculer la pertinence d'un résultat
     */
    private function calculateRelevance($text, $query)
    {
        $words = explode(' ', $query);
        $relevance = 0;

        foreach ($words as $word) {
            if (strlen($word) > 2) {
                $relevance += substr_count($text, $word) * strlen($word);
            }
        }

        return $relevance;
    }

    /**
     * Envoyer email de support
     */
    private function sendSupportEmail($data)
    {
        // Préparer le contenu de l'email
        $emailContent = "
            Nouvelle demande de support livreur
            
            Livreur: {$data['deliverer']->name} ({$data['deliverer']->email})
            Téléphone: {$data['deliverer']->phone}
            Sujet: {$data['subject']}
            Catégorie: {$data['category']}
            Priorité: {$data['priority']}
            
            Message:
            {$data['message']}
            
            Informations techniques:
            - IP: {$data['ip_address']}
            - User Agent: {$data['user_agent']}
            - Date: {$data['submitted_at']}
        ";

        // Ici, vous pouvez utiliser votre système d'email préféré
        // Mail::to('support@alamena.com')->send(new SupportMessage($data));
        
        // Pour l'instant, log dans les fichiers
        Log::info('Support request', [
            'deliverer_id' => $data['deliverer']->id,
            'subject' => $data['subject'],
            'category' => $data['category'],
            'priority' => $data['priority']
        ]);
    }
}