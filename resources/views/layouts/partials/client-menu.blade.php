{{-- Menu Items for Client Layout --}}
<div class="space-y-1">
    {{-- Dashboard --}}
    <a href="{{ route('client.dashboard') }}" 
       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-smooth"
       :class="currentRoute === 'client.dashboard' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50'">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        <span class="font-medium">Tableau de bord</span>
    </a>

    {{-- Packages --}}
    <a href="{{ route('client.packages.index') }}" 
       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-smooth"
       :class="currentRoute.includes('packages') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50'">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        <span class="font-medium">Mes Colis</span>
    </a>

    {{-- Create Package --}}
    <a href="{{ route('client.packages.create') }}" 
       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-smooth text-gray-700 hover:bg-gray-50">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span class="font-medium">Nouveau Colis</span>
    </a>

    {{-- Pickup Requests --}}
    <a href="{{ route('client.pickup-requests.index') }}" 
       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-smooth"
       :class="currentRoute.includes('pickup-requests') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50'">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <span class="font-medium">Demandes de Collecte</span>
    </a>

    {{-- Pickup Addresses --}}
    <a href="{{ route('client.pickup-addresses.index') }}" 
       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-smooth"
       :class="currentRoute.includes('pickup-addresses') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50'">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <span class="font-medium">Adresses de Collecte</span>
    </a>

    {{-- Wallet --}}
    <a href="{{ route('client.wallet.index') }}" 
       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-smooth"
       :class="currentRoute.includes('wallet') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50'">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
        </svg>
        <span class="font-medium">Mon Wallet</span>
    </a>

    {{-- Divider --}}
    <div class="my-2 border-t border-gray-200"></div>

    {{-- Returns --}}
    <a href="{{ route('client.returns.pending') }}" 
       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-smooth"
       :class="currentRoute.includes('returns') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50'">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
        </svg>
        <span class="font-medium">Retours</span>
    </a>

    {{-- Manifests --}}
    <a href="{{ route('client.manifests.index') }}" 
       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-smooth"
       :class="currentRoute.includes('manifests') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50'">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <span class="font-medium">Manifestes</span>
    </a>

    {{-- Tickets --}}
    <a href="{{ route('client.tickets.index') }}" 
       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-smooth"
       :class="currentRoute.includes('tickets') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50'">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <span class="font-medium">Support & Tickets</span>
    </a>

    {{-- Divider --}}
    <div class="my-2 border-t border-gray-200"></div>

    {{-- Bank Accounts --}}
    <a href="{{ route('client.bank-accounts.index') }}" 
       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-smooth"
       :class="currentRoute.includes('bank-accounts') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50'">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
        </svg>
        <span class="font-medium">Comptes Bancaires</span>
    </a>

    {{-- Withdrawals --}}
    <a href="{{ route('client.withdrawals') }}" 
       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-smooth"
       :class="currentRoute.includes('withdrawals') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50'">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <span class="font-medium">Mes Retraits</span>
    </a>

    {{-- Profile --}}
    <a href="{{ route('client.profile.index') }}" 
       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-smooth"
       :class="currentRoute.includes('profile') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50'">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <span class="font-medium">Mon Profil</span>
    </a>

    {{-- Notifications --}}
    <a href="{{ route('client.notifications.index') }}" 
       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-smooth"
       :class="currentRoute.includes('notifications') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50'">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span class="font-medium">Notifications</span>
    </a>
</div>
