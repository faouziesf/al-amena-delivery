<x-layouts.supervisor-new>
    <x-slot name="title">Modifier Charge Fixe</x-slot>
    <x-slot name="subtitle">{{ $charge->name }}</x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <form action="{{ route('supervisor.financial.charges.update', $charge) }}" 
                  method="POST"
                  x-data="{
                      amount: '{{ old('amount', $charge->amount) }}',
                      periodicity: '{{ old('periodicity', $charge->periodicity) }}',
                      monthlyEquivalent: 0,
                      
                      calculateMonthly() {
                          const amount = parseFloat(this.amount) || 0;
                          switch(this.periodicity) {
                              case 'DAILY': this.monthlyEquivalent = amount * 30; break;
                              case 'WEEKLY': this.monthlyEquivalent = amount * 4.33; break;
                              case 'MONTHLY': this.monthlyEquivalent = amount; break;
                              case 'YEARLY': this.monthlyEquivalent = amount / 12; break;
                          }
                      }
                  }"
                  x-init="calculateMonthly()"
                  @input="calculateMonthly()">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nom de la charge <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $charge->name) }}" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="description" name="description" rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('description', $charge->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                Montant (DT) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="amount" name="amount" x-model="amount" step="0.001" min="0" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="periodicity" class="block text-sm font-medium text-gray-700 mb-2">
                                Périodicité <span class="text-red-500">*</span>
                            </label>
                            <select id="periodicity" name="periodicity" x-model="periodicity" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="DAILY">Journalière</option>
                                <option value="WEEKLY">Hebdomadaire</option>
                                <option value="MONTHLY">Mensuelle</option>
                                <option value="YEARLY">Annuelle</option>
                            </select>
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-900">Équivalent Mensuel</p>
                                <p class="text-xs text-blue-700 mt-1">Calculé automatiquement</p>
                            </div>
                            <p class="text-2xl font-bold text-blue-600">
                                <span x-text="monthlyEquivalent.toFixed(3)">0.000</span> DT
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                               {{ old('is_active', $charge->is_active) ? 'checked' : '' }}
                               class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="is_active" class="ml-3 text-sm font-medium text-gray-700">
                            Charge active
                        </label>
                    </div>

                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('supervisor.financial.charges.index') }}" 
                           class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Annuler
                        </a>
                        <button type="submit" 
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                            Mettre à Jour
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.supervisor-new>
