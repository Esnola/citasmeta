@props([
    'icono' => 'abrir',
    'variant' => 'emerald',
    'titulo' => null,
])

@php
  $classes = match ($variant) {
      'yellow' => 'border-yellow-400/20 bg-yellow-400/10 text-yellow-300 hover:bg-yellow-400/15',
      'rose' => 'border-rose-400/20 bg-rose-400/10 text-rose-300 hover:bg-rose-400/15',
      default => 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300 hover:bg-emerald-400/15',
  };

  $stroke = match ($variant) {
      'yellow' => 'stroke-yellow-300',
      'rose' => 'stroke-rose-300',
      default => 'stroke-emerald-300',
  };
  $buttonAttributes = filled($titulo)
      ? ['title' => $titulo, 'aria-label' => $titulo]
      : [];
@endphp

<button
        {{ $attributes->merge($buttonAttributes + [
            'type' => 'button',
            'class' => 'group inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium uppercase tracking-[0.25em] transition-colors '.$classes,
        ]) }}
>

  @if ($icono === 'abrir')
    <x-iconos.expand/>
  @elseif ($icono === 'cerrar')
    <x-iconos.contraer/>
  @elseif ($icono === 'restablecer')
    <x-iconos.restablecer clase="size-4"/>
  @else
    <x-iconos.ojo/>
  @endif

  {{ $slot }}
</button>
