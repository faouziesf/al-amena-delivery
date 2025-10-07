@extends('layouts.deliverer-modern')

@section('title', 'Détail Tâche')

@section('content')
<div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Détail de la tâche</h1>
    <div class="modern-card p-4">
        <h2 class="text-xl font-semibold mb-2" x-text="mockTask.recipient_name"></h2>
        <p class="mb-2" x-text="mockTask.recipient_address"></p>
        <p class="mb-2">Téléphone: <span x-text="mockTask.recipient_phone"></span></p>
        <p class="mb-2">Montant COD: <span x-text="mockTask.cod_amount"></span> TND</p>
        <p class="mb-2">Statut: <span x-text="mockTask.status"></span></p>
        
        <div class="mt-4 flex space-x-2">
            <button class="bg-blue-500 text-white px-4 py-2 rounded">Marquer comme ramassé</button>
            <button class="bg-green-500 text-white px-4 py-2 rounded">Marquer comme livré</button>
        </div>
    </div>
</div>
@endsection
