<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script>
    try {
      document.documentElement.classList.toggle('sidebar-collapsed', localStorage.getItem('sidebar-collapsed') === 'true');
    } catch {}
  </script>
  <title>{{ config('app.name', 'Laravel') }}</title>
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet"/>
  <link rel="icon" type="image/svg+xml" href="/favicon.svg"/>
  <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png"/>
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png"/>
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png"/>
  <link rel="shortcut icon" href="/favicon.ico"/>
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png"/>
  <link rel="manifest" href="/site.webmanifest"/>
  @fluxAppearance
  @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  @endif
  @livewireStyles
  <style>
      [x-cloak] {
          display: none !important;
      }

      .sidebar-shell {
          width: 18rem;
          background-color: rgb(15 23 42 / 0.7);
      }

      html.sidebar-collapsed .sidebar-shell,
      .sidebar-shell[data-collapsed="true"] {
          width: 5rem;
          background-color: rgb(2 6 23 / 0.85);
      }

      aside[data-collapsed="true"] .sidebar-text {
          display: none;
      }

      aside[data-collapsed="true"] a.sidebar-link {
          justify-content: center;
          padding-left: 0.5rem;
          padding-right: 0.5rem;
      }

      aside[data-collapsed="true"] .sidebar-logout {
          justify-content: center;
          padding-left: 0.5rem;
          padding-right: 0.5rem;
      }

      aside[data-collapsed="true"] .sidebar-admin-header {
          justify-content: center;
          padding-left: 0.5rem;
          padding-right: 0.5rem;
          letter-spacing: normal;
      }

      aside[data-collapsed="true"] .sidebar-logo-link {
          padding: 0.5rem;
      }

      aside[data-collapsed="true"] .sidebar-logo-text {
          display: none;
      }
  </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
@if (session('status'))
  <div
          x-data="{ show: false }"
          x-init="$nextTick(() => show = true); setTimeout(() => show = false, 3500)"
          x-show="show"
          x-transition:enter="transition transform ease-linear duration-500"
          x-transition:enter-start="translate-x-full"
          x-transition:enter-end="translate-x-0"
          x-transition:leave="transition transform ease-linear duration-500"
          x-transition:leave-start="translate-x-0"
          x-transition:leave-end="translate-x-full"
          style="display: none"
          class="fixed top-34 -right-4 z-100 flex items-center gap-4 rounded-2xl px-10 py-3 bg-green-400/70 text-sm font-medium text-green-50 inset-ring inset-ring-green-500/20 will-change-transform"
  >
    <x-iconos.ojo clase="size-8"/> {{ session('status') }}
  </div>
@endif

<div
        x-data="{ show: false, message: '', type: 'success' }"
        x-on:toast.window="message = $event.detail.message; type = $event.detail.type || 'success'; show = true; setTimeout(() => show = false, 3500)"
        x-show="show"
        x-transition:enter="transition transform ease-linear duration-500"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition transform ease-linear duration-500"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        style="display: none"
        class="fixed top-34 -right-4 z-100 flex items-center gap-4 rounded-2xl px-10 py-3 text-sm font-medium inset-ring will-change-transform"
        :class="type === 'error' ? 'bg-red-400/70 text-red-50 inset-ring-red-500/20' : 'bg-green-400/70 text-green-50 inset-ring-green-500/20'"
>
  <x-iconos.ojo clase="size-8"/> <span x-text="message"></span>
</div>

