<button
    {{ $attributes->merge([
        'type' => 'button',
        'class' => 'rounded-full border p-2 transition-colors duration-300 flex items-center gap-4',
    ]) }}
    x-bind:class="{
        'border-emerald-400/40 bg-emerald-400/15 text-emerald-100 hover:bg-emerald-400/25': collapsed,
        'border-white/60 bg-white/5 text-slate-100 hover:bg-white/10': !collapsed,
    }"
    :title="collapsed ? 'Expandir menú' : 'Contraer menú'"
>


    <span
        class="transition-transform duration-300"
        :class="collapsed ? 'rotate-180' : ''"
        aria-hidden="true"
    >
        <x-iconos.contraer-flechas />
    </span>
  <span class="transition-transform duration-300"
        :class="collapsed ? 'hidden' : ''">
  Contraer menú
</span>
</button>
