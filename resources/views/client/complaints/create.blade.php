@extends('layouts.client')

@section('title', "Nouvelle réclamation - Colis {$package->package_code}")

@section('content')
<div class="max-w-7xl mx-auto sm:py-5 lg:py-6 max-w-2xl">
    <div class="bg-white rounded-2xl shadow-md hover:shadow-xl border p-4 sm:p-5 lg:p-6 transition-all duration-300 hover:-translate-y-1">
        <!-- En-tête -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900 mb-2">Nouvelle réclamation</h1>
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
                <div class="flex items-center gap-3 flex-col sm:flex-row">
                    <div class="w-10 h-10 bg-blue-500 rounded-2xl flex items-center justify-center text-white font-bold flex-col sm:flex-row">
                        📦
                    </div>
                    <div>
                        <p class="font-semibold text-blue-900">Colis {{ $package->package_code }}</p>
                        <p class="text-blue-700 text-sm">{{ $package->recipient_name }} - {{ $package->recipient_city }}</p>
                        <p class="text-blue-600 text-sm">COD: {{ number_format($package->cod_amount, 2) }} DT</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaire -->
        <form action="{{ route('client.complaints.store', $package) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="space-y-6">
                <!-- Type de réclamation -->
                <div>
                    <label for="type" class="block text-sm font-medium text-slate-700 mb-2">
                        Type de réclamation *
                    </label>
                    <select name="type" id="type" required
                        class="w-full px-3 py-2 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-transparent min-h-[44px]">
                        <option value="">Sélectionnez le type de réclamation</option>
                        <option value="CHANGE_COD">Changement du montant COD</option>
                        <option value="DELIVERY_DELAY">Retard de livraison</option>
                        <option value="REQUEST_RETURN">Demande de retour</option>
                        <option value="RETURN_DELAY">Retard de retour</option>
                        <option value="RESCHEDULE_TODAY">Reporter la livraison</option>
                        <option value="FOURTH_ATTEMPT">Demander une 4ème tentative</option>
                        <option value="CUSTOM">Autre problème</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nouveau montant COD (conditionnel) -->
                <div id="codAmountField" style="display: none;">
                    <label for="new_cod_amount" class="block text-sm font-medium text-slate-700 mb-2">
                        Nouveau montant COD (DT) *
                    </label>
                    <input type="number" name="new_cod_amount" id="new_cod_amount" step="0.01" min="0"
                        class="w-full px-3 py-2 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-transparent min-h-[44px]"
                        placeholder="0.00">
                    <p class="text-sm sm:text-xs text-slate-500 mt-1">Montant actuel: {{ number_format($package->cod_amount, 2) }} DT</p>
                    @error('new_cod_amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date préférée (conditionnel) -->
                <div id="dateField" style="display: none;">
                    <label for="preferred_date" class="block text-sm font-medium text-slate-700 mb-2">
                        Date souhaitée *
                    </label>
                    <input type="date" name="preferred_date" id="preferred_date"
                        min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                        max="{{ date('Y-m-d', strtotime('+7 days')) }}"
                        class="w-full px-3 py-2 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-transparent min-h-[44px]">
                    <p class="text-sm sm:text-xs text-slate-500 mt-1">Entre demain et dans 7 jours maximum</p>
                    @error('preferred_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 mb-2">
                        Description *
                    </label>
                    <textarea name="description" id="description" rows="4" required
                        class="w-full px-3 py-2 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Décrivez votre réclamation en détail...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Pièces jointes -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Pièces jointes <span class="text-slate-400">(optionnel)</span>
                    </label>
                    <div class="border-2 border-dashed border-slate-300 rounded-2xl p-4 sm:p-5 lg:p-6 text-center hover:border-slate-400 transition-colors">
                        <input type="file" name="attachments[]" id="attachments" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt"
                            class="hidden min-h-[44px]" onchange="updateFileList()">
                        <label for="attachments" class="cursor-pointer">
                            <svg class="w-8 h-8 mx-auto mb-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <p class="text-slate-600 font-medium">Cliquez pour ajouter des fichiers</p>
                            <p class="text-slate-400 text-sm">JPG, PNG, PDF, DOC, TXT (max 5MB chacun)</p>
                        </label>
                        <div id="fileList" class="mt-3 text-left hidden"></div>
                    </div>
                    @error('attachments.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Urgent -->
                <div class="flex items-center flex-col sm:flex-row">
                    <input type="checkbox" name="urgent" id="urgent" value="1"
                        class="h-4 w-4 text-red-600 focus:ring-red-500 border-slate-300 rounded min-h-[44px]">
                    <label for="urgent" class="ml-2 block text-sm text-slate-700">
                        Marquer comme <span class="font-medium text-red-600">urgent</span>
                    </label>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 flex items-center justify-between pt-6 border-t border-slate-200 flex-col sm:flex-row">
                <a href="{{ route('client.packages.show', $package) }}"
                   class="text-slate-600 hover:text-slate-800 transition-colors">
                    ← Retour au colis
                </a>

                <div class="flex gap-3 flex-col sm:flex-row">
                    <button type="button" onclick="history.back()"
                        class="px-4 sm:px-5 py-2.5 sm:py-3 text-slate-700 bg-white border border-slate-300 rounded-2xl hover:bg-slate-50 transition-colors transition-all duration-300 hover:-translate-y-1">
                        Annuler
                    </button>
                    <button type="submit"
                        class="px-4 sm:px-5 lg:px-6 py-2 bg-red-600 text-white rounded-2xl hover:bg-red-700 transition-colors font-medium">
                        Créer la réclamation
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Gestion des champs conditionnels
document.getElementById('type').addEventListener('change', function() {
    const codField = document.getElementById('codAmountField');
    const dateField = document.getElementById('dateField');
    const dateInput = document.getElementById('preferred_date');
    const codInput = document.getElementById('new_cod_amount');

    // Cacher tous les champs conditionnels
    codField.style.display = 'none';
    dateField.style.display = 'none';

    // Supprimer les attributs required
    codInput.removeAttribute('required');
    dateInput.removeAttribute('required');

    // Afficher les champs selon le type
    switch(this.value) {
        case 'CHANGE_COD':
            codField.style.display = 'block';
            codInput.setAttribute('required', 'required');
            break;
        case 'RESCHEDULE_TODAY':
        case 'FOURTH_ATTEMPT':
            dateField.style.display = 'block';
            dateInput.setAttribute('required', 'required');
            // Changer le label selon le type
            const label = dateField.querySelector('label');
            if (this.value === 'RESCHEDULE_TODAY') {
                label.innerHTML = 'Date de report *';
            } else {
                label.innerHTML = 'Date pour la 4ème tentative *';
            }
            break;
    }
});

// Gestion de l'affichage des fichiers
function updateFileList() {
    const input = document.getElementById('attachments');
    const fileList = document.getElementById('fileList');

    if (input.files.length > 0) {
        fileList.classList.remove('hidden');
        let html = '<div class="space-y-1">';
        for (let i = 0; i < input.files.length; i++) {
            const file = input.files[i];
            const size = (file.size / 1024).toFixed(1);
            html += `<div class="flex items-center gap-2 text-sm bg-slate-50 rounded px-2 py-1 flex-col sm:flex-row">
                <span class="text-slate-600">📎</span>
                <span class="text-slate-800">${file.name}</span>
                <span class="text-slate-500">(${size} KB)</span>
            </div>`;
        }
        html += '</div>';
        fileList.innerHTML = html;
    } else {
        fileList.classList.add('hidden');
    }
}
</script>
@endsection