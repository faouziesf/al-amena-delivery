<!-- Sidebar with Purple Gradient -->
<div class="flex grow flex-col gap-y-5 overflow-y-auto sidebar-gradient px-6 pb-4 shadow-xl">
    
    <!-- Logo Section -->
    <div class="flex h-16 shrink-0 items-center">
        <div class="flex items-center">
            <div class="h-10 w-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0V8.25a1.5 1.5 0 013 0v10.5zM12 7.5V21M15.75 18.75a1.5 1.5 0 01-3 0V8.25a1.5 1.5 0 013 0v10.5z" />
                </svg>
            </div>
            <span class="ml-3 text-xl font-bold text-white">DeliveryApp</span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex flex-1 flex-col">
        <ul role="list" class="flex flex-1 flex-col gap-y-7">
            <li>
                <ul role="list" class="-mx-2 space-y-2">
                    
                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('client.dashboard') }}" 
                           class="{{ request()->routeIs('client.dashboard') 
                               ? 'bg-white/20 text-white shadow-md' 
                               : 'text-purple-200 hover:text-white hover:bg-white/10' }} 
                               group flex gap-x-3 rounded-xl p-3 text-sm leading-6 font-semibold transition-all duration-200">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                            Dashboard
                        </a>
                    </li>

                    <!-- Mes Colis -->
                    <li>
                        <div x-data="{ open: {{ request()->routeIs('client.packages*') ? 'true' : 'false' }} }">
                            <button type="button" 
                                    class="{{ request()->routeIs('client.packages*') 
                                        ? 'bg-white/20 text-white shadow-md' 
                                        : 'text-purple-200 hover:text-white hover:bg-white/10' }} 
                                        group w-full flex items-center gap-x-3 rounded-xl p-3 text-left text-sm leading-6 font-semibold transition-all duration-200"
                                    @click="open = !open">
                                <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                                <span class="flex-1">Mes Colis</span>
                                
                                @php
                                    $inProgressCount = auth()->user()->sentPackages()->inProgress()->count();
                                @endphp
                                @if($inProgressCount > 0)
                                    <span class="bg-purple-400 text-purple-900 text-xs font-medium px-2 py-1 rounded-full">
                                        {{ $inProgressCount }}
                                    </span>
                                @endif
                                
                                <svg class="h-5 w-5 shrink-0 transition-transform duration-200" 
                                     :class="{ 'rotate-90': open }" 
                                     viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            
                            <ul x-show="open" 
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-1"
                                class="mt-2 px-3 space-y-1">
                                <li>
                                    <a href="{{ route('client.packages.index') }}" 
                                       class="{{ request()->routeIs('client.packages.index') && !request()->has('status') 
                                           ? 'bg-white/10 text-white' 
                                           : 'text-purple-300 hover:text-white hover:bg-white/5' }} 
                                           block rounded-lg py-2 pl-8 pr-3 text-sm leading-6 transition-colors duration-200">
                                        Tous les colis
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('client.packages.index', ['status' => 'in_progress']) }}" 
                                       class="{{ request()->get('status') === 'in_progress' 
                                           ? 'bg-white/10 text-white' 
                                           : 'text-purple-300 hover:text-white hover:bg-white/5' }} 
                                           block rounded-lg py-2 pl-8 pr-3 text-sm leading-6 transition-colors duration-200">
                                        En cours
                                        @if($inProgressCount > 0)
                                            <span class="ml-2 bg-purple-400 text-purple-900 text-xs px-1.5 py-0.5 rounded-full">
                                                {{ $inProgressCount }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('client.packages.index', ['status' => 'delivered']) }}" 
                                       class="{{ request()->get('status') === 'delivered' 
                                           ? 'bg-white/10 text-white' 
                                           : 'text-purple-300 hover:text-white hover:bg-white/5' }} 
                                           block rounded-lg py-2 pl-8 pr-3 text-sm leading-6 transition-colors duration-200">
                                        Livrés
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('client.packages.index', ['status' => 'returned']) }}" 
                                       class="{{ request()->get('status') === 'returned' 
                                           ? 'bg-white/10 text-white' 
                                           : 'text-purple-300 hover:text-white hover:bg-white/5' }} 
                                           block rounded-lg py-2 pl-8 pr-3 text-sm leading-6 transition-colors duration-200">
                                        Retournés
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Nouveau Colis -->
                    <li>
                        <a href="{{ route('client.packages.create') }}" 
                           class="{{ request()->routeIs('client.packages.create') 
                               ? 'bg-white/20 text-white shadow-md' 
                               : 'text-purple-200 hover:text-white hover:bg-white/10' }} 
                               group flex gap-x-3 rounded-xl p-3 text-sm leading-6 font-semibold transition-all duration-200">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Nouveau Colis
                        </a>
                    </li>

                    <!-- Wallet -->
                    <li>
                        <div x-data="{ open: {{ request()->routeIs('client.wallet*') ? 'true' : 'false' }} }">
                            <button type="button" 
                                    class="{{ request()->routeIs('client.wallet*') 
                                        ? 'bg-white/20 text-white shadow-md' 
                                        : 'text-purple-200 hover:text-white hover:bg-white/10' }} 
                                        group w-full flex items-center gap-x-3 rounded-xl p-3 text-left text-sm leading-6 font-semibold transition-all duration-200"
                                    @click="open = !open">
                                <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3" />
                                </svg>
                                <span class="flex-1">Wallet</span>
                                
                                <div class="text-xs font-medium bg-white/20 text-purple-100 px-2 py-1 rounded-md" id="wallet-balance">
                                    {{ number_format(auth()->user()->wallet_balance ?? 0, 3) }} DT
                                </div>
                                
                                <svg class="h-5 w-5 shrink-0 transition-transform duration-200" 
                                     :class="{ 'rotate-90': open }" 
                                     viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            
                            <ul x-show="open" 
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-1"
                                class="mt-2 px-3 space-y-1">
                                <li>
                                    <a href="{{ route('client.wallet.index') }}" 
                                       class="{{ request()->routeIs('client.wallet.index') && !request()->routeIs('client.wallet.*') 
                                           ? 'bg-white/10 text-white' 
                                           : 'text-purple-300 hover:text-white hover:bg-white/5' }} 
                                           block rounded-lg py-2 pl-8 pr-3 text-sm leading-6 transition-colors duration-200">
                                        Historique
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('client.wallet.withdrawal') }}" 
                                       class="{{ request()->routeIs('client.wallet.withdrawal') 
                                           ? 'bg-white/10 text-white' 
                                           : 'text-purple-300 hover:text-white hover:bg-white/5' }} 
                                           block rounded-lg py-2 pl-8 pr-3 text-sm leading-6 transition-colors duration-200">
                                        Demander retrait
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('client.withdrawals') }}" 
                                       class="{{ request()->routeIs('client.withdrawals') 
                                           ? 'bg-white/10 text-white' 
                                           : 'text-purple-300 hover:text-white hover:bg-white/5' }} 
                                           block rounded-lg py-2 pl-8 pr-3 text-sm leading-6 transition-colors duration-200">
                                        Mes demandes
                                        @php
                                            $pendingWithdrawals = auth()->user()->withdrawalRequests()->where('status', 'PENDING')->count();
                                        @endphp
                                        @if($pendingWithdrawals > 0)
                                            <span class="ml-2 bg-orange-400 text-orange-900 text-xs px-1.5 py-0.5 rounded-full">
                                                {{ $pendingWithdrawals }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Réclamations -->
                    <li>
                        <a href="{{ route('client.complaints.index') }}" 
                           class="{{ request()->routeIs('client.complaints*') 
                               ? 'bg-white/20 text-white shadow-md' 
                               : 'text-purple-200 hover:text-white hover:bg-white/10' }} 
                               group flex gap-x-3 rounded-xl p-3 text-sm leading-6 font-semibold transition-all duration-200">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                            <span class="flex-1">Réclamations</span>
                            @php
                                $pendingComplaints = auth()->user()->complaints()->where('status', 'PENDING')->count();
                            @endphp
                            @if($pendingComplaints > 0)
                                <span class="bg-red-400 text-red-900 text-xs font-medium px-2 py-1 rounded-full">
                                    {{ $pendingComplaints }}
                                </span>
                            @endif
                        </a>
                    </li>

                </ul>
            </li>

            <!-- Section Profil (bas de la sidebar) -->
            <li class="mt-auto">
                <!-- Séparateur -->
                <div class="border-t border-white/20 pt-6 mb-4">
                    
                    <!-- Carte Profil Client -->
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20">
                        <div class="flex items-center">
                            <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
                                <span class="text-lg font-bold text-white">
                                    {{ substr(auth()->user()->name, 0, 2) }}
                                </span>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-semibold text-white">
                                    {{ Str::limit(auth()->user()->name, 18) }}
                                </p>
                                @if(auth()->user()->clientProfile)
                                    <p class="text-xs text-purple-200">
                                        {{ Str::limit(auth()->user()->clientProfile->shop_name, 20) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Statut du compte -->
                        <div class="mt-3 flex items-center justify-between">
                            @if(auth()->user()->account_status === 'ACTIVE')
                                <span class="inline-flex items-center rounded-lg bg-green-500/20 px-2 py-1 text-xs font-medium text-green-300 ring-1 ring-green-500/30">
                                    <svg class="mr-1 h-2 w-2" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    Compte actif
                                </span>
                            @elseif(auth()->user()->account_status === 'PENDING')
                                <span class="inline-flex items-center rounded-lg bg-yellow-500/20 px-2 py-1 text-xs font-medium text-yellow-300 ring-1 ring-yellow-500/30">
                                    <svg class="mr-1 h-2 w-2" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    En validation
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-lg bg-red-500/20 px-2 py-1 text-xs font-medium text-red-300 ring-1 ring-red-500/30">
                                    <svg class="mr-1 h-2 w-2" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    Suspendu
                                </span>
                            @endif
                        </div>

                        <!-- Solde Wallet détaillé -->
                        <div class="mt-4 space-y-2">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-purple-200">Solde disponible:</span>
                                <span class="font-semibold text-white">{{ number_format(auth()->user()->wallet_balance ?? 0, 3) }} DT</span>
                            </div>
                            @if((auth()->user()->wallet_pending ?? 0) > 0)
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-purple-300">En attente:</span>
                                    <span class="text-orange-300">{{ number_format(auth()->user()->wallet_pending, 3) }} DT</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </nav>
</div>