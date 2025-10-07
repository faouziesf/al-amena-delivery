@extends('layouts.deliverer-modern')

@section('title', 'Scanner Caméra')

@push('styles')
<style>
    body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
    .camera-card {
        background: white;
        border-radius: 1.5rem;
        padding: 2rem;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    }
    #camera-preview {
        width: 100%;
        max-width: 500px;
        height: 400px;
        background: #000;
        border-radius: 1rem;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="camera-card">
        <div class="text-center mb-4">
            <h4 class="fw-bold">📷 Scanner avec Caméra</h4>
            <p class="text-muted">Positionnez le QR code devant la caméra</p>
        </div>

        <div id="camera-preview" class="mb-4">
            <div class="text-center">
                <div class="spinner-border text-light mb-3" role="status"></div>
                <p>Initialisation de la caméra...</p>
            </div>
        </div>

        <div id="scan-result" class="alert alert-info d-none"></div>

        <div class="d-grid gap-2">
            <button id="start-camera" class="btn btn-primary">
                📷 Démarrer la caméra
            </button>
            <a href="{{ route('deliverer.scan.simple') }}" class="btn btn-outline-secondary">
                ← Scanner manuel
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Placeholder pour l'intégration future de la caméra
document.getElementById('start-camera').addEventListener('click', function() {
    alert('Fonctionnalité caméra en développement. Utilisez le scanner manuel pour le moment.');
    window.location.href = '{{ route("deliverer.scan.simple") }}';
});
</script>
@endpush
@endsection
