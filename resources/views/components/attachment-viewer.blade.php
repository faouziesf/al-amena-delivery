@props(['attachments', 'size' => 'md'])

@php
$sizeClasses = [
    'sm' => 'max-w-xs',
    'md' => 'max-w-sm',
    'lg' => 'max-w-lg'
];
@endphp

@if(!empty($attachments))
<div class="mt-3 space-y-2">
    <p class="text-xs font-medium text-slate-600 mb-2">📎 Pièces jointes :</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
        @foreach($attachments as $attachment)
            <div class="attachment-item border border-slate-200 rounded-lg overflow-hidden hover:border-slate-300 transition-colors">
                @if($attachment['is_image'])
                    <!-- Image avec prévisualisation -->
                    <div class="cursor-pointer" onclick="openImageModal('{{ $attachment['url'] }}', '{{ $attachment['name'] }}')">
                        <div class="aspect-video bg-slate-100 flex items-center justify-center relative group">
                            <img src="{{ $attachment['url'] }}" alt="{{ $attachment['name'] }}"
                                 class="max-w-full max-h-full object-contain rounded-t-lg"
                                 loading="lazy"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                            <!-- Fallback si l'image ne charge pas -->
                            <div class="hidden absolute inset-0 bg-slate-100 flex items-center justify-center" style="display: none;">
                                <span class="text-4xl">{{ $attachment['icon'] }}</span>
                            </div>

                            <!-- Overlay au survol -->
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="p-2 bg-white">
                            <p class="text-sm font-medium text-slate-800 truncate">{{ $attachment['name'] }}</p>
                            <p class="text-xs text-slate-500">{{ number_format($attachment['size'] / 1024, 1) }} KB</p>
                        </div>
                    </div>
                @elseif($attachment['is_pdf'])
                    <!-- PDF avec action de téléchargement -->
                    <a href="{{ $attachment['url'] }}" target="_blank" download
                       class="flex items-center p-3 hover:bg-slate-50 transition-colors">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                            <span class="text-2xl">📄</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate">{{ $attachment['name'] }}</p>
                            <p class="text-xs text-slate-500">PDF • {{ number_format($attachment['size'] / 1024, 1) }} KB</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="window.open('{{ $attachment['url'] }}', '_blank')"
                                    class="p-1 text-slate-500 hover:text-blue-600 transition-colors"
                                    title="Ouvrir">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </button>
                            <button onclick="downloadFile('{{ $attachment['url'] }}', '{{ $attachment['name'] }}')"
                                    class="p-1 text-slate-500 hover:text-green-600 transition-colors"
                                    title="Télécharger">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </button>
                        </div>
                    </a>
                @else
                    <!-- Autres fichiers -->
                    <a href="{{ $attachment['url'] }}" target="_blank" download
                       class="flex items-center p-3 hover:bg-slate-50 transition-colors">
                        <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center mr-3">
                            <span class="text-2xl">{{ $attachment['icon'] }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate">{{ $attachment['name'] }}</p>
                            <p class="text-xs text-slate-500">
                                {{ strtoupper($attachment['file_type']) }} • {{ number_format($attachment['size'] / 1024, 1) }} KB
                            </p>
                        </div>
                        <div class="p-1">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </a>
                @endif
            </div>
        @endforeach
    </div>
</div>

<!-- Modal pour les images -->
<div id="imageModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80" onclick="closeImageModal()">
    <div class="max-w-4xl max-h-[90vh] p-4">
        <div class="bg-white rounded-lg overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 id="modalImageTitle" class="text-lg font-semibold text-slate-900"></h3>
                <button onclick="closeImageModal()" class="text-slate-500 hover:text-slate-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-4">
                <img id="modalImage" src="" alt="" class="max-w-full max-h-[70vh] mx-auto object-contain">
            </div>
            <div class="p-4 border-t bg-slate-50 flex justify-end space-x-3">
                <button onclick="downloadCurrentImage()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Télécharger
                </button>
                <button onclick="closeImageModal()" class="px-4 py-2 bg-slate-300 text-slate-700 rounded-lg hover:bg-slate-400 transition-colors">
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentImageUrl = '';
let currentImageName = '';

function openImageModal(url, name) {
    currentImageUrl = url;
    currentImageName = name;
    document.getElementById('modalImage').src = url;
    document.getElementById('modalImageTitle').textContent = name;
    document.getElementById('imageModal').classList.remove('hidden');
    document.getElementById('imageModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.getElementById('imageModal').classList.remove('flex');
    document.body.style.overflow = '';
}

function downloadCurrentImage() {
    downloadFile(currentImageUrl, currentImageName);
}

function downloadFile(url, filename) {
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.target = '_blank';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Fermer le modal avec la touche Echap
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    }
});
</script>
@endif