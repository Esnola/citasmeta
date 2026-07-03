@props(['titulo'])

<template x-teleport="body">
  <div role="dialog" aria-modal="true"
       {{ $attributes->class(['fixed inset-0 z-[9999] grid place-items-center px-4 py-6']) }}>
    <div class="absolute inset-0 bg-slate-950/80" aria-hidden="true"></div>
    <div class="relative z-10 w-full max-w-md rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
      <h3 class="text-lg font-semibold">{{ $titulo }}</h3>
      {{ $slot }}
      <div class="mt-6 flex flex-wrap justify-end gap-2">
        {{ $actions }}
      </div>
    </div>
  </div>
</template>
