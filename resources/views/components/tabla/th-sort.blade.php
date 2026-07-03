@props([
    'sortBy',
    'sortDirection',
    'currentSort',
])

@php
  $isActive = $currentSort === $sortBy;
  $rumbo = $isActive ? $sortDirection : 'asc';

  $iconos = [
      'cliente' => ['asc' => 'iconos.deAZ',    'desc' => 'iconos.deZA'],
      'fecha'   => ['asc' => 'iconos.num-Asc', 'desc' => 'iconos.num-Desc'],
  ];

  $icono = $iconos[$sortBy][$rumbo]; //phpStorm marca error pero está bien
@endphp

<th class="px-4 py-3 {{ $sortBy === 'fecha' ? 'text-center' : '' }}">
  <button type="button"
          class="inline-flex cursor-pointer items-center gap-1 font-semibold text-slate-200 hover:text-white"
          wire:click="sortByColumn('{{ $sortBy }}')"
          title="Ordenar por {{ $sortBy }}"
          aria-label="Ordenar por {{ $sortBy }}">
    {{ ucfirst($sortBy) }}
    <span class="text-xs text-slate-400">
            <x-dynamic-component :component="$icono" />
        </span>
  </button>
</th>
