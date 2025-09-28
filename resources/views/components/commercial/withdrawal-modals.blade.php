<!-- Assign to Deliverer Modal -->
<div id="assign-deliverer-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-bold text-gray-900">Assigner à un Livreur</h3>
                <button onclick="closeAssignDelivererModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="assign-deliverer-form" class="p-6 space-y-4">
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-sm text-green-700">
                        <div>Demande: <span id="assign-request-code" class="font-medium"></span></div>
                        <div>Montant: <span id="assign-amount" class="font-bold"></span> DT</div>
                        <div>Code livraison: <span id="assign-delivery-code" class="font-mono text-xs"></span></div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Livreur</label>
                    <select id="assign-deliverer-id" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Sélectionner un livreur...</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Le livreur recevra les espèces de votre part</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optionnel)</label>
                    <textarea id="assign-notes" rows="3"
                              placeholder="Instructions spéciales pour la livraison..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <div class="flex items-start space-x-2">
                        <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div class="text-sm text-yellow-700">
                            <p class="font-medium">Important:</p>
                            <p>Vous devez remettre les espèces au livreur avant qu'il ne parte en livraison.</p>
                        </div>
                    </div>
                </div>

                <div class="flex space-x-3 pt-4">
                    <button type="submit" class="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors">
                        Assigner
                    </button>
                    <button type="button" onclick="closeAssignDelivererModal()"
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 transition-colors">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mark as Delivered Modal -->
<div id="mark-delivered-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-bold text-gray-900">Confirmer Livraison</h3>
                <button onclick="closeMarkDeliveredModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="mark-delivered-form" class="p-6 space-y-4">
                <div class="bg-emerald-50 p-4 rounded-lg">
                    <div class="text-sm text-emerald-700">
                        <div>Demande: <span id="delivered-request-code" class="font-medium"></span></div>
                        <div>Montant livré: <span id="delivered-amount" class="font-bold"></span> DT</div>
                        <div>Livreur: <span id="delivered-by" class="font-medium"></span></div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes de livraison</label>
                    <textarea id="delivery-notes" rows="3"
                              placeholder="Détails sur la livraison, signature du client..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                </div>

                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="confirm-delivery" required
                           class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                    <label for="confirm-delivery" class="text-sm text-gray-700">
                        Je confirme que les espèces ont été livrées au client
                    </label>
                </div>

                <div class="flex space-x-3 pt-4">
                    <button type="submit" class="flex-1 bg-emerald-600 text-white py-2 px-4 rounded-lg hover:bg-emerald-700 transition-colors">
                        Confirmer Livraison
                    </button>
                    <button type="button" onclick="closeMarkDeliveredModal()"
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 transition-colors">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Approve Withdrawal Modal -->
<div id="approve-withdrawal-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-bold text-gray-900">Approuver la Demande</h3>
                <button onclick="closeApproveWithdrawalModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="approve-withdrawal-form" class="p-6 space-y-4">
                @if(isset($withdrawal))
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-sm text-green-700">
                        <div>Demande: <span class="font-medium">{{ $withdrawal->request_code }}</span></div>
                        <div>Montant: <span class="font-bold">{{ number_format($withdrawal->amount, 3) }} DT</span></div>
                        <div>Client: <span class="font-medium">{{ $withdrawal->client->name }}</span></div>
                        <div>Méthode: <span class="font-medium">{{ $withdrawal->method_display }}</span></div>
                    </div>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes d'approbation (optionnel)</label>
                    <textarea id="approve-notes" rows="3"
                              placeholder="Commentaires sur l'approbation..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <div class="flex items-start space-x-2">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm text-blue-700">
                            <p class="font-medium">Information:</p>
                            @if(isset($withdrawal) && $withdrawal->method === 'CASH_DELIVERY')
                            <p>Cette demande sera prête pour assignation à un livreur après approbation.</p>
                            @else
                            <p>Cette demande sera traitée comme un virement bancaire.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex space-x-3 pt-4">
                    <button type="submit" class="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors">
                        Approuver
                    </button>
                    <button type="button" onclick="closeApproveWithdrawalModal()"
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 transition-colors">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Withdrawal Modal -->
<div id="reject-withdrawal-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-bold text-gray-900">Rejeter la Demande</h3>
                <button onclick="closeRejectWithdrawalModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="reject-withdrawal-form" class="p-6 space-y-4">
                @if(isset($withdrawal))
                <div class="bg-red-50 p-4 rounded-lg">
                    <div class="text-sm text-red-700">
                        <div>Demande: <span class="font-medium">{{ $withdrawal->request_code }}</span></div>
                        <div>Montant: <span class="font-bold">{{ number_format($withdrawal->amount, 3) }} DT</span></div>
                        <div>Client: <span class="font-medium">{{ $withdrawal->client->name }}</span></div>
                    </div>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motif du rejet <span class="text-red-500">*</span></label>
                    <textarea id="reject-reason" rows="4" required
                              placeholder="Précisez les raisons du rejet de cette demande..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                </div>

                <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                    <div class="flex items-start space-x-2">
                        <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div class="text-sm text-red-700">
                            <p class="font-medium">Attention:</p>
                            <p>Le client sera notifié du rejet de sa demande avec le motif fourni.</p>
                        </div>
                    </div>
                </div>

                <div class="flex space-x-3 pt-4">
                    <button type="submit" class="flex-1 bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition-colors">
                        Rejeter
                    </button>
                    <button type="button" onclick="closeRejectWithdrawalModal()"
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 transition-colors">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>