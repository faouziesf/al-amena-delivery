@extends('layouts.deliverer-modern')

@section('title', 'Ramassages Disponibles')

@push('styles')
<style>
    body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
    .pickup-card {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <h5 class="text-white mb-3">🏪 Ramassages Disponibles</h5>

    <div id="pickups-container">
        <div class="text-center text-white py-5">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-3">Chargement des ramassages...</p>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('deliverer.menu') }}" class="btn btn-light w-100">
            ← Retour au menu
        </a>
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
                    <div class="text-center text-white py-5">
                        <div style="font-size: 4rem;">✅</div>
                        <h4>Aucun ramassage disponible</h4>
                        <p>Revenez plus tard</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = data.map(pickup => `
                <div class="pickup-card">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="badge bg-warning">🏪 Ramassage</span>
                        <small class="text-muted">${pickup.requested_pickup_date || 'Date non définie'}</small>
                    </div>
                    <div class="mb-2">
                        <div class="text-muted small">📍 Adresse</div>
                        <div class="fw-bold">${pickup.pickup_address}</div>
                    </div>
                    <div class="mb-2">
                        <div class="text-muted small">👤 Contact</div>
                        <div>${pickup.pickup_contact_name || 'N/A'}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">📞 Téléphone</div>
                        <div><a href="tel:${pickup.pickup_phone}">${pickup.pickup_phone}</a></div>
                    </div>
                    <button onclick="acceptPickup(${pickup.id})" class="btn btn-primary w-100">
                        ✅ Accepter ce ramassage
                    </button>
                </div>
            `).join('');
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('pickups-container').innerHTML = `
                <div class="alert alert-danger">Erreur de chargement</div>
            `;
        });
}

function acceptPickup(id) {
    if (!confirm('Accepter ce ramassage ?')) return;
    
    fetch(`/deliverer/api/pickups/${id}/accept`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Ramassage accepté !');
            loadPickups();
        } else {
            alert(data.message || 'Erreur');
        }
    });
}
</script>
@endpush
@endsection
