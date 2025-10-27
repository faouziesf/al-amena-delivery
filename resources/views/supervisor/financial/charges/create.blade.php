<x-layouts.supervisor-new>
    <x-slot name="title">Nouvelle Charge Fixe</x-slot>
    <x-slot name="subtitle">Ajouter une charge récurrente à suivre</x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <form action="{{ route('supervisor.financial.charges.store') }}" 
                  method="POST"
                  x-data="{
                      amount: '',
                      periodicity: 'MONTHLY',
                      monthlyEquivalent: 0,
                      
                      calculateMonthly() {
                          const amount = parseFloat(this.amount) || 0;
                          switch(this.periodicity) {
                              case 'DAILY':
                                  this.monthlyEquivalent = amount * 30;
                                  break;
                              case 'WEEKLY':
                                  this.monthlyEquivalent = amount * 4.33;
                                  break;
                              case 'MONTHLY':
                                  this.monthlyEquivalent = amount;
                                  break;
                              case 'YEARLY':
                                  this.monthlyEquivalent = amount / 12;
                                  break;
                          }
                      }
                  }"
                  @input="calculateMonthly()">
                @csrf

                <div class="space-y-6">
                    <!-- Nom -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nom de la charge <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}"
                               required
                               placeholder="Ex: Loyer bureau, Électricité, Salaire..."
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
                        @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="3"
                                  placeholder="Description optionnelle de la charge..."
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                        @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Montant et Périodicité -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                Montant (DT) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   id="amount" 
                                   name="amount" 
                                   x-model="amount"
                                   step="0.001"
                                   min="0"
                                   value="{{ old('amount') }}"
                                   required
                                   placeholder="0.000"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('amount') border-red-500 @enderror">
                            @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="periodicity" class="block text-sm font-medium text-gray-700 mb-2">
                                Périodicité <span class="text-red-500">*</span>
                            </label>
                            <select id="periodicity" 
                                    name="periodicity" 
                                    x-model="periodicity"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('periodicity') border-red-500 @enderror">
                                <option value="DAILY">Journalière</option>
                                <option value="WEEKLY">Hebdomadaire</option>
                                <option value="MONTHLY" selected>Mensuelle</option>
                                <option value="YEARLY">Annuelle</option>
                            </select>
                            @error('periodicity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Équivalent Mensuel (Calculé automatiquement) -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-900">Équivalent Mensuel</p>
                                <p class="text-xs text-blue-700 mt-1">Calculé automatiquement selon la périodicité</p>
                            </div>
                            <p class="text-2xl font-bold text-blue-600">
                                <span x-text="monthlyEquivalent.toFixed(3)">0.000</span> DT
                            </p>
                        </div>
                    </div>

                    <!-- Statut -->
                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="is_active" class="ml-3 text-sm font-medium text-gray-700">
                            Charge active (sera comptabilisée dans les calculs)
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('supervisor.financial.charges.index') }}" 
                           class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                            Annuler
                        </a>
                        <button type="submit" 
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Créer la charge</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Aide -->
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-start space-x-3">
                <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h4 class="font-semibold text-yellow-900">💡 Conseils</h4>
                    <ul class="mt-2 text-sm text-yellow-800 space-y-1">
                        <li>• <strong>Journalière:</strong> Ex: consommation électrique quotidienne → Équiv. mensuel = montant × 30</li>
                        <li>• <strong>Hebdomadaire:</strong> Ex: salaire hebdomadaire → Équiv. mensuel = montant × 4.33</li>
                        <li>• <strong>Mensuelle:</strong> Ex: loyer → Équiv. mensuel = montant</li>
                        <li>• <strong>Annuelle:</strong> Ex: assurance → Équiv. mensuel = montant ÷ 12</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-layouts.supervisor-new>
