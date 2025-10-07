@extends('layouts.deliverer-modern')

@section('title', 'Scanner Colis')

@push('styles')
<style>
    body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
    .scan-card {
        background: white;
        border-radius: 1.5rem;
        padding: 2rem;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    }
    .code-input {
        font-size: 1.5rem;
        font-weight: bold;
        text-align: center;
        letter-spacing: 2px;
        padding: 1.5rem;
        border-radius: 1rem;
        border: 3px solid #667eea;
    }
    .scan-history-item {
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 0.75rem;
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="scan-card">
        <div class="text-center mb-4">
            <div style="font-size: 4rem;">📷</div>
            <h4 class="fw-bold">Scanner un Colis</h4>
            <p class="text-muted">Entrez ou scannez le code du colis</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Scan Form -->
        <form action="{{ route('deliverer.scan.submit') }}" method="POST">
            @csrf
            <div class="mb-4">
                <input type="text" 
                       name="code" 
                       class="form-control code-input" 
                       placeholder="CODE COLIS"
                       autofocus
                       required>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold">
                🔍 Rechercher
            </button>
        </form>

        <!-- Scan History -->
        @if(session('last_scans'))
            <div class="mt-4">
                <h6 class="mb-3">Derniers scans</h6>
                @foreach(session('last_scans') as $scan)
                    <div class="scan-history-item">
                        <div class="d-flex justify-content-between">
                            <div class="fw-bold">{{ $scan['code'] }}</div>
                            <small class="text-muted">{{ $scan['time'] }}</small>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-4">
            <a href="{{ route('deliverer.scan.multi') }}" class="btn btn-outline-primary w-100 mb-2">
                📸 Scanner Multiple
            </a>
            <a href="{{ route('deliverer.menu') }}" class="btn btn-outline-secondary w-100">
                ← Retour au menu
            </a>
        </div>
    </div>
</div>
@endsection
