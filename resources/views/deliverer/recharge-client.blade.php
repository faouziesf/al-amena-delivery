@extends('layouts.deliverer-modern')

@section('title', 'Recharger Client')

@push('styles')
<style>
    body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
    .recharge-card {
        background: white;
        border-radius: 1.5rem;
        padding: 2rem;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="recharge-card">
        <div class="text-center mb-4">
            <div style="font-size: 3rem;">💳</div>
            <h4 class="fw-bold">Recharger un Client</h4>
            <p class="text-muted">Fonctionnalité à venir</p>
        </div>

        <div class="alert alert-info">
            Cette fonctionnalité sera bientôt disponible pour permettre aux livreurs de recharger les comptes clients.
        </div>

        <a href="{{ route('deliverer.menu') }}" class="btn btn-outline-secondary w-100">
            ← Retour au menu
        </a>
    </div>
</div>
@endsection
