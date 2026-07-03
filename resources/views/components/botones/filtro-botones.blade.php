@props([
    'value',
    'icon',
    'text',
])

<flux:radio
        value="{{ $value }}"
        class="cursor-pointer bg-white/5 hover:bg-emerald-50/60 hover:text-white/60 transition-all duration-300 data-checked:bg-emerald-200/30! data-checked:text-emerald-200!"
>
  <x-dynamic-component :component="'iconos.' . $icon"/>
  {{ $text }}
</flux:radio>
