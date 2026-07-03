@props([
    'type' => 'text',
])

<input
    {{ $attributes
        ->class([
            'w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-white shadow-inner shadow-slate-950/20 transition-colors placeholder:text-slate-500',
            'focus:border-emerald-300/50 focus:bg-slate-950/70 focus:outline-none focus:ring-2 focus:ring-emerald-300/20',
            'disabled:cursor-not-allowed disabled:border-white/5 disabled:bg-slate-900/40 disabled:text-slate-500',
            'file:mr-4 file:rounded-full file:border-0 file:bg-emerald-400/10 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-emerald-200 hover:file:bg-emerald-400/15',
        ])
        ->merge(['type' => $type]) }}
>
