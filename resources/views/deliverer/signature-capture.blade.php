@extends('layouts.deliverer-modern')

@section('title', 'Capture Signature')

@section('content')
<div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Capture de signature</h1>
    <div class="modern-card p-4">
        <div id="signature-pad" class="border border-gray-300 rounded-lg mb-4" style="height: 300px;">
            <!-- Canvas for signature will be inserted here -->
        </div>
        
        <div class="flex space-x-2">
            <button id="clear-btn" class="bg-gray-500 text-white px-4 py-2 rounded">Effacer</button>
            <button id="save-btn" class="bg-green-500 text-white px-4 py-2 rounded">Enregistrer</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.createElement('canvas');
        canvas.width = document.getElementById('signature-pad').offsetWidth;
        canvas.height = 300;
        document.getElementById('signature-pad').appendChild(canvas);
        
        const signaturePad = new SignaturePad(canvas);
        
        document.getElementById('clear-btn').addEventListener('click', function() {
            signaturePad.clear();
        });
        
        document.getElementById('save-btn').addEventListener('click', function() {
            if (signaturePad.isEmpty()) {
                alert('Veuillez signer avant d\'enregistrer');
            } else {
                const data = signaturePad.toDataURL();
                // Envoyer la signature au serveur
                alert('Signature enregistrée avec succès!');
            }
        });
    });
</script>
@endsection
