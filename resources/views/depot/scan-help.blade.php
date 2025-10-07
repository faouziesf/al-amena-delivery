<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guide - Système Scan Dépôt</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    
    <div class="max-w-4xl mx-auto px-4 py-8">
        
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-sm p-8 mb-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900 mb-4">
                    🏭 Système de Scan Dépôt
                </h1>
                <p class="text-lg text-gray-600">
                    Guide d'utilisation du système PC/Téléphone
                </p>
            </div>
        </div>

        <!-- Vue d'ensemble -->
        <div class="bg-white rounded-xl shadow-sm p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">📋 Vue d'Ensemble</h2>
            
            <div class="grid md:grid-cols-2 gap-6">
                <div class="bg-blue-50 rounded-lg p-6">
                    <h3 class="text-lg font-bold text-blue-900 mb-3">💻 Interface PC</h3>
                    <ul class="space-y-2 text-blue-800">
                        <li>• QR Code de connexion</li>
                        <li>• Liste des colis scannés</li>
                        <li>• Statistiques en temps réel</li>
                        <li>• Export des données</li>
                    </ul>
                </div>
                
                <div class="bg-green-50 rounded-lg p-6">
                    <h3 class="text-lg font-bold text-green-900 mb-3">📱 Interface Téléphone</h3>
                    <ul class="space-y-2 text-green-800">
                        <li>• Scanner caméra plein écran</li>
                        <li>• Feedback instantané</li>
                        <li>• Détection automatique</li>
                        <li>• Vibrations et effets</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Instructions étape par étape -->
        <div class="bg-white rounded-xl shadow-sm p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">🚀 Instructions d'Utilisation</h2>
            
            <div class="space-y-6">
                
                <!-- Étape 1 -->
                <div class="flex items-start space-x-4">
                    <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm">1</div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Ouvrir le Tableau de Bord PC</h3>
                        <p class="text-gray-600 mb-3">
                            Accédez à <code class="bg-gray-100 px-2 py-1 rounded">/depot/scan</code> sur votre ordinateur.
                            Un QR code unique sera généré pour votre session.
                        </p>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600">
                                <strong>💡 Conseil :</strong> Gardez cette page ouverte pendant toute la session de scan.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Étape 2 -->
                <div class="flex items-start space-x-4">
                    <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm">2</div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Scanner le QR Code avec le Téléphone</h3>
                        <p class="text-gray-600 mb-3">
                            Utilisez l'appareil photo de votre téléphone pour scanner le QR code affiché sur l'écran PC.
                            Cela ouvrira automatiquement l'interface de scan.
                        </p>
                        <div class="bg-yellow-50 rounded-lg p-4">
                            <p class="text-sm text-yellow-800">
                                <strong>⚠️ Important :</strong> Autorisez l'accès à la caméra quand demandé.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Étape 3 -->
                <div class="flex items-start space-x-4">
                    <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm">3</div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Commencer le Scan des Colis</h3>
                        <p class="text-gray-600 mb-3">
                            L'interface caméra s'ouvre en plein écran. Positionnez les codes-barres des colis
                            dans le cadre de scan pour les traiter automatiquement.
                        </p>
                        <div class="bg-green-50 rounded-lg p-4">
                            <p class="text-sm text-green-800">
                                <strong>✅ Succès :</strong> Flash vert + vibration + affichage du numéro
                            </p>
                            <p class="text-sm text-red-800 mt-1">
                                <strong>❌ Erreur :</strong> Flash rouge + vibration + message d'erreur
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Étape 4 -->
                <div class="flex items-start space-x-4">
                    <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm">4</div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Suivre les Résultats sur PC</h3>
                        <p class="text-gray-600 mb-3">
                            Chaque colis scanné apparaît instantanément dans la liste sur l'écran PC,
                            avec l'heure de scan et les statistiques mises à jour.
                        </p>
                        <div class="bg-blue-50 rounded-lg p-4">
                            <p class="text-sm text-blue-800">
                                <strong>📊 Statistiques :</strong> Total scannés, taux par minute, durée session
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Fonctionnalités avancées -->
        <div class="bg-white rounded-xl shadow-sm p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">⚡ Fonctionnalités Avancées</h2>
            
            <div class="grid md:grid-cols-2 gap-6">
                
                <div class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-900">📱 Interface Téléphone</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li>• <strong>Caméra optimisée :</strong> Utilise la caméra arrière automatiquement</li>
                        <li>• <strong>Détection rapide :</strong> Même technologie que les livreurs</li>
                        <li>• <strong>Feedback haptique :</strong> Vibrations pour succès/erreur</li>
                        <li>• <strong>Écran toujours allumé :</strong> Évite la mise en veille</li>
                        <li>• <strong>Cooldown intelligent :</strong> Évite les scans multiples</li>
                    </ul>
                </div>
                
                <div class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-900">💻 Interface PC</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li>• <strong>Temps réel :</strong> Mise à jour automatique sans refresh</li>
                        <li>• <strong>Export CSV :</strong> Téléchargement des données</li>
                        <li>• <strong>Statistiques live :</strong> Taux de scan, durée, totaux</li>
                        <li>• <strong>Indicateur connexion :</strong> Statut téléphone en temps réel</li>
                        <li>• <strong>Session persistante :</strong> Données sauvegardées 8h</li>
                    </ul>
                </div>
                
            </div>
        </div>

        <!-- Messages d'erreur -->
        <div class="bg-white rounded-xl shadow-sm p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">🚨 Messages d'Erreur Courants</h2>
            
            <div class="space-y-4">
                <div class="border-l-4 border-red-500 bg-red-50 p-4">
                    <h4 class="font-bold text-red-900">❌ "Colis Introuvable"</h4>
                    <p class="text-red-800">Le code scanné n'existe pas dans la base de données.</p>
                </div>
                
                <div class="border-l-4 border-yellow-500 bg-yellow-50 p-4">
                    <h4 class="font-bold text-yellow-900">⚠️ "Déjà Scanné"</h4>
                    <p class="text-yellow-800">Ce colis a déjà été traité dans cette session.</p>
                </div>
                
                <div class="border-l-4 border-orange-500 bg-orange-50 p-4">
                    <h4 class="font-bold text-orange-900">🔒 "Statut Invalide"</h4>
                    <p class="text-orange-800">Le colis n'est pas dans un état permettant le scan dépôt.</p>
                </div>
                
                <div class="border-l-4 border-gray-500 bg-gray-50 p-4">
                    <h4 class="font-bold text-gray-900">📡 "Erreur Connexion"</h4>
                    <p class="text-gray-800">Problème de réseau entre le téléphone et le serveur.</p>
                </div>
            </div>
        </div>

        <!-- Support -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl shadow-sm p-8 text-white text-center">
            <h2 class="text-2xl font-bold mb-4">🆘 Besoin d'Aide ?</h2>
            <p class="text-blue-100 mb-6">
                Si vous rencontrez des problèmes avec le système de scan dépôt,
                contactez l'équipe technique ou consultez la documentation complète.
            </p>
            <div class="flex justify-center space-x-4">
                <a href="/depot/scan" 
                   class="bg-white text-blue-600 px-6 py-2 rounded-lg font-medium hover:bg-blue-50 transition-colors">
                    🚀 Commencer le Scan
                </a>
                <button onclick="window.print()" 
                        class="bg-blue-500 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-400 transition-colors">
                    🖨️ Imprimer ce Guide
                </button>
            </div>
        </div>

    </div>

</body>
</html>
