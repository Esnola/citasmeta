@php use Illuminate\Support\Str; @endphp
@extends('layouts.app')

@section('content')
  @php
    $driver = config('whatsapp.driver');
    $cloudApi = config('whatsapp.cloud_api', []);
    $phoneNumberId = (string) ($cloudApi['phone_number_id'] ?? '');
    $accessToken = (string) ($cloudApi['access_token'] ?? '');
    $hasCredentials = filled($phoneNumberId) && filled($accessToken);
  @endphp

  <div x-data="settingsBoard()"
          x-init="init()"
          class="grid gap-4" >

    <div class="rounded-3xl border border-white/10 bg-white/5 p-8 backdrop-blur">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h2 class="text-2xl font-semibold">Ajustes</h2>
          <p class="mt-3 text-sm text-slate-300">
            Reordena las secciones arrastrando su cabecera y contrae o expande cada bloque cuando quieras.
          </p>
        </div>
        <div class="flex flex-wrap gap-2">
          <x-botones.accion-ajustes icono="abrir" variant="emerald" x-on:click="expandAll">
            Expandir todos
          </x-botones.accion-ajustes>
          <x-botones.accion-ajustes icono="cerrar" variant="yellow" x-on:click="collapseAll">
            Contraer todos
          </x-botones.accion-ajustes>
          <x-botones.accion-ajustes icono="restablecer" variant="rose" x-on:click="resetLayout">
            Restablecer todo
          </x-botones.accion-ajustes>
        </div>
      </div>
    </div>

    <div x-ref="board"  class="grid gap-4" aria-label="Secciones de ajustes" >
      <section
              data-settings-section="overview"
              data-default-open="true"
              class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur"
              x-bind:class="sectionStateClasses('overview')"
              x-on:dragenter.prevent="setDropTarget('overview', $event)"
              x-on:dragover.prevent
              x-on:drop.prevent="drop('overview', $event)"
              x-show="isVisible('overview')"
      >
        <div x-show="showDropHint('overview', 'before')" x-cloak
             class="mb-4 h-1 rounded-full bg-emerald-400/80 shadow-[0_0_24px_rgba(52,211,153,0.45)]"></div>
        <div class="flex items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <x-botones.arrastrar-seccion seccion="overview"/>
            <div>
              <h3 class="text-lg font-semibold">Resumen</h3>
              <p class="text-sm text-slate-300">Estado general de WhatsApp, plantillas y conexión.</p>
            </div>
          </div>
          <x-botones.expandir-contraer abierto="isOpen('overview')" x-on:click="toggle('overview')"/>
        </div>

        <div x-show="dragging === 'overview'" x-cloak
             class="mt-4 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-xs uppercase tracking-[0.28em] text-emerald-100">
          Soltando esta tarjeta aquí
        </div>

        <div x-show="isOpen('overview')" x-cloak class="mt-6">
          <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-2xl border border-white/10 bg-slate-900/60 p-4">
              <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Driver</p>
              <p class="mt-2 font-medium">{{ $driver }}</p>
              <p class="mt-1 text-sm text-slate-300">
                @if ($driver === 'cloud_api')
                  Meta WhatsApp Cloud API
                @else
                  Modo local / registro
                @endif
              </p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-slate-900/60 p-4">
              <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Plantilla por defecto</p>
              <p class="mt-2 font-medium">{{ config('whatsapp.default_template') }}</p>
              <p class="mt-1 text-sm text-slate-300">{{ config('whatsapp.default_message') ?? config('whatsapp.templates.' . config('whatsapp.default_template') . '.message') }}</p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-slate-900/60 p-4">
              <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Cloud API (Meta)</p>
              <p class="mt-2 font-medium">{{ $hasCredentials ? 'Credenciales listas' : 'Credenciales pendientes' }}</p>
              <p class="mt-1 text-sm text-slate-300">
                {{ $phoneNumberId ? 'Phone Number ID configurado' : 'Falta Phone Number ID' }}
              </p>
            </div>
          </div>
        </div>
        <div x-show="showDropHint('overview', 'after')" x-cloak
             class="mt-4 h-1 rounded-full bg-emerald-400/80 shadow-[0_0_24px_rgba(52,211,153,0.45)]"></div>
      </section>

      <section
              data-settings-section="cloud-api-setup"
              data-default-open="false"
              class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur"
              x-bind:class="sectionStateClasses('cloud-api-setup')"
              x-on:dragenter.prevent="setDropTarget('cloud-api-setup', $event)"
              x-on:dragover.prevent
              x-on:drop.prevent="drop('cloud-api-setup', $event)"
      >
        <div x-show="showDropHint('cloud-api-setup', 'before')" x-cloak
             class="mb-4 h-1 rounded-full bg-emerald-400/80 shadow-[0_0_24px_rgba(52,211,153,0.45)]"></div>
        <div class="flex items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <x-botones.arrastrar-seccion seccion="cloud-api-setup"/>
            <div>
              <h3 class="text-lg font-semibold">Cloud API (Meta)</h3>
              <p class="text-sm text-slate-300">Guía rápida para configurar WhatsApp Cloud API.</p>
            </div>
          </div>
          <x-botones.expandir-contraer abierto="isOpen('cloud-api-setup')" x-on:click="toggle('cloud-api-setup')"/>
        </div>

        <div x-show="dragging === 'cloud-api-setup'" x-cloak
             class="mt-4 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-xs uppercase tracking-[0.28em] text-emerald-100">
          Suelta para colocar esta sección
        </div>

        <div x-show="isOpen('cloud-api-setup')" x-cloak class="mt-6">
          <p class="text-sm text-slate-300">
            Para usar WhatsApp Cloud API de Meta necesitas una cuenta de desarrollador y un número de teléfono verificado.
          </p>
          <div class="mt-4 space-y-3 text-sm text-slate-200">
            <div class="rounded-2xl border border-white/10 bg-slate-900/60 p-4">
              <p class="font-medium">1. Configura tu aplicación</p>
              <p class="mt-1 text-slate-300">Crea una app en developers.facebook.com y agrega el producto WhatsApp.</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-slate-900/60 p-4">
              <p class="font-medium">2. Obtén las credenciales</p>
              <p class="mt-1 text-slate-300">Necesitas el Phone Number ID y un Access Token de larga duración.</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-slate-900/60 p-4">
              <p class="font-medium">3. Configura el .env</p>
              <p class="mt-1 text-slate-300">Define `WHATSAPP_DRIVER=cloud_api`, `WHATSAPP_CLOUD_API_PHONE_NUMBER_ID` y `WHATSAPP_CLOUD_API_ACCESS_TOKEN`.</p>
            </div>
          </div>
        </div>
        <div x-show="showDropHint('cloud-api-setup', 'after')" x-cloak
             class="mt-4 h-1 rounded-full bg-emerald-400/80 shadow-[0_0_24px_rgba(52,211,153,0.45)]"></div>
      </section>

      <section
              data-settings-section="status"
              data-default-open="false"
              class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur"
              x-bind:class="sectionStateClasses('status')"
              x-on:dragenter.prevent="setDropTarget('status', $event)"
              x-on:dragover.prevent
              x-on:drop.prevent="drop('status', $event)" >

        <div x-show="showDropHint('status', 'before')" x-cloak
             class="mb-4 h-1 rounded-full bg-emerald-400/80 shadow-[0_0_24px_rgba(52,211,153,0.45)]"></div>
        <div class="flex items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <x-botones.arrastrar-seccion seccion="status"/>
            <div>
              <h3 class="text-lg font-semibold">Estado actual</h3>
              <p class="text-sm text-slate-300">Credenciales y configuración de Cloud API.</p>
            </div>
          </div>

          <x-botones.expandir-contraer abierto="isOpen('status')" x-on:click="toggle('status')"/>
        </div>

        <div x-show="dragging === 'status'" x-cloak
             class="mt-4 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-xs uppercase tracking-[0.28em] text-emerald-100">
          Arrastre activo
        </div>

        <div x-show="isOpen('status')" x-cloak class="mt-6 grid gap-4">
          <div class="rounded-2xl border border-white/10 bg-slate-900/60 p-4">
            <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Phone Number ID</p>
            <p class="mt-2 font-medium">
              {{ $phoneNumberId ? Str::mask($phoneNumberId, '*', 4) : 'No configurado' }}
            </p>
          </div>
          <div class="rounded-2xl border border-white/10 bg-slate-900/60 p-4">
            <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Access Token</p>
            <p class="mt-2 font-medium">{{ $accessToken ? 'Configurado' : 'No configurado' }}</p>
            <p class="mt-1 text-sm text-slate-300">Token de larga duración para la API de WhatsApp.</p>
          </div>
        </div>
        <div x-show="showDropHint('status', 'after')" x-cloak
             class="mt-4 h-1 rounded-full bg-emerald-400/80 shadow-[0_0_24px_rgba(52,211,153,0.45)]"></div>
      </section>

      <section data-settings-section="connection"
               data-default-open="true"
               class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur"
               x-bind:class="sectionStateClasses('connection')"
               x-on:dragenter.prevent="setDropTarget('connection', $event)"
               x-on:dragover.prevent
               x-on:drop.prevent="drop('connection', $event)"
      >
        <div x-show="showDropHint('connection', 'before')" x-cloak
             class="mb-4 h-1 rounded-full bg-emerald-400/80 shadow-[0_0_24px_rgba(52,211,153,0.45)]"></div>
        <div class="flex items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <x-botones.arrastrar-seccion seccion="connection"/>
            <div>
              <h3 class="text-lg font-semibold">Prueba de conexión</h3>
              <p class="text-sm text-slate-300">Panel de envío real y vista previa del payload.</p>
            </div>
          </div>
          <x-botones.expandir-contraer abierto="isOpen('connection')" x-on:click="toggle('connection')"/>
        </div>

        <div x-show="dragging === 'connection'" x-cloak
             class="mt-4 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-xs uppercase tracking-[0.28em] text-emerald-100">
          Sección lista para soltar
        </div>

        <div x-show="isOpen('connection')" x-cloak class="mt-6">
          <livewire:whatsapp-connection-test/>
        </div>
        <div x-show="showDropHint('connection', 'after')" x-cloak
             class="mt-4 h-1 rounded-full bg-emerald-400/80 shadow-[0_0_24px_rgba(52,211,153,0.45)]"></div>
      </section>

      <section data-settings-section="templates"
               data-default-open="true"
               class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur"
               x-bind:class="sectionStateClasses('templates')"
               x-on:dragenter.prevent="setDropTarget('templates', $event)"
               x-on:dragover.prevent
               x-on:drop.prevent="drop('templates', $event)"
      >
        <div x-show="showDropHint('templates', 'before')" x-cloak
             class="mb-4 h-1 rounded-full bg-emerald-400/80 shadow-[0_0_24px_rgba(52,211,153,0.45)]"></div>
        <div class="flex items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <x-botones.arrastrar-seccion seccion="templates"/>
            <div>
              <h3 class="text-lg font-semibold">Plantillas</h3>
              <p class="text-sm text-slate-300">Editor y orden de plantillas guardadas.</p>
            </div>
          </div>
          <x-botones.expandir-contraer abierto="isOpen('templates')" x-on:click="toggle('templates')"/>
        </div>

        <div x-show="dragging === 'templates'" x-cloak
             class="mt-4 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-xs uppercase tracking-[0.28em] text-emerald-100">
          Plantilla en movimiento
        </div>

        <div x-show="isOpen('templates')" x-cloak class="mt-6">
          <livewire:whatsapp-template-manager/>
        </div>
        <div x-show="showDropHint('templates', 'after')" x-cloak
             class="mt-4 h-1 rounded-full bg-emerald-400/80 shadow-[0_0_24px_rgba(52,211,153,0.45)]"></div>
      </section>

      <section data-settings-section="reminders"
               data-default-open="true"
               class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur"
               x-bind:class="sectionStateClasses('reminders')"
               x-on:dragenter.prevent="setDropTarget('reminders', $event)"
               x-on:dragover.prevent
               x-on:drop.prevent="drop('reminders', $event)"
      >
        <div x-show="showDropHint('reminders', 'before')" x-cloak
             class="mb-4 h-1 rounded-full bg-emerald-400/80 shadow-[0_0_24px_rgba(52,211,153,0.45)]"></div>
        <div class="flex items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <x-botones.arrastrar-seccion seccion="reminders"/>
            <div>
              <h3 class="text-lg font-semibold">Tiempos de envío</h3>
              <p class="text-sm text-slate-300">Selecciona WhatsApp y email 1, 2, 3 días o una semana antes.</p>
            </div>
          </div>
          <x-botones.expandir-contraer abierto="isOpen('reminders')" x-on:click="toggle('reminders')"/>
        </div>

        <div x-show="dragging === 'reminders'" x-cloak
             class="mt-4 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-xs uppercase tracking-[0.28em] text-emerald-100">
          Recordatorios en movimiento
        </div>

        <div x-show="isOpen('reminders')" x-cloak class="mt-6">
          <livewire:appointment-reminder-settings/>
        </div>
        <div x-show="showDropHint('reminders', 'after')" x-cloak
             class="mt-4 h-1 rounded-full bg-emerald-400/80 shadow-[0_0_24px_rgba(52,211,153,0.45)]"></div>
      </section>
    </div>
  </div>

  <script>
      document.addEventListener('alpine:init', () => {
          Alpine.data('settingsBoard', () => ({
              orderKey: 'eugenia.settings.sections.order',
              openKey: 'eugenia.settings.sections.open',
              dragging: null,
              dropTarget: null,
              dropPosition: null,
              placeholder: null,
              openState: {},

              init() {
                  this.openState = this.loadOpenState();
                  this.applySavedOrder();
              },

              isVisible(sectionId) {
                  return this.sectionElement(sectionId) !== null;
              },

              isOpen(sectionId) {
                  if (Object.prototype.hasOwnProperty.call(this.openState, sectionId)) {
                      return this.openState[sectionId];
                  }

                  const section = this.sectionElement(sectionId);
                  return section ? section.dataset.defaultOpen === 'true' : false;
              },

              toggle(sectionId) {
                  this.openState[sectionId] = !this.isOpen(sectionId);
                  this.saveOpenState();
              },

              expandAll() {
                  this.sectionIds().forEach((sectionId) => {
                      this.openState[sectionId] = true;
                  });
                  this.saveOpenState();
              },

              collapseAll() {
                  this.sectionIds().forEach((sectionId) => {
                      this.openState[sectionId] = false;
                  });
                  this.saveOpenState();
              },

              startDrag(sectionId, event) {
                  const dragged = this.sectionElement(sectionId);

                  if (!dragged) {
                      return;
                  }

                  this.dragging = sectionId;
                  this.dropTarget = sectionId;
                  this.dropPosition = 'before';
                  event.dataTransfer.effectAllowed = 'move';
                  event.dataTransfer.setData('text/plain', sectionId);
                  event.dataTransfer.setDragImage(this.buildDragImage(dragged), 20, 20);
                  dragged.classList.add('settings-dragging');
              },

              stopDrag() {
                  const dragged = this.dragging ? this.sectionElement(this.dragging) : null;

                  if (dragged) {
                      dragged.classList.remove('settings-dragging');
                  }

                  this.removePlaceholder();
                  this.dragging = null;
                  this.dropTarget = null;
                  this.dropPosition = null;
              },

              setDropTarget(sectionId, event) {
                  if (!this.dragging || this.dragging === sectionId) {
                      return;
                  }

                  this.dropTarget = sectionId;

                  const section = this.sectionElement(sectionId);
                  if (!section) {
                      return;
                  }

                  const rect = section.getBoundingClientRect();
                  this.dropPosition = event.clientY > rect.top + (rect.height / 2) ? 'after' : 'before';
                  this.repositionDragged(section, this.dropPosition);
              },

              drop(sectionId) {
                  if (!this.dragging || this.dragging === sectionId) {
                      this.stopDrag();
                      return;
                  }

                  const dragged = this.dragging ? this.sectionElement(this.dragging) : null;

                  if (!dragged) {
                      this.persistOrder();
                      this.stopDrag();
                      return;
                  }

                  this.persistOrder();
                  this.stopDrag();
              },

              repositionDragged(target, position) {
                  if (!this.dragging || !target) {
                      return;
                  }

                  const dragged = this.sectionElement(this.dragging);

                  if (!dragged || dragged === target) {
                      return;
                  }

                  const previousRects = this.captureSectionRects();
                  const parent = target.parentNode;
                  const referenceNode = position === 'after' ? target.nextSibling : target;

                  if (referenceNode) {
                      parent.insertBefore(dragged, referenceNode);
                  } else {
                      parent.appendChild(dragged);
                  }

                  this.animateReflow(previousRects);
              },

              createPlaceholder(section) {
                  this.removePlaceholder();

                  const placeholder = document.createElement('div');
                  placeholder.dataset.settingsPlaceholder = 'true';
                  placeholder.className = 'settings-drop-placeholder';
                  placeholder.style.minHeight = `${Math.max(section.getBoundingClientRect().height, 96)}px`;
                  placeholder.style.height = `${Math.max(section.getBoundingClientRect().height, 96)}px`;
                  placeholder.innerHTML = '<span>Soltar aquí</span>';

                  this.placeholder = placeholder;
                  this.$refs.board.insertBefore(placeholder, section);

                  return placeholder;
              },

              movePlaceholder(target, position) {
                  if (!this.placeholder || !this.placeholder.parentNode || !target) {
                      return;
                  }

                  const parent = target.parentNode;
                  const referenceNode = position === 'after' ? target.nextSibling : target;

                  if (referenceNode) {
                      parent.insertBefore(this.placeholder, referenceNode);
                  } else {
                      parent.appendChild(this.placeholder);
                  }
              },

              removePlaceholder() {
                  if (this.placeholder && this.placeholder.parentNode) {
                      this.placeholder.parentNode.removeChild(this.placeholder);
                  }

                  this.placeholder = null;
              },

              buildDragImage(section) {
                  const clone = section.cloneNode(true);
                  clone.classList.add('pointer-events-none', 'scale-95', 'rotate-[-1deg]', 'shadow-2xl', 'shadow-emerald-500/25');
                  clone.style.position = 'absolute';
                  clone.style.top = '-9999px';
                  clone.style.left = '-9999px';
                  clone.style.width = `${section.offsetWidth}px`;
                  document.body.appendChild(clone);

                  setTimeout(() => clone.remove(), 0);

                  return clone;
              },

              captureSectionRects() {
                  return new Map(
                      this.sectionNodes().map((section) => [
                          section.dataset.settingsSection,
                          section.getBoundingClientRect(),
                      ])
                  );
              },

              animateReflow(previousRects) {
                  requestAnimationFrame(() => {
                      this.sectionNodes().forEach((section) => {
                          const sectionId = section.dataset.settingsSection;
                          const previousRect = previousRects.get(sectionId);

                          if (!previousRect) {
                              return;
                          }

                          const currentRect = section.getBoundingClientRect();
                          const deltaX = previousRect.left - currentRect.left;
                          const deltaY = previousRect.top - currentRect.top;

                          if (deltaX === 0 && deltaY === 0) {
                              return;
                          }

                          section.animate(
                              [
                                  {transform: `translate(${deltaX}px, ${deltaY}px)`},
                                  {transform: 'translate(0, 0)'},
                              ],
                              {
                                  duration: 240,
                                  easing: 'cubic-bezier(0.2, 0, 0, 1)',
                              }
                          );
                      });
                  });
              },

              applySavedOrder() {
                  const savedOrder = this.loadOrder();
                  if (!savedOrder.length) {
                      this.persistOrder();
                      return;
                  }

                  const board = this.$refs.board;
                  const elements = new Map(this.sectionIds().map((sectionId) => [sectionId, this.sectionElement(sectionId)]));

                  savedOrder
                      .filter((sectionId) => elements.has(sectionId))
                      .forEach((sectionId) => {
                          board.appendChild(elements.get(sectionId));
                      });

                  this.sectionIds()
                      .filter((sectionId) => !savedOrder.includes(sectionId))
                      .forEach((sectionId) => {
                          board.appendChild(this.sectionElement(sectionId));
                      });
              },

              persistOrder() {
                  localStorage.setItem(this.orderKey, JSON.stringify(this.sectionIds()));
              },

              loadOrder() {
                  try {
                      const raw = localStorage.getItem(this.orderKey);
                      return raw ? JSON.parse(raw) : [];
                  } catch (error) {
                      return [];
                  }
              },

              loadOpenState() {
                  try {
                      const raw = localStorage.getItem(this.openKey);
                      if (raw) {
                          return JSON.parse(raw);
                      }
                  } catch (error) {
                      // Fallback to defaults below.
                  }

                  return this.sectionIds().reduce((state, sectionId) => {
                      const section = this.sectionElement(sectionId);
                      state[sectionId] = section ? section.dataset.defaultOpen === 'true' : false;
                      return state;
                  }, {});
              },

              saveOpenState() {
                  localStorage.setItem(this.openKey, JSON.stringify(this.openState));
              },

              resetLayout() {
                  localStorage.removeItem(this.orderKey);
                  this.openState = this.sectionIds().reduce((state, sectionId) => {
                      state[sectionId] = false;
                      return state;
                  }, {});
                  this.saveOpenState();
                  window.location.reload();
              },

              sectionIds() {
                  return Array.from(this.$refs.board.querySelectorAll('[data-settings-section]'))
                      .map((section) => section.dataset.settingsSection);
              },

              sectionElement(sectionId) {
                  return this.$refs.board.querySelector(`[data-settings-section="${sectionId}"]`);
              },

              sectionNodes() {
                  return Array.from(this.$refs.board.querySelectorAll('[data-settings-section]'));
              },

              showDropHint(sectionId, position) {
                  return this.dragging && this.dropTarget === sectionId && this.dropPosition === position && this.dragging !== sectionId;
              },

              sectionStateClasses(sectionId) {
                  return {
                      'ring-4 ring-emerald-300/80 shadow-[0_24px_80px_rgba(16,185,129,0.24)] scale-[1.015]': this.dropTarget === sectionId && this.dragging && this.dragging !== sectionId,
                      'opacity-80 scale-[0.97] shadow-[0_16px_40px_rgba(15,23,42,0.32)]': this.dragging === sectionId,
                      'border-emerald-400/30 bg-emerald-500/5': this.dragging === sectionId || this.dropTarget === sectionId,
                      'transition-all duration-200 ease-out': true,
                  };
              },
          }));
      });
  </script>
@endsection
