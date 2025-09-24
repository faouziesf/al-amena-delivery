@props(['package'])

<div x-data="complaintModal({{ $package->id }}, '{{ $package->package_code }}')" class="inline-block">
    <!-- Bouton réclamation simple -->
    <button @click="openModal()"
            class="p-2 text-red-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200"
            title="Réclamation">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
    </button>

    <!-- Modal réclamation simplifié -->
    <div x-show="showModal"
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
         @click="closeModal()">

        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-lg shadow-lg w-full max-w-md">

            <!-- En-tête simplifié -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Réclamation - <span x-text="packageCode" class="text-red-600"></span>
                    </h3>
                    <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Formulaire simplifié -->
            <form @submit.prevent="submitComplaint()" class="px-6 py-4 space-y-4">
                <!-- Type de réclamation simplifié -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type de réclamation</label>
                    <select x-model="complaintData.type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <option value="">Sélectionnez un type</option>
                        <option value="CHANGE_COD">Changement du montant COD</option>
                        <option value="DELIVERY_DELAY">Retard de livraison</option>
                        <option value="REQUEST_RETURN">Demande de retour</option>
                        <option value="RETURN_DELAY">Retard de retour</option>
                        <option value="RESCHEDULE_TODAY">Report livraison</option>
                        <option value="FOURTH_ATTEMPT">4ème tentative</option>
                        <option value="CUSTOM">Autre motif</option>
                    </select>
                </div>

                <!-- Nouveau montant COD -->
                <div x-show="complaintData.type === 'CHANGE_COD'">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nouveau montant COD (DT)</label>
                    <input type="number" x-model="complaintData.new_cod_amount" step="0.01" min="0" placeholder="0.00"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <p class="text-xs text-gray-500 mt-1">Montant actuel: {{ number_format($package->cod_amount, 2) }} DT</p>
                </div>

                <!-- Date de report/4ème tentative -->
                <div x-show="complaintData.type === 'RESCHEDULE_TODAY' || complaintData.type === 'FOURTH_ATTEMPT'">
                    <label class="block text-sm font-medium text-gray-700 mb-2" x-text="complaintData.type === 'RESCHEDULE_TODAY' ? 'Date de report' : 'Date pour 4ème tentative'"></label>
                    <input type="date" x-model="complaintData.preferred_date" :min="getMinDate()" :max="getMaxDate()"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea x-model="complaintData.description" rows="3" placeholder="Décrivez votre réclamation..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none"></textarea>
                </div>

            </form>

            <!-- Actions simplifiées -->
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" @click="closeModal()"
                        class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    Annuler
                </button>
                <button @click="submitComplaint()"
                        :disabled="!isFormValid() || loading"
                        :class="isFormValid() && !loading ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                        class="px-4 py-2 rounded-md font-medium">
                    <span x-show="!loading">Envoyer</span>
                    <span x-show="loading">Envoi...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function complaintModal(packageId, packageCode) {
    return {
        packageId: packageId,
        packageCode: packageCode,
        showModal: false,
        loading: false,
        complaintData: {
            type: '',
            description: '',
            new_cod_amount: '',
            preferred_date: '',
            urgent: false
        },

        openModal() {
            this.showModal = true;
            this.resetForm();
        },

        closeModal() {
            this.showModal = false;
            this.resetForm();
        },

        resetForm() {
            this.complaintData = {
                type: '',
                description: '',
                new_cod_amount: '',
                preferred_date: '',
                urgent: false
            };
            this.loading = false;
        },

        isFormValid() {
            // Vérifier les champs obligatoires de base
            if (!this.complaintData.type || !this.complaintData.description.trim()) {
                return false;
            }

            // Vérifier le nouveau montant COD si nécessaire
            if (this.complaintData.type === 'CHANGE_COD') {
                if (!this.complaintData.new_cod_amount || this.complaintData.new_cod_amount <= 0) {
                    return false;
                }
            }

            // Vérifier la date si nécessaire
            if (this.complaintData.type === 'RESCHEDULE_TODAY' || this.complaintData.type === 'FOURTH_ATTEMPT') {
                if (!this.complaintData.preferred_date) {
                    return false;
                }
            }

            return true;
        },

        getMinDate() {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            return tomorrow.toISOString().split('T')[0];
        },

        getMaxDate() {
            const maxDate = new Date();
            maxDate.setDate(maxDate.getDate() + 7);
            return maxDate.toISOString().split('T')[0];
        },

        async submitComplaint() {
            if (!this.isFormValid()) return;

            this.loading = true;

            try {
                await this.makeComplaintRequest();
            } catch (error) {
                // Si c'est une erreur 419 (CSRF), on essaie une seule fois de plus après un refresh du token
                if (error.message.includes('419')) {
                    console.warn('Token CSRF expiré, nouvelle tentative...');
                    try {
                        // Attendre un moment et réessayer
                        await new Promise(resolve => setTimeout(resolve, 500));
                        await this.makeComplaintRequest();
                    } catch (retryError) {
                        console.error('Échec de la seconde tentative:', retryError);
                        alert('Session expirée. La page va être rechargée.');
                        setTimeout(() => window.location.reload(), 1000);
                    }
                } else {
                    console.error('Erreur complète:', error);
                    alert('Erreur lors de l\'envoi de la réclamation. Vérifiez la console pour plus de détails.');
                }
            } finally {
                this.loading = false;
            }
        },

        async makeComplaintRequest() {
            // Préparer les données à envoyer
            const formData = new FormData();
            formData.append('type', this.complaintData.type);
            formData.append('description', this.complaintData.description);
            formData.append('urgent', this.complaintData.urgent ? '1' : '0');

            if (this.complaintData.type === 'CHANGE_COD' && this.complaintData.new_cod_amount) {
                formData.append('new_cod_amount', this.complaintData.new_cod_amount);
            }

            if ((this.complaintData.type === 'RESCHEDULE_TODAY' || this.complaintData.type === 'FOURTH_ATTEMPT') && this.complaintData.preferred_date) {
                formData.append('preferred_date', this.complaintData.preferred_date);
            }

            // Debug : afficher les données envoyées
            console.log('Données de réclamation envoyées:', {
                type: this.complaintData.type,
                description: this.complaintData.description,
                urgent: this.complaintData.urgent,
                new_cod_amount: this.complaintData.new_cod_amount,
                preferred_date: this.complaintData.preferred_date,
                packageId: this.packageId
            });

            // Récupérer le token CSRF actuel
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            const response = await fetch(`/client/complaints/${this.packageId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });

            console.log('Statut de la réponse:', response.status);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Erreur HTTP:', response.status, errorText);
                throw new Error(`Erreur ${response.status}: ${errorText}`);
            }

            const data = await response.json();
            console.log('Données reçues:', data);

            if (data.success) {
                this.closeModal();
                alert('Réclamation envoyée avec succès');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                throw new Error(data.message || 'Erreur lors de l\'envoi');
            }
        }
    }
}
</script>