@props(['message', 'isOwn' => false])

@php
    $isFromClient = $message->isFromClient();
    $senderName = $isFromClient ? ($message->sender->first_name ?? 'Client') : 'Support';
    $avatar = $isFromClient ? strtoupper(substr($message->sender->first_name ?? 'C', 0, 1)) : 'S';
    $bgColor = $isFromClient ? 'bg-blue-600' : 'bg-emerald-600';
    $bubbleStyle = $isOwn ? 'ml-auto bg-blue-600 text-white' : 'mr-auto bg-white border border-slate-200 text-slate-800';
    $containerAlign = $isOwn ? 'justify-end' : 'justify-start';
@endphp

<div class="flex {{ $containerAlign }} mb-6 group">
    <div class="flex max-w-4xl {{ $isOwn ? 'flex-row-reverse' : 'flex-row' }} items-end space-x-3 {{ $isOwn ? 'space-x-reverse' : '' }}">
        <!-- Avatar -->
        <div class="flex-shrink-0">
            <div class="w-10 h-10 rounded-full {{ $bgColor }} flex items-center justify-center text-white font-semibold shadow-lg ring-2 ring-white">
                {{ $avatar }}
            </div>
        </div>

        <!-- Message Container -->
        <div class="flex flex-col {{ $isOwn ? 'items-end' : 'items-start' }} space-y-2">
            <!-- Sender Info -->
            <div class="flex items-center space-x-2 {{ $isOwn ? 'flex-row-reverse space-x-reverse' : '' }}">
                <span class="text-sm font-medium text-slate-700">{{ $senderName }}</span>
                <span class="text-xs text-slate-500">{{ $message->created_at->format('d/m/Y à H:i') }}</span>
            </div>

            <!-- Message Bubble -->
            <div class="relative">
                @if(!empty(trim($message->message)))
                    <div class="px-4 py-3 rounded-2xl {{ $bubbleStyle }} shadow-sm {{ $isOwn ? 'rounded-br-md' : 'rounded-bl-md' }} max-w-2xl">
                        <div class="text-sm leading-relaxed whitespace-pre-wrap">
                            {{ $message->message }}
                        </div>
                    </div>
                @endif

                <!-- Attachments -->
                @if($message->hasAttachments())
                    <div class="mt-2 {{ $isOwn ? 'ml-auto' : 'mr-auto' }} max-w-sm">
                        <x-attachment-viewer :attachments="$message->formatted_attachments" size="sm" class="bg-white border border-slate-200 rounded-xl shadow-sm" />
                    </div>
                @endif

                <!-- Empty message indicator -->
                @if(empty(trim($message->message)) && !$message->hasAttachments())
                    <div class="px-4 py-3 rounded-2xl bg-slate-100 border border-slate-200 shadow-sm {{ $isOwn ? 'rounded-br-md' : 'rounded-bl-md' }} max-w-2xl">
                        <div class="text-sm text-slate-500 italic">
                            Message sans contenu
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>