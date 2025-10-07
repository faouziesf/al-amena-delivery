@extends('layouts.deliverer-modern')

@section('title', 'Mes Retraits')

@push('styles')
<style>
    body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
    .withdrawal-card {
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
    <h5 class="text-white mb-3">💵 Mes Retraits Espèces</h5>

    @if($withdrawals->isEmpty())
        <div class="text-center text-white py-5">
            <div style="font-size: 4rem;">✅</div>
            <h4>Aucun retrait assigné</h4>
            <p>Vous n'avez pas de retraits en cours</p>
        </div>
    @else
        @foreach($withdrawals as $withdrawal)
            <div class="withdrawal-card">
                <div class="d-flex justify-content-between mb-2">
                    <span class="badge bg-info">💵 Retrait</span>
                    <span class="badge bg-warning">{{ $withdrawal->status }}</span>
                </div>

                <div class="mb-2">
                    <div class="text-muted small">👤 Client</div>
                    <div class="fw-bold">{{ $withdrawal->client->name ?? 'N/A' }}</div>
                </div>

                <div class="mb-2">
                    <div class="text-muted small">💰 Montant</div>
                    <div class="h5 fw-bold text-success mb-0">
                        {{ number_format($withdrawal->amount, 3) }} DT
                    </div>
                </div>

                @if($withdrawal->delivery_address)
                    <div class="mb-2">
                        <div class="text-muted small">📍 Adresse</div>
                        <div>{{ $withdrawal->delivery_address }}</div>
                    </div>
                @endif

                <div class="mt-3">
                    @if($withdrawal->status === 'READY_FOR_DELIVERY')
                        <form action="{{ route('deliverer.withdrawals.delivered', $withdrawal) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                ✅ Marquer comme Livré
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    @endif

    <div class="mt-3">
        <a href="{{ route('deliverer.menu') }}" class="btn btn-light w-100">
            ← Retour au menu
        </a>
    </div>
</div>
@endsection
