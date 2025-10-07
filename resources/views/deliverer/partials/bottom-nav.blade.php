<!-- Navigation Bottom Moderne -->
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 safe-bottom z-50">
    <div class="grid grid-cols-4 h-16">
        
        <!-- Tournée -->
        <a href="{{ route('deliverer.tournee') }}" 
           class="flex flex-col items-center justify-center space-y-1 {{ request()->routeIs('deliverer.tournee') ? 'text-indigo-600' : 'text-gray-600' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <span class="text-xs font-medium">Tournée</span>
        </a>
        
        <!-- Scanner -->
        <a href="{{ route('deliverer.scan.multi') }}" 
           class="flex flex-col items-center justify-center space-y-1 {{ request()->routeIs('deliverer.scan.*') ? 'text-indigo-600' : 'text-gray-600' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
            </svg>
            <span class="text-xs font-medium">Scanner</span>
        </a>
        
        <!-- Portefeuille -->
        <a href="{{ route('deliverer.wallet') }}" 
           class="flex flex-col items-center justify-center space-y-1 {{ request()->routeIs('deliverer.wallet') ? 'text-indigo-600' : 'text-gray-600' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            <span class="text-xs font-medium">Wallet</span>
        </a>
        
        <!-- Menu -->
        <a href="{{ route('deliverer.menu') }}" 
           class="flex flex-col items-center justify-center space-y-1 {{ request()->routeIs('deliverer.menu') ? 'text-indigo-600' : 'text-gray-600' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <span class="text-xs font-medium">Menu</span>
        </a>
        
    </div>
</nav>
