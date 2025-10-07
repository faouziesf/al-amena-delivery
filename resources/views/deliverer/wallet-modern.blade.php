@extends('layouts.deliverer-modern')

@section('title', 'Mon Wallet')

@push('styles')
<style>
    body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
    .wallet-card {
        background: white;
        border-radius: 1.5rem;
        padding: 2rem;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    }
    .balance-display {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 1.25rem;
        padding: 2rem;
        text-align: center;
        margin-bottom: 2rem;
    }
    .transaction-item {
        padding: 1rem;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="wallet-card">
        <h4 class="text-center mb-4">💰 Mon Wallet</h4>

        <!-- Balance Display -->
        <div class="balance-display">
            <div class="text-white-50 mb-2">Solde Disponible</div>
            <div class="h1 fw-bold mb-0">0.000 DT</div>
            <small class="text-white-50">Mis à jour maintenant</small>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-6">
                <div class="text-center p-3 bg-light rounded">
                    <div class="h5 fw-bold text-success mb-0">0.000 DT</div>
                    <small class="text-muted">Collecté aujourd'hui</small>
                </div>
            </div>
            <div class="col-6">
                <div class="text-center p-3 bg-light rounded">
                    <div class="h5 fw-bold text-warning mb-0">0.000 DT</div>
                    <small class="text-muted">En attente</small>
                </div>
            </div>
        </div>

        <!-- Transactions -->
        <h6 class="mb-3">Transactions Récentes</h6>
        <div class="text-center text-muted py-4">
            <div style="font-size: 3rem;">📊</div>
            <p>Aucune transaction récente</p>
        </div>

        <!-- Actions -->
        <div class="mt-4">
            <a href="{{ route('deliverer.menu') }}" class="btn btn-outline-secondary w-100">
                ← Retour au menu
            </a>
        </div>
    </div>
</div>
@endsection
