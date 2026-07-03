@php
    $path = match ($icono) {
        'plus' => '<path d="M7 3v8M3 7h8" stroke-linecap="round" stroke-linejoin="round" />',
        'edit' => '<path d="M8.5 3.5 10.5 5.5M3.5 10.5l1-3.5 5-5a1.4 1.4 0 0 1 2 2l-5 5-3.5 1Z" stroke-linecap="round" stroke-linejoin="round" />',
        'delete' => '<path d="M3 4h8M5 4V3h4v1M10.5 4l-.5 7H4L3.5 4M6 6.5v3M8 6.5v3" stroke-linecap="round" stroke-linejoin="round" />',
        'back' => '<path d="M6 3 2 7l4 4M2.5 7H12" stroke-linecap="round" stroke-linejoin="round" />',
        'check' => '<path d="M3 7.5 5.6 10 11 4" stroke-linecap="round" stroke-linejoin="round" />',
        'eye' => '<path d="M1.8 7s1.9-3.5 5.2-3.5S12.2 7 12.2 7 10.3 10.5 7 10.5 1.8 7 1.8 7Z" stroke-linecap="round" stroke-linejoin="round" /><path d="M7 5.7a1.3 1.3 0 1 1 0 2.6 1.3 1.3 0 0 1 0-2.6Z" stroke-linecap="round" stroke-linejoin="round" />',
        'calendar' => '<svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><path d="M7 11h2v2H7zm0 4h2v2H7zm4-4h2v2h-2zm0 4h2v2h-2zm4-4h2v2h-2zm0 4h2v2h-2z"></path><path d="M5 22h14c1.103 0 2-.897 2-2V6c0-1.103-.897-2-2-2h-2V2h-2v2H9V2H7v2H5c-1.103 0-2 .897-2 2v14c0 1.103.897 2 2 2zM19 8l.001 12H5V8h14z"></path></svg>',
        default => '<path d="M4 7h6" stroke-linecap="round" stroke-linejoin="round" />',
    };
@endphp

{!! $path !!}
