@extends('layouts.deliverer')

@section('title', 'Changer mot de passe')

@section('content')
<div class="bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="px-4 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">🔒 Changer mot de passe</h1>
                    <p class="text-gray-600 mt-1">Sécurisez votre compte avec un nouveau mot de passe</p>
                </div>
                <a href="{{ route('deliverer.profile.show') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                    Retour au profil
                </a>
            </div>
        </div>
    </div>

    <div class="p-4 max-w-2xl mx-auto">
        <!-- Sécurité info -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
            <div class="flex items-start gap-4">
                <div class="bg-blue-100 p-2 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-blue-900 mb-2">Recommandations de sécurité</h3>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• Utilisez au moins 8 caractères</li>
                        <li>• Mélangez lettres majuscules, minuscules, chiffres et symboles</li>
                        <li>• Évitez les mots de passe évidents (nom, date de naissance, etc.)</li>
                        <li>• Ne partagez jamais votre mot de passe</li>
                        <li>• Changez-le régulièrement pour plus de sécurité</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Formulaire -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold mb-6">Nouveau mot de passe</h3>

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <p class="text-green-800 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-red-800 font-medium mb-2">Erreurs détectées :</p>
                            <ul class="text-sm text-red-700 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>• {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('deliverer.profile.password.update') }}" id="passwordForm">
                @csrf

                <!-- Mot de passe actuel -->
                <div class="mb-6">
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                        Mot de passe actuel *
                    </label>
                    <div class="relative">
                        <input type="password" id="current_password" name="current_password" required
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 pr-12 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('current_password') border-red-300 @enderror">
                        <button type="button" onclick="togglePassword('current_password')"
                                class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nouveau mot de passe -->
                <div class="mb-6">
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                        Nouveau mot de passe *
                    </label>
                    <div class="relative">
                        <input type="password" id="new_password" name="new_password" required minlength="8"
                               oninput="checkPasswordStrength()"
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 pr-12 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('new_password') border-red-300 @enderror">
                        <button type="button" onclick="togglePassword('new_password')"
                                class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Indicateur force du mot de passe -->
                    <div class="mt-2">
                        <div class="flex gap-1 mb-2">
                            <div id="strength-bar-1" class="h-2 bg-gray-200 rounded-full flex-1"></div>
                            <div id="strength-bar-2" class="h-2 bg-gray-200 rounded-full flex-1"></div>
                            <div id="strength-bar-3" class="h-2 bg-gray-200 rounded-full flex-1"></div>
                            <div id="strength-bar-4" class="h-2 bg-gray-200 rounded-full flex-1"></div>
                        </div>
                        <p id="strength-text" class="text-sm text-gray-500">Tapez votre mot de passe</p>
                    </div>

                    @error('new_password')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirmation nouveau mot de passe -->
                <div class="mb-6">
                    <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirmer le nouveau mot de passe *
                    </label>
                    <div class="relative">
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" required
                               oninput="checkPasswordMatch()"
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 pr-12 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <button type="button" onclick="togglePassword('new_password_confirmation')"
                                class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    <div id="password-match" class="mt-1 text-sm"></div>
                </div>

                <!-- Critères de validation -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h4 class="font-medium text-gray-900 mb-3">Critères de validation :</h4>
                    <div class="space-y-2 text-sm">
                        <div id="criteria-length" class="flex items-center gap-2 text-gray-600">
                            <span class="criteria-icon">⚪</span>
                            <span>Au moins 8 caractères</span>
                        </div>
                        <div id="criteria-uppercase" class="flex items-center gap-2 text-gray-600">
                            <span class="criteria-icon">⚪</span>
                            <span>Au moins une lettre majuscule</span>
                        </div>
                        <div id="criteria-lowercase" class="flex items-center gap-2 text-gray-600">
                            <span class="criteria-icon">⚪</span>
                            <span>Au moins une lettre minuscule</span>
                        </div>
                        <div id="criteria-number" class="flex items-center gap-2 text-gray-600">
                            <span class="criteria-icon">⚪</span>
                            <span>Au moins un chiffre</span>
                        </div>
                        <div id="criteria-special" class="flex items-center gap-2 text-gray-600">
                            <span class="criteria-icon">⚪</span>
                            <span>Au moins un caractère spécial (!@#$%^&*)</span>
                        </div>
                    </div>
                </div>

                <!-- Boutons -->
                <div class="flex gap-4">
                    <button type="submit" id="submitBtn"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white py-3 px-6 rounded-lg font-medium transition-colors">
                        Changer le mot de passe
                    </button>
                    <a href="{{ route('deliverer.profile.show') }}"
                       class="bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 px-6 rounded-lg font-medium">
                        Annuler
                    </a>
                </div>
            </form>
        </div>

        <!-- Conseils supplémentaires -->
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mt-6">
            <div class="flex items-start gap-3">
                <div class="bg-amber-100 p-2 rounded-lg">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                <div class="text-sm text-amber-800">
                    <p class="font-medium mb-1">Conseils pour un mot de passe fort :</p>
                    <ul class="space-y-1">
                        <li>• Utilisez une phrase que vous seul connaissez</li>
                        <li>• Remplacez certaines lettres par des chiffres ou symboles</li>
                        <li>• Évitez les informations personnelles évidentes</li>
                        <li>• N'utilisez pas le même mot de passe sur plusieurs sites</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    if (field.type === 'password') {
        field.type = 'text';
    } else {
        field.type = 'password';
    }
}

