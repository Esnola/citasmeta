@extends('layouts.guest')

@section('contentWidth', 'max-w-6xl')

@section('content')
  <div class="grid items-center gap-16 md:grid-cols-2 md:gap-20">
    {{-- Columna izquierda: Hero y CTA --}}
    <div class="space-y-10">
      <div class="space-y-4">
          <p class="inline-flex items-center gap-2 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-300">
            <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
            Recordatorios inteligentes vía WhatsApp
          </p>

          <h1 class="text-5xl font-bold leading-tight tracking-tight text-white md:text-6xl">
            Gestiona
            <span class="bg-gradient-to-r from-emerald-400 to-indigo-400 bg-clip-text text-transparent">las citas de tus clientes</span>
            con facilidad
          </h1>

          <p class="text-lg leading-relaxed text-slate-400 max-w-lg">
            Organiza pacientes, programa recordatorios automáticos por WhatsApp y reduce las ausencias.
            Todo desde un panel simple y potente.
          </p>
        </div>

      <div class="flex flex-wrap gap-4">
        <a href="{{ route('login') }}"
           class="inline-flex items-center gap-2.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-emerald-600 px-8 py-4 text-base font-bold text-white shadow-xl shadow-emerald-500/20 transition-all duration-300 hover:from-emerald-400 hover:to-emerald-500 hover:shadow-2xl hover:shadow-emerald-500/30 hover:-translate-y-0.5">
          <x-iconos.conectar clase="size-6"/>
          Acceder al panel
        </a>
        <a href="#features"
           class="inline-flex items-center gap-2.5 rounded-2xl border border-white/10 bg-white/5 px-8 py-4 text-base font-bold text-slate-300 transition-all duration-300 hover:border-white/20 hover:bg-white/10 hover:text-white">
          Saber más
        </a>
      </div>
    </div>

    {{-- Columna derecha: Logo y features --}}
    <div class="relative hidden md:block">
      <div class="absolute -inset-4 rounded-3xl bg-gradient-to-br from-emerald-500/10 to-indigo-500/10 blur-2xl"></div>
      <div class="relative space-y-6">
        <div class="rounded-2xl border border-white/10 bg-slate-900/60 p-6 backdrop-blur-xl">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-300">
                <x-iconos.whatsapp clase="size-5" />
              </div>
              <div>
                <p class="text-sm font-semibold text-slate-200">WhatsApp</p>
                <p class="text-xs text-slate-400">Recordatorios automáticos</p>
              </div>
            </div>
            <span class="rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-bold text-emerald-300">Activo</span>
          </div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-slate-900/60 p-6 backdrop-blur-xl">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/20 text-indigo-300">
                <x-iconos.customer clase="size-5" />
              </div>
              <div>
                <p class="text-sm font-semibold text-slate-200">Pacientes</p>
                <p class="text-xs text-slate-400">Importación desde Excel</p>
              </div>
            </div>
            <span class="rounded-full bg-indigo-500/20 px-3 py-1 text-xs font-bold text-indigo-300">Gestión</span>
          </div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-slate-900/60 p-6 backdrop-blur-xl">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/20 text-amber-300">
                <x-iconos.calendar clase="size-5" />
              </div>
              <div>
                <p class="text-sm font-semibold text-slate-200">Agenda</p>
                <p class="text-xs text-slate-400">Citas programadas y control</p>
              </div>
            </div>
            <span class="rounded-full bg-amber-500/20 px-3 py-1 text-xs font-bold text-amber-300">Visión general</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Separador sutil --}}
  <div class="my-24 border-t border-white/5"></div>

  {{-- Sección de características detalladas --}}
  <div id="features" class="space-y-16">
    <div class="mx-auto max-w-2xl text-center">
      <h2 class="text-3xl font-bold text-white md:text-4xl">
        Todo lo que necesitas para gestionar tu clínica
      </h2>
      <p class="mt-4 text-lg text-slate-400">
        Una herramienta completa para reducir las ausencias y mantener a tus pacientes informados.
      </p>
    </div>

    <div class="grid gap-6 md:grid-cols-3">
      <div class="group rounded-2xl border border-white/10 bg-slate-900/30 p-8 backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:border-emerald-500/30 hover:bg-slate-900/60 hover:shadow-xl hover:shadow-emerald-500/5">
        <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-300 transition-all duration-300 group-hover:bg-emerald-500/20 group-hover:scale-110">
          <x-iconos.whatsapp clase="size-7" />
        </div>
        <h3 class="text-xl font-bold text-white">Recordatorios WhatsApp</h3>
        <p class="mt-3 text-sm leading-relaxed text-slate-400">
          Programa envíos automáticos de WhatsApp para recordar a tus pacientes sus citas.
          Reduce las ausencias hasta un 80%.
        </p>
      </div>

      <div class="group rounded-2xl border border-white/10 bg-slate-900/30 p-8 backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:border-indigo-500/30 hover:bg-slate-900/60 hover:shadow-xl hover:shadow-indigo-500/5">
        <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-300 transition-all duration-300 group-hover:bg-indigo-500/20 group-hover:scale-110">
          <x-iconos.excel clase="size-7" />
        </div>
        <h3 class="text-xl font-bold text-white">Importación Excel</h3>
        <p class="mt-3 text-sm leading-relaxed text-slate-400">
          Importa tus pacientes y citas directamente desde Excel con un solo clic.
          Sin migraciones manuales ni procesos tediosos.
        </p>
      </div>

      <div class="group rounded-2xl border border-white/10 bg-slate-900/30 p-8 backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:border-amber-500/30 hover:bg-slate-900/60 hover:shadow-xl hover:shadow-amber-500/5">
        <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-300 transition-all duration-300 group-hover:bg-amber-500/20 group-hover:scale-110">
          <x-iconos.nueva-cita clase="size-7" />
        </div>
        <h3 class="text-xl font-bold text-white">Gestión de citas</h3>
        <p class="mt-3 text-sm leading-relaxed text-slate-400">
          Panel completo para crear, editar y gestionar citas con filtros avanzados
          y vista general de la agenda diaria.
        </p>
      </div>
    </div>
  </div>

  {{-- Separador sutil --}}
  <div class="my-24 border-t border-white/5"></div>

  {{-- Footer simple --}}
  <div class="flex flex-col items-center justify-between gap-4 pb-6 text-sm text-slate-500 md:flex-row">
    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
    <div class="flex items-center gap-6">
      <p class="text-xs">Laravel 13 · Livewire 4 · Flux UI · Tailwind CSS 4</p>
    </div>
  </div>
@endsection
