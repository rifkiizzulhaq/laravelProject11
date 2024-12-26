@props(['type' => 'success'])

@php
$classes = match ($type) {
    'success' => 'bg-green-500',
    'error' => 'bg-red-500',
    'warning' => 'bg-yellow-500',
    default => 'bg-green-500'
};
@endphp

<div id="toast-notification" 
    class="fixed top-4 right-4 flex items-center w-full max-w-xs p-4 mb-4 text-white {{ $classes }} rounded-lg shadow transform translate-x-full transition-transform duration-300 ease-in-out"
    role="alert">
    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg bg-white/25">
        @if($type === 'success')
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
            </svg>
        @elseif($type === 'error')
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        @endif
    </div>
    <div class="ml-3 text-sm font-normal" id="toast-message"></div>
    <button type="button" class="ml-auto -mx-1.5 -my-1.5 text-white hover:text-gray-100 rounded-lg p-1.5 inline-flex h-8 w-8" onclick="hideToast()">
        <span class="sr-only">Close</span>
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
    </button>
</div>

<script>
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast-notification');
    const messageEl = document.getElementById('toast-message');
    
    // Update message and type
    messageEl.textContent = message;
    toast.className = toast.className.replace(/bg-\w+-500/g, `bg-${type}-500`);
    
    // Show toast
    toast.classList.remove('translate-x-full');
    
    // Auto hide after 3 seconds
    setTimeout(() => {
        hideToast();
    }, 3000);
}

function hideToast() {
    const toast = document.getElementById('toast-notification');
    toast.classList.add('translate-x-full');
}
</script> 