function checkPasswordStrength() {
    const password = document.getElementById('new_password').value;
    const strengthBars = [
        document.getElementById('strength-bar-1'),
        document.getElementById('strength-bar-2'),
        document.getElementById('strength-bar-3'),
        document.getElementById('strength-bar-4')
    ];
    const strengthText = document.getElementById('strength-text');

    // Reset bars
    strengthBars.forEach(bar => {
        bar.className = 'h-2 bg-gray-200 rounded-full flex-1';
    });

    let score = 0;
    let feedback = [];

    // Critères
    const criteria = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
    };

    // Mettre à jour les critères visuels
    updateCriteria(criteria);

    // Calculer score
    Object.values(criteria).forEach(met => {
        if (met) score++;
    });

    // Bonus pour longueur
    if (password.length >= 12) score += 0.5;
    if (password.length >= 16) score += 0.5;

    // Couleurs et texte selon score
    let color, text;
    if (score <= 2) {
        color = 'bg-red-500';
        text = 'Très faible';
    } else if (score <= 3) {
        color = 'bg-orange-500';
        text = 'Faible';
    } else if (score <= 4) {
        color = 'bg-yellow-500';
        text = 'Moyen';
    } else if (score <= 5) {
        color = 'bg-green-500';
        text = 'Fort';
    } else {
        color = 'bg-green-600';
        text = 'Très fort';
    }

    // Appliquer couleurs
    const barsToColor = Math.min(Math.ceil(score), 4);
    for (let i = 0; i < barsToColor; i++) {
        strengthBars[i].className = `h-2 ${color} rounded-full flex-1`;
    }

    strengthText.textContent = password.length > 0 ? text : 'Tapez votre mot de passe';
    strengthText.className = `text-sm ${password.length > 0 ?
        (score <= 2 ? 'text-red-600' :
         score <= 3 ? 'text-orange-600' :
         score <= 4 ? 'text-yellow-600' : 'text-green-600')
        : 'text-gray-500'}`;

    // Activer/désactiver bouton submit
    updateSubmitButton();
}

function updateCriteria(criteria) {
    Object.keys(criteria).forEach(key => {
        const element = document.getElementById(`criteria-${key}`);
        const icon = element.querySelector('.criteria-icon');
        if (criteria[key]) {
            element.className = 'flex items-center gap-2 text-green-600';
            icon.textContent = '✅';
        } else {
            element.className = 'flex items-center gap-2 text-gray-600';
            icon.textContent = '⚪';
        }
    });
}

function checkPasswordMatch() {
    const password = document.getElementById('new_password').value;
    const confirmation = document.getElementById('new_password_confirmation').value;
    const matchDiv = document.getElementById('password-match');

    if (confirmation.length === 0) {
        matchDiv.textContent = '';
        matchDiv.className = 'mt-1 text-sm';
    } else if (password === confirmation) {
        matchDiv.textContent = '✅ Les mots de passe correspondent';
        matchDiv.className = 'mt-1 text-sm text-green-600';
    } else {
        matchDiv.textContent = '❌ Les mots de passe ne correspondent pas';
        matchDiv.className = 'mt-1 text-sm text-red-600';
    }

    updateSubmitButton();
}

function updateSubmitButton() {
    const password = document.getElementById('new_password').value;
    const confirmation = document.getElementById('new_password_confirmation').value;
    const submitBtn = document.getElementById('submitBtn');

    const isValid = password.length >= 8 && password === confirmation;
    submitBtn.disabled = !isValid;
}

// Vérification en temps réel
document.getElementById('new_password').addEventListener('input', checkPasswordStrength);
document.getElementById('new_password_confirmation').addEventListener('input', checkPasswordMatch);

// Initialisation
checkPasswordStrength();
checkPasswordMatch();
</script>
@endpush
@endsection