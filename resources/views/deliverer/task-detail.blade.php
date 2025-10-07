@extends('layouts.deliverer-modern')

@section('title', 'Détail Colis')

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
    .info-row:last-child { border-bottom: none; }
    .action-btn {
        padding: 1rem;
        border-radius: 1rem;
        font-weight: bold;
        transition: all 0.3s;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="detail-card">
        <!-- Header -->
        <div class="text-center mb-4">
            <div style="font-size: 3rem;">📦</div>
            <h4 class="fw-bold">{{ $package->tracking_number }}</h4>
            <span class="badge bg-info">{{ $package->status }}</span>
        </div>

        <!-- COD Amount -->
        @if($package->cod_amount > 0)
            <div class="alert alert-success text-center mb-4">
                <div class="h3 fw-bold mb-0">{{ number_format($package->cod_amount, 3) }} DT</div>
                <small>Montant à collecter</small>
            </div>
        @endif

        <!-- Client Info -->
        <div class="info-row">
            <div class="text-muted small mb-1">👤 Destinataire</div>
            <div class="fw-bold">{{ $package->recipient_name }}</div>
        </div>

        <div class="info-row">
            <div class="text-muted small mb-1">📞 Téléphone</div>
            <div>
                <a href="tel:{{ $package->recipient_phone }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-phone"></i> {{ $package->recipient_phone }}
                </a>
            </div>
        </div>

        <div class="info-row">
            <div class="text-muted small mb-1">📍 Adresse</div>
            <div>{{ $package->recipient_address }}</div>
        </div>

        @if($package->recipient_city)
            <div class="info-row">
                <div class="text-muted small mb-1">🏙️ Ville</div>
                <div>{{ $package->recipient_city }}</div>
            </div>
        @endif

        @if($package->delivery_notes)
            <div class="info-row">
                <div class="text-muted small mb-1">📝 Notes</div>
                <div>{{ $package->delivery_notes }}</div>
            </div>
        @endif

        <!-- Actions -->
        <div class="mt-4">
            @if($package->status === 'AVAILABLE' || $package->status === 'ACCEPTED')
                <form action="{{ route('deliverer.simple.pickup', $package) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-primary action-btn w-100">
                        ✅ Marquer comme Collecté
                    </button>
                </form>
            @endif

            @if($package->status === 'PICKED_UP')
                <form action="{{ route('deliverer.simple.deliver', $package) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-success action-btn w-100">
                        🚚 Marquer comme Livré
                    </button>
                </form>

                <form action="{{ route('deliverer.simple.unavailable', $package) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-warning action-btn w-100">
                        ❌ Client Indisponible
                    </button>
                </form>
            @endif

            <a href="{{ route('deliverer.tournee') }}" class="btn btn-outline-secondary action-btn w-100">
                ← Retour à la tournée
            </a>
        </div>
    </div>
</div>
@endsection
