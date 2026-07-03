@php
    $hora = (int) now()->format('H');
    if ($hora >= 6 && $hora < 12) {
        $saludo = 'Buenos días';
    } elseif ($hora >= 12 && $hora < 20) {
        $saludo = 'Buenas tardes';
    } else {
        $saludo = 'Buenas noches';
    }
@endphp

<div class="space-y-8 py-2">
  {{-- Encabezado principal estilo Hero --}}
  <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-slate-900/40 p-8 md:p-10 backdrop-blur-xl">
    <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-emerald-500/10 blur-3xl"></div>
    <div class="absolute -left-10 -bottom-10 h-40 w-40 rounded-full bg-indigo-500/10 blur-3xl"></div>
    
    <div class="relative flex flex-col justify-between gap-6 md:flex-row md:items-center">
      <div class="space-y-2">
        <h1 class="text-3xl font-bold tracking-tight text-white md:text-4xl">
          {{ $saludo }}, <span class="bg-gradient-to-r from-emerald-400 to-indigo-400 bg-clip-text text-transparent">{{ auth()->user()->name ?? 'Doctor' }}</span>
        </h1>
        <p class="text-slate-400 max-w-xl text-base">
          Bienvenido de nuevo a tu panel de control. Aquí tienes un resumen del estado de tus citas y recordatorios para hoy.
        </p>
      </div>
      <div class="flex items-center gap-3 self-start rounded-2xl border border-white/10 bg-slate-950/60 px-5 py-3 text-sm text-slate-300 md:self-center">
        <x-iconos.calendar clase="size-5 text-emerald-400" />
        <span class="font-medium">{{ ucfirst(now()->translatedFormat('l, d \\d\\e F \\d\\e Y')) }}</span>
      </div>
    </div>
  </div>

  {{-- Grid de métricas --}}
  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
    {{-- Pendientes --}}
    <div class="group relative overflow-hidden rounded-2xl border border-amber-500/10 bg-slate-900/30 p-4 backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:border-amber-500/30 hover:bg-slate-900/60 hover:shadow-xl hover:shadow-amber-500/5">
      <div class="absolute top-0 right-0 h-16 w-16 rounded-bl-full bg-amber-500/5 transition-all duration-300 group-hover:bg-amber-500/10"></div>
      <div class="flex items-center justify-between">
        <div class="space-y-0.5">
          <p class="text-xs font-semibold tracking-wide uppercase text-amber-400/80">Pendientes</p>
          <p class="text-2xl font-extrabold text-white tracking-tight">{{ $pendingCount }}</p>
        </div>
        <div class="rounded-xl bg-amber-500/10 p-2.5 text-amber-300 transition-all duration-300 group-hover:bg-amber-500/20 group-hover:scale-110">
          <x-iconos.reloj-arena clase="size-5" />
        </div>
      </div>
      <div class="mt-3 flex items-center justify-between border-t border-white/5 pt-2.5">
        <span class="text-[10px] text-slate-400">Por enviar</span>
        <span class="inline-flex items-center text-[10px] font-medium text-amber-300 bg-amber-500/10 px-1.5 py-0.5 rounded-full">Espera</span>
      </div>
    </div>

    {{-- Enviados --}}
    <div class="group relative overflow-hidden rounded-2xl border border-emerald-500/10 bg-slate-900/30 p-4 backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:border-emerald-500/30 hover:bg-slate-900/60 hover:shadow-xl hover:shadow-emerald-500/5">
      <div class="absolute top-0 right-0 h-16 w-16 rounded-bl-full bg-emerald-500/5 transition-all duration-300 group-hover:bg-emerald-500/10"></div>
      <div class="flex items-center justify-between">
        <div class="space-y-0.5">
          <p class="text-xs font-semibold tracking-wide uppercase text-emerald-400/80">Enviados</p>
          <p class="text-2xl font-extrabold text-white tracking-tight">{{ $sentCount }}</p>
        </div>
        <div class="rounded-xl bg-emerald-500/10 p-2.5 text-emerald-300 transition-all duration-300 group-hover:bg-emerald-500/20 group-hover:scale-110">
          <x-iconos.whatsapp clase="size-5" />
        </div>
      </div>
      <div class="mt-3 flex items-center justify-between border-t border-white/5 pt-2.5">
        <span class="text-[10px] text-slate-400">Entregados</span>
        <span class="inline-flex items-center text-[10px] font-medium text-emerald-300 bg-emerald-500/10 px-1.5 py-0.5 rounded-full">Éxito</span>
      </div>
    </div>

    {{-- Fallidos --}}
    <div class="group relative overflow-hidden rounded-2xl border border-red-500/10 bg-slate-900/30 p-4 backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:border-red-500/30 hover:bg-slate-900/60 hover:shadow-xl hover:shadow-red-500/5">
      <div class="absolute top-0 right-0 h-16 w-16 rounded-bl-full bg-red-500/5 transition-all duration-300 group-hover:bg-red-500/10"></div>
      <div class="flex items-center justify-between">
        <div class="space-y-0.5">
          <p class="text-xs font-semibold tracking-wide uppercase text-red-400/80">Fallidos</p>
          <p class="text-2xl font-extrabold text-white tracking-tight">{{ $failedCount }}</p>
        </div>
        <div class="rounded-xl bg-red-500/10 p-2.5 text-red-300 transition-all duration-300 group-hover:bg-red-500/20 group-hover:scale-110">
          <x-iconos.alert clase="size-5" />
        </div>
      </div>
      <div class="mt-3 flex items-center justify-between border-t border-white/5 pt-2.5">
        <span class="text-[10px] text-slate-400">Atención</span>
        <span class="inline-flex items-center text-[10px] font-medium text-red-300 bg-red-500/10 px-1.5 py-0.5 rounded-full">Error</span>
      </div>
    </div>

    {{-- Canceladas --}}
    <div class="group relative overflow-hidden rounded-2xl border border-slate-500/10 bg-slate-900/30 p-4 backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:border-slate-500/30 hover:bg-slate-900/60 hover:shadow-xl hover:shadow-slate-500/5">
      <div class="absolute top-0 right-0 h-16 w-16 rounded-bl-full bg-slate-500/5 transition-all duration-300 group-hover:bg-slate-500/10"></div>
      <div class="flex items-center justify-between">
        <div class="space-y-0.5">
          <p class="text-xs font-semibold tracking-wide uppercase text-slate-400/80">Canceladas</p>
          <p class="text-2xl font-extrabold text-white tracking-tight">{{ $cancelados }}</p>
        </div>
        <div class="rounded-xl bg-slate-500/10 p-2.5 text-slate-300 transition-all duration-300 group-hover:bg-slate-500/20 group-hover:scale-110">
          <x-iconos.papelera clase="size-5" />
        </div>
      </div>
      <div class="mt-3 flex items-center justify-between border-t border-white/5 pt-2.5">
        <span class="text-[10px] text-slate-400">Inactivo</span>
        <span class="inline-flex items-center text-[10px] font-medium text-slate-300 bg-slate-500/10 px-1.5 py-0.5 rounded-full">Off</span>
      </div>
    </div>

    {{-- Caducados --}}
    <div class="group relative overflow-hidden rounded-2xl border border-orange-500/10 bg-slate-900/30 p-4 backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:border-orange-500/30 hover:bg-slate-900/60 hover:shadow-xl hover:shadow-orange-500/5">
      <div class="absolute top-0 right-0 h-16 w-16 rounded-bl-full bg-orange-500/5 transition-all duration-300 group-hover:bg-orange-500/10"></div>
      <div class="flex items-center justify-between">
        <div class="space-y-0.5">
          <p class="text-xs font-semibold tracking-wide uppercase text-orange-400/80">Caducados</p>
          <p class="text-2xl font-extrabold text-white tracking-tight">{{ $caducados }}</p>
        </div>
        <div class="rounded-xl bg-orange-500/10 p-2.5 text-orange-300 transition-all duration-300 group-hover:bg-orange-500/20 group-hover:scale-110">
          <x-iconos.calendario-pasado clase="size-5" />
        </div>
      </div>
      <div class="mt-3 flex items-center justify-between border-t border-white/5 pt-2.5">
        <span class="text-[10px] text-slate-400">Sin notificar</span>
        <span class="inline-flex items-center text-[10px] font-medium text-orange-300 bg-orange-500/10 px-1.5 py-0.5 rounded-full">Expira</span>
      </div>
    </div>

    {{-- Totales --}}
    <div class="group relative overflow-hidden rounded-2xl border border-indigo-500/10 bg-slate-900/30 p-4 backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:border-indigo-500/30 hover:bg-slate-900/60 hover:shadow-xl hover:shadow-indigo-500/5">
      <div class="absolute top-0 right-0 h-16 w-16 rounded-bl-full bg-indigo-500/5 transition-all duration-300 group-hover:bg-indigo-500/10"></div>
      <div class="flex items-center justify-between">
        <div class="space-y-0.5">
          <p class="text-xs font-semibold tracking-wide uppercase text-indigo-400/80">Totales</p>
          <p class="text-2xl font-extrabold text-white tracking-tight">{{ $totales }}</p>
        </div>
        <div class="rounded-xl bg-indigo-500/10 p-2.5 text-indigo-300 transition-all duration-300 group-hover:bg-indigo-500/20 group-hover:scale-110">
          <x-iconos.cita clase="size-5" />
        </div>
      </div>
      <div class="mt-3 flex items-center justify-between border-t border-white/5 pt-2.5">
        <span class="text-[10px] text-slate-400">Histórico</span>
        <span class="inline-flex items-center text-[10px] font-medium text-indigo-300 bg-indigo-500/10 px-1.5 py-0.5 rounded-full">Total</span>
      </div>
    </div>
  </div>

</div>