<div
        class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(16,185,129,0.18),transparent_35%),linear-gradient(180deg,#020617,#0f172a)]"></div>
<div x-data="{ mobileOpen: false }" class="relative mx-auto flex min-h-screen min-w-6/8">

  <button
          x-on:click="mobileOpen = !mobileOpen"
          class="fixed top-4 left-4 z-50 rounded-lg border border-white/20 bg-slate-900/80 p-2 backdrop-blur-xl xl:hidden"
          :class="mobileOpen ? 'text-white' : 'text-slate-300'"
  >
    <svg x-show="!mobileOpen" class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
    </svg>
    <svg x-show="mobileOpen" class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
    </svg>
  </button>

  <div
          x-show="mobileOpen"
          x-on:click="mobileOpen = false"
          x-transition:enter="transition-opacity duration-300"
          x-transition:enter-start="opacity-0"
          x-transition:enter-end="opacity-100"
          x-transition:leave="transition-opacity duration-200"
          x-transition:leave-start="opacity-100"
          x-transition:leave-end="opacity-0"
          class="fixed inset-0 z-30 bg-black/50 backdrop-blur-sm xl:hidden"
          style="display: none"
  ></div>

  <aside
          x-data="{
              collapsed: localStorage.getItem('sidebar-collapsed') === 'true',
              adminExpanded: ['true', 'open'].includes(localStorage.getItem('accordion:admin-accordion-panel') ?? 'true'),
          }"
          x-init="$el.dataset.collapsed = collapsed"
          :data-collapsed="collapsed"
          class="sidebar-shell sticky top-0 z-40 h-screen shrink-0 border-r border-white/10 px-4 py-5 shadow-[18px_0_60px_rgba(15,23,42,0.32)] backdrop-blur-xl transition-all duration-300 max-xl:fixed max-xl:inset-y-0 max-xl:left-0 max-xl:max-w-[80vw]"
          :class="{
              'max-xl:-translate-x-full': !mobileOpen,
              'max-xl:translate-x-0': mobileOpen,
              'hidden xl:block': !mobileOpen,
              'block': mobileOpen
          }"
  >
    <nav x-cloak class="flex h-full flex-col justify-between text-sm">


      <div class="grid gap-12 items-center jusitfy-center">
        <a x-show="!collapsed"
                href="{{ route('dashboard') }}"
                class="sidebar-logo-link group relative flex flex-col items-center gap-3 overflow-hidden rounded-3xl border border-emerald-400/20 bg-emerald-400/10 p-3 text-emerald-100 transition-all duration-500 hover:-translate-y-0.5 hover:border-emerald-300/50 hover:bg-emerald-400/15 hover:shadow-[0_0_35px_-5px_rgba(16,185,129,0.55)]">
          <span aria-hidden="true"
                class="pointer-events-none absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/25 to-transparent transition-transform duration-700 ease-out group-hover:translate-x-full"></span>
          <img src="/logo.png" alt="Logo"
               class="w-full transition-transform duration-500 ease-out group-hover:scale-105 group-hover:rotate-2 group-hover:drop-shadow-[0_0_12px_rgba(16,185,129,0.65)]">
          <span class="sidebar-logo-text block truncate text-base font-semibold transition-colors duration-300 group-hover:text-emerald-50">
            {{ config('app.name', 'Citas') }}
          </span>
        </a>

        <div x-show="collapsed">
          <flux:brand href="/" logo="/logo.png"/>
        </div>

        <x-botones.sidebar-toggle
                x-on:click="collapsed = !collapsed; localStorage.setItem('sidebar-collapsed', collapsed); document.documentElement.classList.toggle('sidebar-collapsed', collapsed)"
        />
        <div class="grid gap-2">

          <x-navegacion.aside-link route="dashboard" route-is="dashboard" color="sky" icono="dashboard"
                                   text="Dashboard" class="sidebar-link"/>
          <x-navegacion.aside-link route="agenda.index" route-is="agenda.*" color="indigo" icono="agenda"
                                   text="Agenda" class="sidebar-link"/>
          <x-navegacion.aside-link route="clients.list" route-is="clients.*" color="emerald" icono="customer"
                                   text="Clientes" class="sidebar-link"/>
          <x-navegacion.aside-link route="appointments.index" route-is="appointments.*" color="yellow"
                                   icono="calendar" icono-clase="size-5" text="Citas" class="sidebar-link"/>
        </div>


      </div>

      @if (auth()->check() && auth()->user()->is_admin)
        <div class="mb-12 grid gap-2">
          <div
                  role="button"
                  tabindex="0"
                  x-on:click="adminExpanded = !adminExpanded; localStorage.setItem('accordion:admin-accordion-panel', adminExpanded)"
                  x-on:keydown.enter.prevent="$el.click()"
                  x-on:keydown.space.prevent="$el.click()"
                  x-bind:aria-expanded="adminExpanded"
                  aria-controls="admin-accordion-panel"
                  class="sidebar-admin-header flex items-center gap-3 rounded-t-md border-b border-white/10 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.25em] text-slate-500 transition-colors duration-200 hover:bg-white/15 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300/40"
          >
            <x-iconos.admin-user/>
            <span class="sidebar-text">Administración</span>
            <x-iconos.up x-bind:class="adminExpanded && 'rotate-180'"
                         clase="sidebar-text ml-auto size-6 transition-transform duration-300 ease-in-out"/>
          </div>
          <div
                  id="admin-accordion-panel"
                  x-show="adminExpanded"
                  x-transition.opacity.duration.300ms
                  class="mt-2 mb-4 grid gap-2 overflow-hidden"
          >
            <x-navegacion.aside-link route="admin.users.create" route-is="admin.users.*"
                                     color="orange" text="Usuarios" icono="usuarios" class="sidebar-link"/>
            <x-navegacion.aside-link route="admin.login-history" route-is="admin.login-history" color="rose"
                                     text="Conexiones" icono="reloj-agujas" class="sidebar-link"/>
            <x-navegacion.aside-link route="admin.tools" route-is="admin.tools" color="violet"
                                     icono="excel" text="Import / Export" class="sidebar-link"/>
            <x-navegacion.aside-link route="settings.index" route-is="settings.*" color="cyan"
                                     text="Ajustes" icono="ajustes" class="sidebar-link"/>
          </div>

          <form class="border-t border-white/10 pt-4" method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="sidebar-logout group flex w-full items-center gap-3 rounded-full border border-rose-400/25 bg-rose-400/10 px-3 py-2 font-medium text-rose-200 transition-colors hover:bg-rose-400/15">
              <x-iconos.salir clase="size-8"/>
              <span class="sidebar-text flex items-center gap-3">
                                Salir
                            </span>
            </button>
          </form>
        </div>
      @endif
    </nav>
  </aside>
  <main class="px-4 py-5 lg:px-6 lg:py-6 w-full">
      @yield('content')
  </main>
</div>
@livewireScripts
@fluxScripts
</body>
</html>
