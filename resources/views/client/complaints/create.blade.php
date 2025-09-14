@extends('layouts.client')

@section('title', 'Créer une Réclamation')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-purple-900">Créer une Réclamation</h1>
            <p class="mt-1 text-sm text-purple-600">
                Signalez un problème concernant le colis <strong>{{ $package->package_code }}</strong>
            </p>
        </div>
        
        <div class="flex items-center space-x-3">
            <a href="{{ route('client.packages.show', $package) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-xl font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition ease-in-out duration-150">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                </svg>
                Retour au Colis
            </a>
        </div>
    </div>
@endsection

@section('content')
    <form action="{{ route('client.complaints.store', $package) }}" 
          method="POST" 
          x-data="complaintForm()" 
          @submit="onSubmit"
          class="max-w-4xl mx-auto space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Formulaire principal -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Informations sur le colis -->
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <div class="flex items-center mb-4">
                        <div class="h-8 w-8 rounded-lg bg-purple-100 flex items-center justify-center">
                            <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                            </svg>
                        </div>
                        <h3 class="ml-3 text-lg font-semibold text-purple-900">Colis Concerné</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-gray-500">Code colis</span>
                                <p class="text-lg font-semibold text-purple-900">{{ $package->package_code }}</p>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-500">Statut actuel</span>
                                <div class="mt-1">
                                    <x-client.package-status-badge :status="$package->status" size="md" />
                                </div>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-500">COD</span>
                                <p class="text-sm text-gray-900">{{ number_format($package->cod_amount, 3) }} DT</p>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-gray-500">Destinataire</span>
                                <p class="text-sm text-gray-900">{{ $package->recipient_name }}</p>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-500">Destination</span>
                                <p class="text-sm text-gray-900">{{ $package->delegationTo->name }}</p>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-500">Tentatives</span>
                                <p class="text-sm text-gray-900">{{ $package->delivery_attempts }}/3</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulaire de réclamation -->
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <div class="flex items-center mb-6">
                        <div class="h-8 w-8 rounded-lg bg-orange-100 flex items-center justify-center">
                            <svg class="h-5 w-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <h3 class="ml-3 text-lg font-semibold text-purple-900">Détails de la Réclamation</h3>
                    </div>

                    <div class="space-y-6">
                        <!-- Type de réclamation -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Type de réclamation <span class="text-red-500">*</span>
                            </label>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Changement COD -->
                                <div class="relative">
                                    <label class="flex items-center p-4 border border-gray-300 rounded-xl hover:border-purple-500 cursor-pointer transition-colors"
                                           :class="{ 'border-purple-500 bg-purple-50': form.type === 'CHANGE_COD' }">
                                        <input type="radio" 
                                               name="type" 
                                               value="CHANGE_COD" 
                                               x-model="form.type"
                                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300">
                                        <div class="ml-3">
                                            <div class="flex items-center">
                                                <svg class="h-5 w-5 text-purple-600 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H4.5m-1.5 0H3c.621 0 1.125.504 1.125 1.125v.375M3.75 15h-.75v.75c0 .621.504 1.125 1.125 1.125h.75m0-1.5v.375c0 .621.504 1.125 1.125 1.125H6.75m-3 0H4.5c-.621 0-1.125-.504-1.125-1.125V15m0 0h-.75" />
                                                </svg>
                                                <span class="text-sm font-medium text-gray-900">Changement COD</span>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1">Modifier le montant à encaisser</p>
                                        </div>
                                    </label>
                                </div>

                                <!-- Retard livraison -->
                                <div class="relative">
                                    <label class="flex items-center p-4 border border-gray-300 rounded-xl hover:border-purple-500 cursor-pointer transition-colors"
                                           :class="{ 'border-purple-500 bg-purple-50': form.type === 'DELIVERY_DELAY' }">
                                        <input type="radio" 
                                               name="type" 
                                               value="DELIVERY_DELAY" 
                                               x-model="form.type"
                                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300">
                                        <div class="ml-3">
                                            <div class="flex items-center">
                                                <svg class="h-5 w-5 text-orange-600 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span class="text-sm font-medium text-gray-900">Retard de livraison</span>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1">Délai de livraison dépassé</p>
                                        </div>
                                    </label>
                                </div>

                                <!-- Demande de retour -->
                                <div class="relative">
                                    <label class="flex items-center p-4 border border-gray-300 rounded-xl hover:border-purple-500 cursor-pointer transition-colors"
                                           :class="{ 'border-purple-500 bg-purple-50': form.type === 'REQUEST_RETURN' }">
                                        <input type="radio" 
                                               name="type" 
                                               value="REQUEST_RETURN" 
                                               x-model="form.type"
                                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300">
                                        <div class="ml-3">
                                            <div class="flex items-center">
                                                <svg class="h-5 w-5 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                                                </svg>
                                                <span class="text-sm font-medium text-gray-900">Demande de retour</span>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1">Retourner le colis à l'expéditeur</p>
                                        </div>
                                    </label>
                                </div>

                                <!-- Report jour J -->
                                <div class="relative">
                                    <label class="flex items-center p-4 border border-gray-300 rounded-xl hover:border-purple-500 cursor-pointer transition-colors"
                                           :class="{ 'border-purple-500 bg-purple-50': form.type === 'RESCHEDULE_TODAY' }">
                                        <input type="radio" 
                                               name="type" 
                                               value="RESCHEDULE_TODAY" 
                                               x-model="form.type"
                                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300">
                                        <div class="ml-3">
                                            <div class="flex items-center">
                                                <svg class="h-5 w-5 text-indigo-600 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 005.25 9h13.5a2.25 2.25 0 002.25 2.25v7.5" />
                                                </svg>
                                                <span class="text-sm font-medium text-gray-900">Report aujourd'hui</span>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1">Reporter la livraison dans la semaine</p>
                                        </div>
                                    </label>
                                </div>

                                <!-- 4ème tentative -->
                                <div class="relative">
                                    <label class="flex items-center p-4 border border-gray-300 rounded-xl hover:border-purple-500 cursor-pointer transition-colors"
                                           :class="{ 'border-purple-500 bg-purple-50': form.type === 'FOURTH_ATTEMPT' }">
                                        <input type="radio" 
                                               name="type" 
                                               value="FOURTH_ATTEMPT" 
                                               x-model="form.type"
                                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300">
                                        <div class="ml-3">
                                            <div class="flex items-center">
                                                <svg class="h-5 w-5 text-green-600 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                                </svg>
                                                <span class="text-sm font-medium text-gray-900">4ème tentative</span>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1">Demander une nouvelle tentative</p>
                                        </div>
                                    </label>
                                </div>

                                <!-- Personnalisé -->
                                <div class="relative">
                                    <label class="flex items-center p-4 border border-gray-300 rounded-xl hover:border-purple-500 cursor-pointer transition-colors"
                                           :class="{ 'border-purple-500 bg-purple-50': form.type === 'CUSTOM' }">
                                        <input type="radio" 
                                               name="type" 
                                               value="CUSTOM" 
                                               x-model="form.type"
                                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300">
                                        <div class="ml-3">
                                            <div class="flex items-center">
                                                <svg class="h-5 w-5 text-gray-600 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                                                </svg>
                                                <span class="text-sm font-medium text-gray-900">Autre problème</span>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1">Décrire un problème spécifique</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            @error('type')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nouveau montant COD (si changement COD sélectionné) -->
                        <div x-show="form.type === 'CHANGE_COD'" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95">
                            
                            <div class="border border-purple-200 rounded-xl p-4 bg-purple-50">
                                <h4 class="text-sm font-medium text-purple-900 mb-4">Nouveau Montant COD</h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">COD actuel</label>
                                        <div class="mt-1 px-3 py-2 bg-gray-100 border border-gray-300 rounded-xl">
                                            <span class="text-lg font-semibold text-gray-900">{{ number_format($package->cod_amount, 3) }} DT</span>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label for="new_cod_amount" class="block text-sm font-medium text-gray-700">
                                            Nouveau COD <span class="text-red-500">*</span>
                                        </label>
                                        <div class="mt-1 relative">
                                            <input type="number" 
                                                   name="new_cod_amount" 
                                                   id="new_cod_amount" 
                                                   x-model="form.new_cod_amount"
                                                   value="{{ old('new_cod_amount') }}"
                                                   step="0.001"
                                                   min="0"
                                                   max="9999.999"
                                                   :required="form.type === 'CHANGE_COD'"
                                                   class="block w-full px-3 py-2 pr-12 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">DT</span>
                                            </div>
                                        </div>
                                        @error('new_cod_amount')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                                    <p class="text-xs text-blue-700">
                                        💡 <strong>Info:</strong> Le changement de COD sera traité par notre équipe commerciale. 
                                        Vous serez notifié une fois la modification effectuée.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Description détaillée -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">
                                Description du problème <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <textarea name="description" 
                                          id="description" 
                                          rows="5"
                                          x-model="form.description"
                                          required
                                          placeholder="Décrivez en détail le problème rencontré avec ce colis..."
                                          class="block w-full px-3 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-500 focus:outline-none focus:ring-purple-500 focus:border-purple-500">{{ old('description') }}</textarea>
                            </div>
                            
                            <div class="mt-2 flex justify-between text-sm">
                                <span class="text-gray-500">Soyez le plus précis possible pour un traitement rapide</span>
                                <span class="text-gray-400" x-text="form.description.length + '/1000'"></span>
                            </div>
                            
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar - Informations -->
            <div class="space-y-6">
                
                <!-- Priorité automatique -->
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <h3 class="text-lg font-semibold text-purple-900 mb-4">Priorité de Traitement</h3>
                    
                    <div class="space-y-3">
                        <div x-show="form.type === 'CHANGE_COD'" class="flex items-center p-3 bg-red-50 rounded-lg border border-red-200">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">Priorité Haute</p>
                                <p class="text-xs text-red-600">Les changements de COD sont traités en priorité</p>
                            </div>
                        </div>
                        
                        <div x-show="form.type !== 'CHANGE_COD' && form.type !== ''" class="flex items-center p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-800">Priorité Normale</p>
                                <p class="text-xs text-blue-600">Traitement sous 24-48h ouvrables</p>
                            </div>
                        </div>
                        
                        <div x-show="form.type === ''" class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-700">Sélectionnez un type</p>
                                <p class="text-xs text-gray-500">La priorité sera définie automatiquement</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations utiles -->
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <h3 class="text-lg font-semibold text-purple-900 mb-4">À Savoir</h3>
                    
                    <div class="space-y-4 text-sm text-gray-600">
                        <div class="flex items-start">
                            <svg class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Toutes les réclamations sont traitées gratuitement</span>
                        </div>
                        
                        <div class="flex items-start">
                            <svg class="h-4 w-4 text-blue-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <span>Vous recevrez une notification dès qu'un commercial prendra en charge votre réclamation</span>
                        </div>
                        
                        <div class="flex items-start">
                            <svg class="h-4 w-4 text-purple-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <span>Les réclamations ne peuvent être créées que tant que le colis n'est pas payé ou retourné</span>
                        </div>
                        
                        <div class="flex items-start">
                            <svg class="h-4 w-4 text-yellow-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <span>Plus votre description est précise, plus le traitement sera rapide</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="space-y-3">
                    <button type="submit" 
                            :disabled="!canSubmit || loading"
                            :class="{ 'opacity-50 cursor-not-allowed': !canSubmit || loading }"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50">
                        <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-show="!loading">Créer la Réclamation</span>
                        <span x-show="loading">Création en cours...</span>
                    </button>
                    
                    <a href="{{ route('client.packages.show', $package) }}" 
                       class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-xl shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Annuler
                    </a>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    function complaintForm() {
        return {
            loading: false,
            form: {
                type: '{{ old("type", "") }}',
                description: '{{ old("description") }}',
                new_cod_amount: parseFloat('{{ old("new_cod_amount", 0) }}') || 0
            },
            
            get canSubmit() {
                const hasType = this.form.type !== '';
                const hasDescription = this.form.description.trim().length > 10;
                const codValid = this.form.type !== 'CHANGE_COD' || (this.form.new_cod_amount >= 0);
                
                return hasType && hasDescription && codValid && !this.loading;
            },
            
            onSubmit() {
                this.loading = true;
            },
            
            init() {
                // Pré-remplir avec des valeurs appropriées selon le statut du colis
                @if($package->status === 'UNAVAILABLE' && $package->delivery_attempts >= 3)
                    this.form.type = 'FOURTH_ATTEMPT';
                    this.form.description = 'Mon colis a été marqué comme "non disponible" après 3 tentatives. Je souhaiterais une 4ème tentative de livraison.';
                @elseif($package->status === 'DELIVERED')
                    this.form.type = 'CHANGE_COD';
                    this.form.description = 'Le colis a été livré mais je souhaite modifier le montant COD.';
                @endif
            }
        }
    }
</script>
@endpush