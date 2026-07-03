@props([
    'route' => null,
    'routeIs'=> null,
    'color' => 'sky',
    'text' => null,
    'icono'=> null,
    'iconoClase'=>null,
])

@php
  $palette = match ($color) {
      'emerald' => [
          'active' => 'border-emerald-400/25 bg-emerald-400/15 text-emerald-200',
          'inactive' => 'border-white/10 bg-white/5 text-slate-300 hover:border-emerald-400/20 hover:bg-emerald-400/10 hover:text-emerald-200',
          'dot' => 'bg-emerald-300',
      ],
      'yellow' => [
          'active' => 'border-yellow-400/25 bg-yellow-400/15 text-yellow-200',
          'inactive' => 'border-white/10 bg-white/5 text-slate-300 hover:border-yellow-400/20 hover:bg-yellow-400/10 hover:text-yellow-200',
          'dot' => 'bg-yellow-300',
      ],
      'violet' => [
          'active' => 'border-violet-400/25 bg-violet-400/15 text-violet-200',
          'inactive' => 'border-white/10 bg-white/5 text-slate-300 hover:border-violet-400/20 hover:bg-violet-400/10 hover:text-violet-200',
          'dot' => 'bg-violet-300',
      ],
      'orange' => [
          'active' => 'border-orange-400/25 bg-orange-400/15 text-orange-200',
          'inactive' => 'border-white/10 bg-white/5 text-slate-300 hover:border-orange-400/20 hover:bg-orange-400/10 hover:text-orange-200',
          'dot' => 'bg-orange-300',
      ],
      'rose' => [
          'active' => 'border-rose-400/25 bg-rose-400/15 text-rose-200',
          'inactive' => 'border-white/10 bg-white/5 text-slate-300 hover:border-rose-400/20 hover:bg-rose-400/10 hover:text-rose-200',
          'dot' => 'bg-rose-300',
      ],
      'cyan' => [
          'active' => 'border-cyan-400/25 bg-cyan-400/15 text-cyan-200',
          'inactive' => 'border-white/10 bg-white/5 text-slate-300 hover:border-cyan-400/20 hover:bg-cyan-400/10 hover:text-cyan-200',
          'dot' => 'bg-cyan-300',
      ],
      default => [
          'active' => 'border-sky-400/25 bg-sky-400/15 text-sky-200',
          'inactive' => 'border-white/10 bg-white/5 text-slate-300 hover:border-sky-400/20 hover:bg-sky-400/10 hover:text-sky-200',
          'dot' => 'bg-sky-300',
      ],
  };

  $active = request()->routeIs($routeIs);
@endphp

<a
        {{ $attributes->class([
            'group flex items-center gap-3 rounded-full border px-3 py-2 font-medium transition-colors',
            $palette['active'] => $active,
            $palette['inactive'] => ! $active,
        ])->merge(['href' => route($route) ]) }}
>
  @if($icono)
    <x-dynamic-component :component="'iconos.' . $icono" :clase="$iconoClase"/>
  @else
    <span class=" size-2 rounded-full {{ $palette['dot'] }}"></span>
  @endif

  <span class="sidebar-text">{{ $text ?? $slot }}</span>
</a>
