@extends('layouts.deliverer-modern')

@section('title', 'Ma Tournée')

@push('styles')
<style>
    body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
    .task-card {
        background: white;
        border-radius: 1rem;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    .task-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    .badge-livraison { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .badge-pickup { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .stats-card {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <!-- Stats -->
    <div class="stats-card mb-4">
        <div class="row text-center">
            <div class="col-3">
                <div class="h4 fw-bold text-purple mb-0">{{ $stats['total'] }}</div>
                <small class="text-muted">Total</small>
            </div>
            <div class="col-3">
                <div class="h4 fw-bold text-info mb-0">{{ $stats['livraisons'] }}</div>
                <small class="text-muted">Livraisons</small>
            </div>
            <div class="col-3">
                <div class="h4 fw-bold text-warning mb-0">{{ $stats['pickups'] }}</div>
                <small class="text-muted">Ramassages</small>
            </div>
            <div class="col-3">
                <div class="h4 fw-bold text-success mb-0">{{ $stats['completed'] }}</div>
                <small class="text-muted">Terminés</small>
            </div>
        </div>
    </div>

    <h5 class="text-white mb-3">📦 Mes Tâches ({{ $tasks->count() }})</h5>

    @if($tasks->isEmpty())
        <div class="text-center text-white py-5">
            <div style="font-size: 4rem;">✅</div>
            <h4>Aucune tâche en cours</h4>
            <p>Vous avez terminé toutes vos livraisons !</p>
        </div>
    @else
        @foreach($tasks as $task)
            <div class="task-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge {{ $task['type'] === 'livraison' ? 'badge-livraison' : 'badge-pickup' }}">
                            {{ $task['type'] === 'livraison' ? '📦 Livraison' : '🏪 Ramassage' }}
                        </span>
                        <div class="fw-bold mt-1">{{ $task['tracking_number'] }}</div>
                    </div>
                    @if($task['cod_amount'] > 0)
                        <div class="text-end">
                            <div class="fw-bold text-success">{{ number_format($task['cod_amount'], 3) }} DT</div>
                            <small class="text-muted">COD</small>
                        </div>
                    @endif
                </div>

                <div class="mb-2">
                    <div class="text-muted small">👤 Client</div>
                    <div class="fw-bold">{{ $task['recipient_name'] }}</div>
                </div>

                <div class="mb-2">
                    <div class="text-muted small">📍 Adresse</div>
                    <div>{{ $task['recipient_address'] }}</div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">📞 Téléphone</div>
                    <div>
                        <a href="tel:{{ $task['recipient_phone'] }}" class="text-decoration-none">
                            {{ $task['recipient_phone'] }}
                        </a>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    @if($task['type'] === 'livraison')
                        <a href="{{ route('deliverer.task.detail', $task['id']) }}" class="btn btn-sm btn-purple flex-fill">
                            Voir Détails
                        </a>
                    @else
                        <a href="{{ route('deliverer.pickup.detail', $task['pickup_id']) }}" class="btn btn-sm btn-warning flex-fill">
                            Voir Ramassage
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
