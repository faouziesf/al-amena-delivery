@extends('emails.layout')

@section('title', 'Changement de Statut de Colis')

@section('content')
    <h1>📦 Mise à Jour de Votre Colis</h1>

    <p>Bonjour {{ $userName }},</p>

    <p>Le statut de votre colis a été mis à jour :</p>

    <div class="info-box">
        <strong>Numéro de colis :</strong> {{ $packageCode }}<br>
        <strong>Ancien statut :</strong> {{ $oldStatusLabel }}<br>
        <strong>Nouveau statut :</strong> {{ $newStatusLabel }}<br>
        @if(isset($estimatedDelivery))
        <strong>Livraison estimée :</strong> {{ $estimatedDelivery }}
        @endif
    </div>

    @if($newStatus === 'DELIVERED')
        <p style="color: #10b981; font-weight: 600;">
            ✅ Votre colis a été livré avec succès !
        </p>
    @elseif($newStatus === 'OUT_FOR_DELIVERY')
        <p style="color: #3b82f6; font-weight: 600;">
            🚚 Votre colis est en cours de livraison.
        </p>
    @elseif($newStatus === 'RETURNED')
        <p style="color: #ef4444; font-weight: 600;">
            ↩️ Votre colis a été retourné.
        </p>
    @endif

    <p style="text-align: center;">
        <a href="{{ $trackingUrl }}" class="button">
            Suivre Mon Colis
        </a>
    </p>

    <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>

    <p>
        Cordialement,<br>
        <strong>L'équipe Al-Amena Delivery</strong>
    </p>
@endsection
