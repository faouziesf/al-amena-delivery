@extends('layouts.deliverer-modern')

@section('title', 'Dashboard')

@push('styles')
<style>
    body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
    .dashboard-card {
        background: white;
        border-radius: 1.5rem;
        padding: 2rem;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="dashboard-card">
        <div class="text-center">
            <h3 class="fw-bold mb-4">🚚 Dashboard Livreur</h3>
            <p class="text-muted">Redirection vers la tournée...</p>
        </div>
    </div>
</div>

<script>
    setTimeout(() => {
        window.location.href = '{{ route("deliverer.tournee") }}';
    }, 1000);
</script>
@endsection
