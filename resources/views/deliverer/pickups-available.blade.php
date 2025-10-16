@extends('layouts.deliverer-modern')

@section('title', 'Ramassages Disponibles')

@section('content')
<div class="px-4 pb-4">
    <div class="max-w-md mx-auto">
        <h5 class="text-white font-bold text-xl mb-4 px-2">🏪 Ramassages Disponibles</h5>

        <div id="pickups-container">
            <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-12 text-center text-white">
                <div class="spinner w-12 h-12 border-4 border-white/30 border-t-white rounded-full animate-spin mx-auto mb-4"></div>
                <p>Chargement des ramassages...</p>
            </div>
        </div>

        <div class="mt-4">
            <a href="{{ route('deliverer.menu') }}" 
               class="block w-full bg-white/20 backdrop-blur-lg text-white text-center py-4 rounded-2xl font-semibold hover:bg-white/30 transition-all">
                ← Retour au menu
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadPickups();
});

function loadPickups() {
    fetch('{{ route("deliverer.api.available.pickups") }}')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('pickups-container');
            
            if (data.length === 0) {
                container.innerHTML = `
                    <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-12 text-center text-white">
                        <div class="text-6xl mb-4">✅</div>
                        <h4 class="text-xl font-bold mb-2">Aucun ramassage disponible</h4>
                        <p class="text-white/80">Tous les ramassages de votre zone ont été assignés</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = `<div class="space-y-3">` + data.map(pickup => `
                <div class="bg-white rounded-2xl shadow-lg p-4">
                    <div class="flex justify-between items-start mb-3">
                        <span class="inline-block px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-semibold">
                            🏪 Ramassage
                        </span>
                        <div class="text-xs text-gray-500">${pickup.requested_pickup_date || 'Date non définie'}</div>
                    </div>
                    <div class="space-y-2 text-sm mb-4">
                        <div>
                            <div class="text-xs text-gray-500">🗺️ Gouvernorat</div>
                            <div class="font-semibold text-indigo-600">${pickup.governorate} - ${pickup.delegation_name}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">📍 Adresse</div>
                            <div class="font-semibold text-gray-800">${pickup.pickup_address}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">👤 Contact</div>
                            <div class="text-gray-700">${pickup.pickup_contact_name || 'N/A'}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">📞 Téléphone</div>
                            <a href="tel:${pickup.pickup_phone}" class="text-indigo-600 font-medium hover:underline">${pickup.pickup_phone}</a>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">👥 Client</div>
                            <div class="text-gray-700">${pickup.client_name}</div>
                        </div>
                        ${pickup.pickup_notes ? `
                        <div>
                            <div class="text-xs text-gray-500">📝 Notes</div>
                            <div class="text-gray-600 text-xs italic">${pickup.pickup_notes}</div>
                        </div>
                        ` : ''}
                    </div>
                    <button onclick="acceptPickup(${pickup.id})" 
                            id="btn-${pickup.id}"
                            class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all active:scale-95">
                        ✅ Accepter ce ramassage
                    </button>
                </div>
            `).join('') + `</div>`;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('pickups-container').innerHTML = `
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg">
                    <strong>Erreur de chargement</strong><br>
                    <small>${error.message}</small>
                </div>
            `;
        });
}

function acceptPickup(id) {
    if (!confirm('Voulez-vous accepter ce ramassage ?')) return;
    
    const btn = document.getElementById(`btn-${id}`);
    const originalText = btn.innerHTML;
    
    // Afficher le loading sur le bouton
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin mx-auto"></div>';
    
    fetch(`/deliverer/api/pickups/${id}/accept`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Ramassage accepté !', 'success');
            // Recharger la liste après 1 seconde
            setTimeout(() => {
                loadPickups();
            }, 1000);
        } else {
            showToast(data.message || 'Erreur lors de l\'acceptation', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Erreur de connexion', 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}
</script>
@endpush
@endsection
