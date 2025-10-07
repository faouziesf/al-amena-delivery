@extends('layouts.deliverer-modern')

@section('title', 'Détail Ramassage')

@push('styles')
<style>
    body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
    .detail-card {
        background: white;
        border-radius: 1.5rem;
        padding: 2rem;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    }
    .info-row {
        padding: 1rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="detail-card">
        <div class="text-center mb-4">
            <div style="font-size: 3rem;">🏪</div>
            <h4 class="fw-bold">Ramassage #{{ $pickup->id }}</h4>
            <span class="badge bg-warning">{{ $pickup->status }}</span>
        </div>

        <div class="info-row">
            <div class="text-muted small mb-1">📍 Adresse de ramassage</div>
            <div class="fw-bold">{{ $pickup->pickup_address }}</div>
        </div>

        <div class="info-row">
            <div class="text-muted small mb-1">👤 Contact</div>
            <div>{{ $pickup->pickup_contact_name ?? 'N/A' }}</div>
        </div>

        <div class="info-row">
            <div class="text-muted small mb-1">📞 Téléphone</div>
            <div>
                <a href="tel:{{ $pickup->pickup_phone }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-phone"></i> {{ $pickup->pickup_phone }}
                </a>
            </div>
        </div>

        @if($pickup->pickup_notes)
            <div class="info-row">
                <div class="text-muted small mb-1">📝 Notes</div>
                <div>{{ $pickup->pickup_notes }}</div>
            </div>
        @endif

        @if($pickup->requested_pickup_date)
            <div class="info-row">
                <div class="text-muted small mb-1">📅 Date demandée</div>
                <div>{{ $pickup->requested_pickup_date->format('d/m/Y H:i') }}</div>
            </div>
        @endif

        <div class="mt-4">
            @if($pickup->status === 'assigned' || $pickup->status === 'pending')
                <form action="{{ route('deliverer.pickup.collect', $pickup->id) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-success w-100 py-3 rounded-pill fw-bold">
                        ✅ Marquer comme Collecté
                    </button>
                </form>
            @endif

            <a href="{{ route('deliverer.tournee') }}" class="btn btn-outline-secondary w-100">
                ← Retour à la tournée
            </a>
        </div>
    </div>
</div>
@endsection
