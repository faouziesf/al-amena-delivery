@props(['message'])

<div class="mb-4">
    <!-- En-tête du message -->
    <div class="flex items-center gap-2 mb-2">
        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-medium
            {{ $message->isFromClient() ? 'bg-blue-500' : 'bg-green-500' }}">
            @if($message->isFromClient())
                {{ strtoupper(substr($message->sender->first_name ?? 'C', 0, 1)) }}
            @else
                S
            @endif
        </div>
        <div class="flex flex-col">
            <span class="text-sm font-medium text-slate-800">
                {{ $message->isFromClient() ? ($message->sender->first_name ?? 'Client') : 'Support' }}
            </span>
            <span class="text-xs text-slate-500">
                {{ $message->created_at->format('d/m/Y à H:i') }}
            </span>
        </div>
    </div>

    <!-- Contenu du message -->
    <div class="ml-10">
        @if(!empty(trim($message->message)))
            <div class="bg-white border border-slate-200 rounded-lg p-3 mb-2">
                <div class="text-slate-800 text-sm leading-relaxed">
                    {!! nl2br(e($message->message)) !!}
                </div>
            </div>
        @endif

        <!-- Pièces jointes -->
        @if($message->hasAttachments())
            <x-attachment-viewer :attachments="$message->formatted_attachments" size="sm" />
        @endif

        <!-- Si pas de message et pas de pièces jointes, afficher un placeholder -->
        @if(empty(trim($message->message)) && !$message->hasAttachments())
            <div class="bg-slate-100 border border-slate-200 rounded-lg p-3 mb-2">
                <div class="text-slate-500 text-sm italic">
                    Message vide
                </div>
            </div>
        @endif
    </div>
</div>