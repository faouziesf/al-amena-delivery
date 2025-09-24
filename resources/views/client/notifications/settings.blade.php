@extends('layouts.client')

@section('title', 'Paramètres de Notifications')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-purple-900">Paramètres de Notifications</h1>
            <p class="mt-1 text-sm text-purple-600">
                Gérez vos préférences de notifications
            </p>
        </div>
        <a href="{{ route('client.notifications.index') }}"
           class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour aux notifications
        </a>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('client.notifications.update.settings') }}">
        @csrf

        <!-- Paramètres généraux -->
        <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Paramètres généraux</h3>

            <div class="space-y-6">
                <!-- Notifications par email -->
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Notifications par email</h4>
                        <p class="text-sm text-gray-500">Recevoir les notifications importantes par email</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="email_notifications" value="1"
                               class="sr-only peer" {{ old('email_notifications', $settings['email_notifications'] ?? true) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                    </label>
                </div>

                <!-- Notifications push -->
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Notifications push</h4>
                        <p class="text-sm text-gray-500">Recevoir des notifications dans le navigateur</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="push_notifications" value="1"
                               class="sr-only peer" {{ old('push_notifications', $settings['push_notifications'] ?? true) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                    </label>
                </div>

                <!-- Fréquence de résumé -->
                <div>
                    <label for="summary_frequency" class="block text-sm font-medium text-gray-700 mb-2">
                        Fréquence du résumé par email
                    </label>
                    <select name="summary_frequency" id="summary_frequency"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="never" {{ old('summary_frequency', $settings['summary_frequency'] ?? 'daily') === 'never' ? 'selected' : '' }}>Jamais</option>
                        <option value="daily" {{ old('summary_frequency', $settings['summary_frequency'] ?? 'daily') === 'daily' ? 'selected' : '' }}>Quotidien</option>
                        <option value="weekly" {{ old('summary_frequency', $settings['summary_frequency'] ?? 'daily') === 'weekly' ? 'selected' : '' }}>Hebdomadaire</option>
                        <option value="monthly" {{ old('summary_frequency', $settings['summary_frequency'] ?? 'daily') === 'monthly' ? 'selected' : '' }}>Mensuel</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Types de notifications -->
        <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Types de notifications</h3>

            <div class="space-y-6">
                <!-- Notifications de colis -->
                <div>
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Notifications de colis</h4>
                    <div class="space-y-3 pl-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Nouveau colis accepté</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notifications[package_accepted]" value="1"
                                       class="sr-only peer" {{ old('notifications.package_accepted', $settings['notifications']['package_accepted'] ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Colis collecté</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notifications[package_picked_up]" value="1"
                                       class="sr-only peer" {{ old('notifications.package_picked_up', $settings['notifications']['package_picked_up'] ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Colis livré</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notifications[package_delivered]" value="1"
                                       class="sr-only peer" {{ old('notifications.package_delivered', $settings['notifications']['package_delivered'] ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Colis retourné</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notifications[package_returned]" value="1"
                                       class="sr-only peer" {{ old('notifications.package_returned', $settings['notifications']['package_returned'] ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Notifications de portefeuille -->
                <div>
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Notifications de portefeuille</h4>
                    <div class="space-y-3 pl-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Rechargement approuvé</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notifications[wallet_topup_approved]" value="1"
                                       class="sr-only peer" {{ old('notifications.wallet_topup_approved', $settings['notifications']['wallet_topup_approved'] ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Retrait traité</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notifications[wallet_withdrawal_processed]" value="1"
                                       class="sr-only peer" {{ old('notifications.wallet_withdrawal_processed', $settings['notifications']['wallet_withdrawal_processed'] ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Paiement COD reçu</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notifications[wallet_cod_payment]" value="1"
                                       class="sr-only peer" {{ old('notifications.wallet_cod_payment', $settings['notifications']['wallet_cod_payment'] ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Notifications de réclamations -->
                <div>
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Notifications de réclamations</h4>
                    <div class="space-y-3 pl-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Réponse à ma réclamation</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notifications[complaint_response]" value="1"
                                       class="sr-only peer" {{ old('notifications.complaint_response', $settings['notifications']['complaint_response'] ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Réclamation résolue</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notifications[complaint_resolved]" value="1"
                                       class="sr-only peer" {{ old('notifications.complaint_resolved', $settings['notifications']['complaint_resolved'] ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Notifications de collecte -->
                <div>
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Notifications de collecte</h4>
                    <div class="space-y-3 pl-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Collecte assignée</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notifications[pickup_assigned]" value="1"
                                       class="sr-only peer" {{ old('notifications.pickup_assigned', $settings['notifications']['pickup_assigned'] ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Collecte terminée</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notifications[pickup_completed]" value="1"
                                       class="sr-only peer" {{ old('notifications.pickup_completed', $settings['notifications']['pickup_completed'] ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('client.notifications.index') }}"
               class="inline-flex items-center px-6 py-3 bg-gray-300 border border-transparent rounded-xl font-semibold text-sm text-gray-700 hover:bg-gray-400 transition ease-in-out duration-150">
                Annuler
            </a>
            <button type="submit"
                    class="inline-flex items-center px-6 py-3 bg-purple-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:bg-purple-700 transition ease-in-out duration-150">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Enregistrer les paramètres
            </button>
        </div>
    </form>
@endsection