@extends('layouts.client')

@section('title', 'Créer un Ticket de Support')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
                <a href="{{ route('client.tickets.index') }}" class="hover:text-gray-700">Tickets</a>
                <span>/</span>
                <span class="text-gray-900">Nouveau Ticket</span>
            </nav>

            <h1 class="text-3xl font-bold text-gray-900">Créer un Ticket de Support</h1>
            <p class="text-gray-600 mt-1">Décrivez votre problème ou votre question pour obtenir de l'aide</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Formulaire principal -->
            <div class="lg:col-span-2">
                <form method="POST" action="{{ route('client.tickets.store') }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow-sm border p-6">
                    @csrf

                    @if($complaint)
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-blue-800 font-medium">
                                    Création de ticket depuis la réclamation #{{ $complaint->id }}
                                </p>
                            </div>
                            <p class="text-blue-700 text-sm mt-1">
                                Les informations de la réclamation seront pré-remplies dans le ticket
                            </p>
                        </div>
                        <input type="hidden" name="complaint_id" value="{{ $complaint->id }}">
                    @endif

                    <!-- Type de ticket -->
                    <div class="mb-6">
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                            Type de demande <span class="text-red-500">*</span>
                        </label>
                        <select name="type" id="type" required
                                class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-300 @enderror">
                            <option value="">Sélectionnez le type</option>
                            <option value="COMPLAINT" {{ old('type', $complaint ? 'COMPLAINT' : '') === 'COMPLAINT' ? 'selected' : '' }}>
                                📋 Réclamation
                            </option>
                            <option value="QUESTION" {{ old('type') === 'QUESTION' ? 'selected' : '' }}>
                                ❓ Question générale
                            </option>
                            <option value="SUPPORT" {{ old('type') === 'SUPPORT' ? 'selected' : '' }}>
                                🛠️ Support technique
                            </option>
                            <option value="OTHER" {{ old('type') === 'OTHER' ? 'selected' : '' }}>
                                📝 Autre
                            </option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sujet -->
                    <div class="mb-6">
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                            Sujet <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="subject" id="subject" required
                               value="{{ old('subject', $complaint ? 'Réclamation - ' . $complaint->type : '') }}"
                               placeholder="Résumez votre demande en quelques mots"
                               class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('subject') border-red-300 @enderror">
                        @error('subject')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description détaillée <span class="text-red-500">*</span>
                        </label>
                        <textarea name="description" id="description" rows="6" required
                                  placeholder="Décrivez votre problème ou votre question en détail..."
                                  class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-300 @enderror">{{ old('description', $complaint ? $complaint->description : '') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">
                            Plus vous fournissez de détails, plus nous pourrons vous aider efficacement.
                        </p>
                    </div>

                    <!-- Priorité -->
                    <div class="mb-6">
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                            Priorité <span class="text-red-500">*</span>
                        </label>
                        <select name="priority" id="priority" required
                                class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('priority') border-red-300 @enderror">
                            <option value="NORMAL" {{ old('priority') === 'NORMAL' ? 'selected' : '' }}>
                                🟢 Normale - Réponse sous 24-48h
                            </option>
                            <option value="HIGH" {{ old('priority') === 'HIGH' ? 'selected' : '' }}>
                                🟡 Élevée - Réponse sous 12-24h
                            </option>
                            <option value="URGENT" {{ old('priority') === 'URGENT' ? 'selected' : '' }}>
                                🔴 Urgente - Réponse immédiate requise
                            </option>
                        </select>
                        @error('priority')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Package lié (optionnel) -->
                    <div class="mb-6">
                        <label for="package_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Colis concerné (optionnel)
                        </label>
                        <input type="text" name="package_code" id="package_code"
                               placeholder="Ex: PKG0200001"
                               class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-sm text-gray-500">
                            Si votre ticket concerne un colis spécifique, saisissez son code
                        </p>
                    </div>

                    <!-- Pièces jointes -->
                    <div class="mb-6">
                        <label for="attachments" class="block text-sm font-medium text-gray-700 mb-2">
                            Pièces jointes (optionnel)
                        </label>
                        <input type="file" name="attachments[]" id="attachments" multiple
                               accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                               class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-sm text-gray-500">
                            Formats acceptés: JPG, PNG, PDF, DOC, DOCX (max 10MB par fichier)
                        </p>
                        @error('attachments.*')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-6 border-t">
                        <a href="{{ route('client.tickets.index') }}"
                           class="text-gray-600 hover:text-gray-800 font-medium">
                            ← Retour aux tickets
                        </a>

                        <div class="flex space-x-3">
                            <button type="button" onclick="saveDraft()"
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                                Sauvegarder brouillon
                            </button>
                            <button type="submit"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                                Créer le ticket
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Sidebar d'aide -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border p-6 sticky top-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">💡 Conseils pour un bon ticket</h3>

                    <div class="space-y-4 text-sm">
                        <div class="flex items-start space-x-2">
                            <span class="text-green-600 font-semibold">✓</span>
                            <p class="text-gray-700">
                                <strong>Soyez précis :</strong> Un sujet clair aide notre équipe à vous orienter rapidement
                            </p>
                        </div>

                        <div class="flex items-start space-x-2">
                            <span class="text-green-600 font-semibold">✓</span>
                            <p class="text-gray-700">
                                <strong>Détaillez le problème :</strong> Expliquez ce qui ne fonctionne pas et ce que vous attendez
                            </p>
                        </div>

                        <div class="flex items-start space-x-2">
                            <span class="text-green-600 font-semibold">✓</span>
                            <p class="text-gray-700">
                                <strong>Joignez des preuves :</strong> Screenshots, photos ou documents pertinents
                            </p>
                        </div>

                        <div class="flex items-start space-x-2">
                            <span class="text-green-600 font-semibold">✓</span>
                            <p class="text-gray-700">
                                <strong>Choisissez la bonne priorité :</strong> Réservez "Urgent" aux vrais cas critiques
                            </p>
                        </div>
                    </div>

                    <hr class="my-6">

                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-blue-900 mb-2">🚀 Temps de réponse</h4>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li>• Normal: 24-48h</li>
                            <li>• Élevé: 12-24h</li>
                            <li>• Urgent: 2-4h</li>
                        </ul>
                    </div>

                    <hr class="my-6">

                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">📞 Autres moyens de contact</h4>
                        <div class="text-sm text-gray-600 space-y-2">
                            <p>📧 Email: support@alamena.com</p>
                            <p>📱 Tél: +216 70 123 456</p>
                            <p class="text-xs text-gray-500">Lun-Ven: 8h-18h</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function saveDraft() {
    const formData = new FormData(document.querySelector('form'));
    formData.append('_draft', '1');

    // Ici vous pouvez implémenter la sauvegarde en localStorage ou envoyer au serveur
    alert('Fonctionnalité de brouillon à implémenter');
}

// Auto-save draft every 2 minutes
setInterval(() => {
    const subject = document.getElementById('subject').value;
    const description = document.getElementById('description').value;

    if (subject || description) {
        localStorage.setItem('ticket_draft', JSON.stringify({
            subject: subject,
            description: description,
            type: document.getElementById('type').value,
            priority: document.getElementById('priority').value,
            timestamp: Date.now()
        }));
    }
}, 120000); // 2 minutes

// Load draft on page load
document.addEventListener('DOMContentLoaded', function() {
    const draft = localStorage.getItem('ticket_draft');
    if (draft) {
        const data = JSON.parse(draft);
        // Check if draft is less than 24h old
        if (Date.now() - data.timestamp < 24 * 60 * 60 * 1000) {
            if (confirm('Un brouillon a été trouvé. Voulez-vous le restaurer ?')) {
                document.getElementById('subject').value = data.subject || '';
                document.getElementById('description').value = data.description || '';
                document.getElementById('type').value = data.type || '';
                document.getElementById('priority').value = data.priority || '';
            }
        }
    }
});
</script>
@endsection