@extends('emails.layout')

@section('title', $title ?? 'Notification')

@section('content')
    <h1>{{ $icon ?? '🔔' }} {{ $title }}</h1>

    <p>Bonjour {{ $userName }},</p>

    <p>{!! nl2br(e($message)) !!}</p>

    @if(isset($details) && !empty($details))
        <div class="info-box">
            @foreach($details as $label => $value)
                <strong>{{ $label }} :</strong> {{ $value }}<br>
            @endforeach
        </div>
    @endif

    @if(isset($actionUrl) && isset($actionText))
        <p style="text-align: center;">
            <a href="{{ $actionUrl }}" class="button">
                {{ $actionText }}
            </a>
        </p>
    @endif

    @if(isset($additionalMessage))
        <p>{!! nl2br(e($additionalMessage)) !!}</p>
    @endif

    <p>
        Cordialement,<br>
        <strong>L'équipe Al-Amena Delivery</strong>
    </p>
@endsection
