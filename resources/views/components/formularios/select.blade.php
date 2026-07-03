<select
    {{ $attributes->class([
        'w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-white shadow-inner shadow-slate-950/20 transition-colors',
        'focus:border-sky-300/50 focus:bg-slate-950/70 focus:outline-none focus:ring-2 focus:ring-sky-300/20',
        'disabled:cursor-not-allowed disabled:border-white/5 disabled:bg-slate-900/40 disabled:text-slate-500',
    ]) }}
>
    {{ $slot }}
</select>
