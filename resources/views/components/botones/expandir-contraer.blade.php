@props(['abierto' => 'false'])

<button
        {{ $attributes->merge([
            'type' => 'button',
            'class' => 'group inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium uppercase tracking-[0.25em] transition-colors',
        ]) }}
        x-bind:aria-expanded="({{ $abierto }}).toString()"
        x-bind:class="{{ $abierto }}
        ? 'border-yellow-400/20 bg-yellow-400/10 text-yellow-300 hover:bg-yellow-400/15'
        : 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300 hover:bg-emerald-400/15'"
>
  <span x-show="{{ $abierto }}">
        <x-iconos.contraer/>
    </span>

  <span x-show="! ({{ $abierto }})">
        <x-iconos.expand/>
    </span>

  <span x-text="{{ $abierto }} ? 'Contraer' : 'Expandir'"></span>
</button>
