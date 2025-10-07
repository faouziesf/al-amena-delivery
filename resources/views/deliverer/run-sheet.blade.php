@extends('layouts.deliverer-modern')

@section('title', 'Run Sheet')

@push('styles')
<style>
    body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
    .runsheet-card {
        background: white;
        border-radius: 1.5rem;
        padding: 2rem;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="runsheet-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold">📋 Run Sheet</h3>
            <p class="text-muted">Feuille de route du jour</p>
        </div>

        <div class="alert alert-info">
            Fonctionnalité Run Sheet en développement.
        </div>

        <div class="d-grid gap-2">
            <a href="{{ route('deliverer.tournee') }}" class="btn btn-primary">
                📦 Voir ma tournée
            </a>
            <a href="{{ route('deliverer.menu') }}" class="btn btn-outline-secondary">
                ← Retour au menu
            </a>
        </div>
    </div>
</div>
@endsection
