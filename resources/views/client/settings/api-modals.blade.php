<!-- Modal: Générer Token -->
<div id="generateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold mb-4">✨ Générer un Token API</h3>
        <p class="text-gray-600 mb-4">
            Un nouveau token API sera créé pour votre compte. Vous pourrez l'utiliser immédiatement pour faire des appels API.
        </p>
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-4">
            <p class="text-yellow-800 font-medium">
                ⚠️ Copiez et conservez ce token en lieu sûr. Il ne sera affiché qu'une seule fois.
            </p>
        </div>
        <div class="flex gap-3">
            <button onclick="closeModal('generateModal')" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                Annuler
            </button>
            <button onclick="confirmGenerate()" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                Confirmer
            </button>
        </div>
    </div>
</div>

<!-- Modal: Régénérer Token -->
<div id="regenerateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold mb-4">🔄 Régénérer le Token API</h3>
        <p class="text-gray-600 mb-4">
            Cette action va créer un nouveau token et invalider l'ancien.
        </p>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
            <p class="text-red-800 font-bold mb-2">
                ⚠️ ATTENTION - Action Irréversible
            </p>
            <ul class="text-red-700 text-sm list-disc list-inside space-y-1">
                <li>Votre ancien token cessera de fonctionner immédiatement</li>
                <li>Toutes vos intégrations existantes seront impactées</li>
                <li>Vous devrez mettre à jour votre token dans tous vos systèmes</li>
            </ul>
        </div>
        <label class="flex items-center mb-4 cursor-pointer">
            <input type="checkbox" id="confirmRegenerate" class="mr-2">
            <span class="text-sm">Je comprends que l'ancien token ne fonctionnera plus</span>
        </label>
        <div class="flex gap-3">
            <button onclick="closeModal('regenerateModal')" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                Annuler
            </button>
            <button onclick="confirmRegenerate()" 
                    id="confirmRegenerateBtn"
                    disabled
                    class="flex-1 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition disabled:bg-gray-400 disabled:cursor-not-allowed">
                Régénérer
            </button>
        </div>
    </div>
</div>

<!-- Modal: Supprimer Token -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold mb-4">🗑️ Supprimer le Token API</h3>
        <p class="text-gray-600 mb-4">
            Êtes-vous sûr de vouloir supprimer votre token API ?
        </p>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
            <p class="text-red-800 font-medium">
                Cette action est irréversible. Toutes vos intégrations cesseront de fonctionner.
            </p>
        </div>
        <div class="flex gap-3">
            <button onclick="closeModal('deleteModal')" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                Annuler
            </button>
            <button onclick="confirmDelete()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                Supprimer
            </button>
        </div>
    </div>
</div>

<!-- Modal: Succès -->
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <div class="text-center mb-4">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-3xl">✅</span>
            </div>
            <h3 class="text-xl font-bold">Token Créé avec Succès !</h3>
        </div>
        
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-4">
            <p class="text-yellow-800 font-bold mb-2">
                ⚠️ Copiez ce token maintenant !
            </p>
            <p class="text-yellow-700 text-sm">
                Pour des raisons de sécurité, ce token ne sera plus jamais affiché en clair.
            </p>
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Votre Token :</label>
            <div class="relative">
                <input type="text" 
                       id="newToken" 
                       readonly
                       class="w-full px-4 py-3 bg-white border-2 border-green-500 rounded-lg font-mono text-sm select-all pr-12">
                <button onclick="copyNewToken()" 
                        class="absolute right-3 top-3 text-green-600 hover:text-green-800 transition">
                    📋
                </button>
            </div>
        </div>
        
        <button onclick="closeSuccessModal()" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
            J'ai Copié le Token
        </button>
    </div>
</div>
