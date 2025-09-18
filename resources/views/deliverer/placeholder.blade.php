@extends('layouts.deliverer')

@section('title', $title ?? 'En développement')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div class="text-center">
        <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $title ?? 'Fonctionnalité' }}</h1>
        <p class="text-gray-600 mb-4">Cette section est en cours de développement.</p>
        <a href="{{ route('deliverer.dashboard') }}" 
           class="bg-blue-600 text-white px-6 py-2 rounded-xl font-medium hover:bg-blue-700 transition-colors">
            Retour au Dashboard
        </a>
    </div>
</div>
@endsection