{{-- Action Icons for Mobile - No Dropdown --}}
<div class="flex items-center gap-1">
    {{-- View Details --}}
    <a href="{{ route('client.packages.show', $package) }}"
       class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg touch-active transition-colors"
       title="Voir détails">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
    </a>

    {{-- Track Package --}}
    <a href="{{ route('public.track.package', $package->package_code) }}" target="_blank"
       class="p-2 text-green-600 hover:bg-green-50 rounded-lg touch-active transition-colors"
       title="Suivre">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
    </a>

    {{-- Print --}}
    <a href="{{ route('client.packages.print', $package) }}" target="_blank"
       class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg touch-active transition-colors"
       title="Imprimer">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
    </a>

    @if($package->canBeDeleted())
        {{-- Edit --}}
        <a href="{{ route('client.packages.edit', $package) }}"
           class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg touch-active transition-colors"
           title="Modifier">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
        </a>

        {{-- Delete --}}
        <form action="{{ route('client.packages.destroy', $package) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit"
                    onclick="return confirm('Supprimer ce colis ?')"
                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg touch-active transition-colors"
                    title="Supprimer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </form>
    @endif

    @if(!in_array($package->status, ['PAID', 'DELIVERED_PAID']))
        {{-- Complaint --}}
        <a href="{{ route('client.complaints.create', $package) }}"
           class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg touch-active transition-colors"
           title="Réclamation">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </a>
    @endif
</div>
