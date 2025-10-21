@extends('emails.layout')

@section('title', 'Nouveau Colis Assigné')

@section('content')
    <h1>📦 Nouveau Colis Assigné</h1>

    <p>Bonjour {{ $delivererName }},</p>

    <p>Un nouveau colis vous a été assigné pour livraison :</p>

    <div class="info-box">
        <strong>Numéro de colis :</strong> {{ $packageCode }}<br>
        <strong>Expéditeur :</strong> {{ $senderName }}<br>
        <strong>Destinataire :</strong> {{ $receiverName }}<br>
        <strong>Adresse de livraison :</strong> {{ $deliveryAddress }}<br>
        <strong>Gouvernorat :</strong> {{ $gouvernorat }}<br>
        @if(isset($phoneNumber))
        <strong>Téléphone :</strong> {{ $phoneNumber }}<br>
        @endif
        @if(isset($codAmount) && $codAmount > 0)
        <strong>COD :</strong> {{ $codAmount }} DT
        @endif
    </div>

    <p style="text-align: center;">
        <a href="{{ $packageUrl }}" class="button">
            Voir les Détails
        </a>
    </p>

    <p>Merci de procéder à la livraison dans les meilleurs délais.</p>

    <p>
        Cordialement,<br>
        <strong>L'équipe Al-Amena Delivery</strong>
    </p>
@endsection
