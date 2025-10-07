@extends('layouts.deliverer-modern')

@section('title', 'Menu Principal')

@push('styles')
<style>
    body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
    .menu-card {
        background: white;
        border-radius: 1.5rem;
        padding: 2rem;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        transition: transform 0.3s ease;
    }
    .menu-card:hover {
        transform: translateY(-5px);
    }
    .menu-item {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 1.25rem;
        padding: 1.5rem;
        margin-bottom: 1rem;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        transition: all 0.3s ease;
    }
    .menu-item:hover {
        transform: translateX(10px);
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        color: white;
    }
    .stat-badge {
        background: rgba(255,255,255,0.2);
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="menu-card">
        <div class="text-center mb-4">
            <h1 class="h3 fw-bold text-purple mb-2">🚚 Menu Livreur</h1>
            <p class="text-muted">Bienvenue {{ Auth::user()->name }}</p>
        </div>

        <!-- Stats rapides -->
        <div class="row mb-4">
            <div class="col-4">
                <div class="text-center">
                    <div class="h2 fw-bold text-purple">{{ $activeCount }}</div>
                    <small class="text-muted">Actifs</small>
                </div>
            </div>
            <div class="col-4">
                <div class="text-center">
                    <div class="h2 fw-bold text-success">{{ $todayCount }}</div>
                    <small class="text-muted">Livrés</small>
                </div>
            </div>
            <div class="col-4">
                <div class="text-center">
                    <div class="h2 fw-bold text-info">{{ number_format($balance, 3) }}</div>
                    <small class="text-muted">DT</small>
                </div>
            </div>
        </div>

        <!-- Menu Items -->
        <div class="menu-list">
            <a href="{{ route('deliverer.tournee') }}" class="menu-item">
                <div class="d-flex align-items-center">
                    <div class="me-3" style="font-size: 2rem;">📦</div>
                    <div>
                        <div class="fw-bold">Ma Tournée</div>
                        <small>Voir mes livraisons</small>
                    </div>
                </div>
                <span class="stat-badge">{{ $activeCount }}</span>
            </a>

            <a href="{{ route('deliverer.scan.simple') }}" class="menu-item">
                <div class="d-flex align-items-center">
                    <div class="me-3" style="font-size: 2rem;">📷</div>
                    <div>
                        <div class="fw-bold">Scanner</div>
                        <small>Scanner un colis</small>
                    </div>
                </div>
                <i class="fas fa-chevron-right"></i>
            </a>

            <a href="{{ route('deliverer.scan.multi') }}" class="menu-item">
                <div class="d-flex align-items-center">
                    <div class="me-3" style="font-size: 2rem;">📸</div>
                    <div>
                        <div class="fw-bold">Scanner Multiple</div>
                        <small>Scanner plusieurs colis</small>
                    </div>
                </div>
                <i class="fas fa-chevron-right"></i>
            </a>

            <a href="{{ route('deliverer.pickups.available') }}" class="menu-item">
                <div class="d-flex align-items-center">
                    <div class="me-3" style="font-size: 2rem;">🏪</div>
                    <div>
                        <div class="fw-bold">Ramassages</div>
                        <small>Collectes disponibles</small>
                    </div>
                </div>
                <i class="fas fa-chevron-right"></i>
            </a>

            <a href="{{ route('deliverer.wallet') }}" class="menu-item">
                <div class="d-flex align-items-center">
                    <div class="me-3" style="font-size: 2rem;">💰</div>
                    <div>
                        <div class="fw-bold">Mon Wallet</div>
                        <small>Solde et transactions</small>
                    </div>
                </div>
                <span class="stat-badge">{{ number_format($balance, 3) }} DT</span>
            </a>

            <a href="{{ route('deliverer.withdrawals.index') }}" class="menu-item">
                <div class="d-flex align-items-center">
                    <div class="me-3" style="font-size: 2rem;">💵</div>
                    <div>
                        <div class="fw-bold">Retraits Espèces</div>
                        <small>Mes retraits assignés</small>
                    </div>
                </div>
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>
    </div>
</div>
@endsection
