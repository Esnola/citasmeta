@props([
    'texto' => null,
    'estado' => null,
    'variant' => 'emerald',
    'locked' => false,
])

@php
  $checkedClasses = match ($variant) {
      'sky' => 'peer-checked:bg-sky-400 peer-focus-visible:ring-sky-300',
      'yellow' => 'peer-checked:bg-yellow-400 peer-focus-visible:ring-yellow-300',
      'rose' => 'peer-checked:bg-rose-400 peer-focus-visible:ring-rose-300',
      default => 'peer-checked:bg-emerald-400 peer-focus-visible:ring-emerald-300',
  };

  $isLocked = (bool) $locked;
@endphp

<label @class([
    'inline-flex items-center gap-2 rounded-2xl border px-4 py-3 transition-colors',
    'cursor-not-allowed border-white/5 bg-slate-950/25 opacity-60' => $isLocked,
    'cursor-pointer border-white/10 bg-slate-950/40 hover:border-emerald-400/20 hover:bg-emerald-400/10' => ! $isLocked,
])>
  <input {{ $attributes->class(['peer sr-only'])->merge(['type' => 'checkbox', 'disabled' => $isLocked]) }}>
  <span class="h-5 w-9 rounded-full bg-slate-700 transition after:block after:h-4 after:w-4 after:translate-x-0.5 after:translate-y-0.5 after:rounded-full after:bg-white after:transition peer-checked:after:translate-x-4 peer-focus-visible:ring-2 peer-disabled:bg-slate-800 peer-disabled:after:bg-slate-400 {{ $checkedClasses }}"></span>
  @if ($texto || trim($slot->toHtml()) !== '')
    <span class="text-sm {{ $isLocked ? 'text-slate-500' : 'text-slate-200' }}">{{ $texto ?? $slot }}</span>
  @endif
</label>
