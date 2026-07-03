<div class="grid gap-6">
  <div class='rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur '>
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div class="flex gap-6 items-center">
        <x-iconos.calendar/>
        <h2 class="text-xl font-semibold">
          {{ $sentOnly ? 'Citas enviadas' : ($selectedClient?->full_name ?? 'Citas') }}
        </h2>
        <h3 class="rounded-2xl border border-green-300/70 shadow-xs shadow-green-300 bg-slate-900/60 px-4 py-3 text-sm text-slate-300">
          {{ $appointmentsCount }} cita{{ $appointmentsCount > 1 ? 's' : '' }}
        </h3>
        @if ($deliveryStatusesSyncedAt)
          <span class="text-xs font-medium text-slate-400">Sincronizado: {{ $deliveryStatusesSyncedAt }}</span>
        @endif
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <x-botones.icono-buton
                color="indigo"
                icon="reload"
                label="Actualizar Datos"
                texto="Actualizar Datos"
                x-on:click="localStorage.setItem('autoSyncAfterSend', '1')"
                wire:click="syncDeliveryStatuses"
                wire:loading.attr="disabled"
                wire:target="syncDeliveryStatuses"
        />

        @if ($showAppointmentNavigation)
          <div class="contents" data-appointment-navigation="{{ $sentOnly ? 'all' : 'sent' }}">
            @if ($sentOnly)
              <x-botones.icono-buton
                      icon="calendar"
                      label="Todas las citas"
                      texto="Todas las citas"
                      onclick="window.location.href='{{ route('appointments.index')}}'"/>
            @else
              <x-botones.icono-buton
                      icon="calendario-filtro"
                      label="Citas enviadas"
                      texto="Citas enviadas"
                      onclick="window.location.href='{{ route('appointments.sent')}}'"/>
            @endif
          </div>
        @endif
        <x-botones.icono-buton
                color="emerald"
                icon="nueva-cita"
                label="Nueva cita"
                texto="Nueva cita"
                onclick="window.location.href='{{ route('appointments.create', $selectedClient ? ['client' => $selectedClient->id] : []) }}'"
        />


      </div>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-4">
      @if ($selectedClient && ! $sentOnly)
        <flux:radio.group wire:model.live="dateFilter" variant="segmented" label="Citas"
                          class="border border-white/10 rounded-2xl gap-1"
                          :disabled="$showAllHistory">
          <flux:radio value="upcoming"
                      class="cursor-pointer bg-white/5 hover:bg-emerald-50/60 hover:text-white/60t transition-all duration-300 data-checked:bg-emerald-200/30! data-checked:text-emerald-200!">
            <x-iconos.proxima-cita/>
            Próximas
          </flux:radio>
          <flux:radio value="all"
                      class="cursor-pointer bg-white/5 hover:bg-emerald-50/60 hover:text-white/60t transition-all duration-300 data-checked:bg-emerald-200/30! data-checked:text-emerald-200!">
            <x-iconos.todos/>
            Todas
          </flux:radio>
          <flux:radio value="past"
                      class="cursor-pointer bg-white/5 hover:bg-emerald-50/60 hover:text-white/60t transition-all duration-300 data-checked:bg-emerald-200/30! data-checked:text-emerald-200!">
            <x-iconos.calendario-pasado/>
            Pasadas
          </flux:radio>
        </flux:radio.group>
      @endif

      @if ($showBulkActions && count($selectedAppointmentIds))
        <flux:dropdown>
          <flux:button icon="list-bullet" icon:trailing="chevron-down" variant="primary"
                       class="bg-emerald-600! text-white! hover:bg-emerald-500!"
                       :disabled="$selectedAppointmentIds === []">
            {{ count($selectedAppointmentIds) }} {{ count($selectedAppointmentIds) === 1 ? 'cita seleccionada' : 'citas seleccionadas' }}
          </flux:button>
          <flux:menu class="min-w-60 border! border-white/10! bg-slate-900! p-2! shadow-2xl! shadow-slate-950/60!">
            <flux:menu.item icon="check-circle" wire:click="updateSelectedActiveStatus(true)"
                            class="cursor-pointer text-emerald-200! transition-colors hover:bg-emerald-500/15! hover:text-emerald-100!">
              Activar seleccionadas
            </flux:menu.item>
            <flux:menu.item icon="pause-circle" wire:click="updateSelectedActiveStatus(false)"
                            class="cursor-pointer text-amber-200! transition-colors hover:bg-amber-500/15! hover:text-amber-100!">
              Desactivar seleccionadas
            </flux:menu.item>
            <flux:menu.separator class="my-2! bg-white/10!"/>
            <flux:menu.item icon="trash" wire:click="confirmBulkDelete"
                            class="cursor-pointer text-red-300! transition-colors hover:bg-red-500/15! hover:text-red-200!">
              Eliminar seleccionadas
            </flux:menu.item>
          </flux:menu>
        </flux:dropdown>
      @endif
      @if($show_filters_nombre)
        <flux:field>
          <flux:label>Nombre</flux:label>
          <x-formularios.input wire:model.live.debounce.300ms="filter_nombre" placeholder="Filtrar por nombre"
                               id="filter-nombre"
                               onkeydown="if(event.key==='Tab'){const a=document.querySelector('table tbody tr a');if(a){event.preventDefault();a.focus();}}"/>
        </flux:field>

        <flux:field>
          <flux:label>Apellidos</flux:label>
          <x-formularios.input wire:model.live.debounce.300ms="filter_apellidos"
                               placeholder="Filtrar por apellidos"/>
        </flux:field>
      @endif
      @unless ($sentOnly)
        <div class="flex flex-col items-center justify-center gap-2">
          <flux:label class="text-[14px] font-bold">Filtros</flux:label>
          <div class="flex items-center justify-center ml-6 gap-4">
            <flux:field class="flex flex-col">
              <flux:label>Enviadas</flux:label>
              <x-formularios.toggle wire:model.live="filter_enviado"/>
            </flux:field>

            <flux:field class="flex flex-col">
              <flux:label>Entregadas</flux:label>
              <x-formularios.toggle wire:model.live="filter_entregado"/>
            </flux:field>

            <flux:field class="flex flex-col">
              <flux:label>Supendidas</flux:label>
              <x-formularios.toggle wire:model.live="filter_activo"/>
            </flux:field>
          </div>
        </div>
      @endunless
    </div>

    @if ($selectedClient)
      <div class="mt-4 overflow-hidden rounded-2xl border border-white/10">
        <table class="min-w-full divide-y divide-white/10 text-left text-sm">
          <thead class="bg-slate-900/70 text-slate-300">
          <tr>
            <th class="px-4 py-3 text-xs">
              <input type="checkbox"
                     wire:key="select-all-appointments-{{ $dateFilter }}-{{ (int) $filter_enviado }}-{{ (int) $filter_activo }}-{{ (int) $filter_entregado }}"
                     class="size-4 cursor-pointer rounded border-white/20 bg-slate-950/50 text-emerald-500 accent-emerald-500 focus:ring-2 focus:ring-emerald-400/40 focus:ring-offset-0"
                     @checked($allVisibleAppointmentsSelected)
                     wire:change="toggleVisibleAppointments(@js($visibleAppointmentIds))"
                     aria-label="{{ $allVisibleAppointmentsSelected ? 'Deseleccionar todas las citas visibles' : 'Seleccionar todas las citas visibles' }}"
              >
            </th>
            <x-tabla.th-sort sortBy="cliente" :sortDirection="$sort_direction" :currentSort="$sort_by"/>
            <x-tabla.th-sort sortBy="fecha" :sortDirection="$sort_direction" :currentSort="$sort_by"/>
            <th class="px-4 py-3 text-center">Hora Cita</th>
            <th class="px-4 py-3 text-center">
              <div class="flex items-center justify-center gap-2">
                <x-iconos.whatsapp clase="size-4"/>
                Enviado
              </div>
            </th>
            <th class="px-4 py-3 text-center">
              <div class="flex items-center justify-center gap-2">
                <x-iconos.whatsapp clase="size-4"/>
                Entregado
              </div>
            </th>
            <th class="px-4 py-3 text-center">
              <div class="flex items-center justify-center gap-2">
                <x-iconos.whatsapp clase="size-4"/>
                Leído
              </div>
            </th>
            <th class="px-4 py-3 text-xs text-center">Recordatorio</th>
            <th class="px-4 py-3 text-xs text-center">Cita activa</th>
            <th class="px-4 py-3 text-xs text-right">Acciones</th>
          </tr>
          </thead>
          <tbody class="divide-y divide-white/10 bg-slate-950/40">
          @forelse ($appointments as $appointment)
            @php
              $editUrl = route('appointments.edit', $appointment);
              $rowUrl = $selectedClient
                  ? $editUrl
                  : ($sentOnly ? $editUrl : route('clients.appointments', $appointment->client_id));
              $isInactive = ! $appointment->cita_activa || $appointment->scheduledFor()->isPast();
            @endphp
            <tr wire:key="appointment-{{ $appointment->id }}"
                role="link" tabindex="0"
                onclick="window.location='{{ $rowUrl }}'"
                onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.location='{{ $rowUrl }}';}else if(event.key==='ArrowDown'){event.preventDefault();this.nextElementSibling?.focus();}else if(event.key==='ArrowUp'){event.preventDefault();this.previousElementSibling?.focus();}"
                class="cursor-pointer transition-colors hover:bg-white/5 focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-emerald-400/60 focus-visible:bg-white/5 {{ $isInactive ? 'bg-slate-400/15' : '' }}"

                @if ($appointment->enviado && $appointment->latestWhatsAppMessage?->provider_message_id)
                  title="Message SID: {{ $appointment->latestWhatsAppMessage?->provider_message_id }}"
                    @endif >
              <td class="px-4 py-3 text-center" onclick="event.stopPropagation()">
                <input type="checkbox"
                       class="size-4 cursor-pointer rounded border-white/20 bg-slate-950/50 text-emerald-500 accent-emerald-500 focus:ring-2 focus:ring-emerald-400/40 focus:ring-offset-0"
                       wire:model.live="selectedAppointmentIds"
                       value="{{ $appointment->id }}"
                       aria-label="Seleccionar cita de {{ $appointment->client?->full_name }}">
              </td>
              <td class="px-4 py-3">
                <a href="{{ $rowUrl }}" tabindex="-1"
                   class="inline-flex items-center gap-2 font-medium {{ $isInactive ? 'text-slate-400 hover:text-slate-300' : 'text-emerald-300 hover:text-emerald-200' }}">
                  <span>{{ $appointment->client?->nombre }} {{ $appointment->client?->apellidos }}</span>
                  @if (($appointment->appointments_count ?? 1) > 1)
                    <span class="inline-flex items-center rounded-full bg-sky-500/15 px-2 py-0.5 text-xs font-semibold text-sky-200 ring-1 ring-inset ring-sky-400/30"
                          title="{{ $appointment->appointments_count }} citas"
                          aria-label="{{ $appointment->appointments_count }} citas">
                    {{ $appointment->appointments_count }}
                  </span>
                  @endif
                </a>
              </td>
              <td class="px-4 py-3 text-center text-xs">{{ucwords($appointment->fecha?->translatedFormat('l, d - F - Y'))}}</td>
              <td class="px-4 py-3 text-center text-xs">{{ Str::substr($appointment->hora, 0, 5) }}</td>
              <td class="px-4 py-3 text-center">
                <div class="relative flex flex-col items-center justify-center gap-1">
                  @if($appointment->enviado)
                    <span class="flex items-center justify-center text-green-400">
                    <x-iconos.doble-check/>
                  </span>
                    <h6 class="text-[10px] text-slate-400">
                      {{ $appointment->whatsapp_sent_at?->format('H:i d/m/Y') }}
                    </h6>
                  @elseif($appointment->latestWhatsAppMessage?->provider_message_id)
                    <span class="flex items-center justify-center text-slate-300/40">
                    <x-iconos.doble-check/>
                  </span>
                  @else
                    <div class="flex flex-col items-center justify-center text-slate-500 text-xs">
                      <x-iconos.whatsapp clase="size-5"/>
                      @if($appointment->isFuture())
                        Pendiente
                      @else
                        No enviado
                      @endif
                    </div>
                  @endif
                </div>
              </td>
              <td class="px-4 py-3 text-center">
                @if($appointment->enviado)
                  <div class="relative flex flex-col items-center justify-center gap-1">
                    @if($appointment->entregado)
                      <span class="flex items-center justify-center text-green-400">
                      <x-iconos.doble-check/>
                    </span>
                      <h6 class="text-[10px] text-slate-400">
                        {{ $appointment->whatsapp_delivered_at?->format('H:i d/m/Y') }}
                      </h6>
                    @else
                      <span class="flex items-center justify-center text-slate-500">
                      <x-iconos.doble-check clase="size-6"/>
                    </span>
                    @endif
                  </div>
                @endif
              </td>
              <td class="px-4 py-3 text-center">
                @if($appointment->entregado)
                  <div class="relative flex flex-col items-center justify-center gap-1">
                    @if($appointment->whatsapp_read_at)
                      <span class="flex items-center justify-center text-green-400">
                      <x-iconos.doble-check/>
                    </span>
                      <h6 class="text-[10px] text-slate-400">
                        {{ $appointment->whatsapp_read_at?->format('H:i d/m/Y') }}
                      </h6>
                    @else
                      <span class="flex items-center justify-center text-slate-500">
                      <x-iconos.doble-check clase="size-6"/>
                    </span>
                    @endif
                  </div>
                @endif
              </td>
              <td class="px-4 py-3 text-center" onclick="event.stopPropagation()">
                @if ($appointment->enviado)
                  <x-botones.icono-buton
                          color="emerald"
                          icon="whatsapp"
                          label="Reenviar"
                          texto="Reenviar"
                          wire:click="confirmResend({{ $appointment->id }})"
                          wire:loading.attr="disabled"
                          wire:target="confirmResend({{ $appointment->id }})"
                  />
                @else
                  <x-formularios.toggle
                          :estado="$appointment->activo ? 'Sí' : 'No'"
                          :checked="$appointment->activo"
                          :locked="! $appointment->isFuture()"
                          wire:change="updateActiveStatus({{ $appointment->id }}, $event.target.checked)"/>
                @endif
              </td>
              <td class="px-4 py-3 text-center" onclick="event.stopPropagation()">
                <x-formularios.toggle
                        :estado="$appointment->cita_activa ? 'Sí' : 'No'"
                        :checked="$appointment->cita_activa"
                        :locked="! $appointment->isFuture()"
                        wire:change="updateAppointmentActiveStatus({{ $appointment->id }}, $event.target.checked)"/>
              </td>
              <td class="px-4 py-3 text-right" onclick="event.stopPropagation()">
                <div class="flex justify-end items-center gap-2">
                  @if (! $appointment->enviado && $appointment->activo && $appointment->isFuture())
                    <x-botones.icono-buton
                            color="emerald"
                            icon="whatsapp"
                            label="Enviar WhatsApp"
                            x-on:click="localStorage.setItem('autoSyncAfterSend', '1')"
                            wire:click="sendNow({{ $appointment->id }})"
                            wire:loading.attr="disabled"
                            wire:target="sendNow({{ $appointment->id }})"
                    />
                  @endif
                  <x-botones.icono-buton
                          color="blue"
                          icon="lapiz"
                          label="Editar cita"
                          onclick="window.location='{{ $editUrl }}'"
                  />
                  <x-botones.icono-buton
                          color="red"
                          icon="papelera"
                          label="Eliminar cita"
                          wire:click="confirmDelete({{ $appointment->id }})"
                  />
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td class="px-4 py-6 text-slate-400" colspan="10">
                {{ $sentOnly ? 'No hay citas enviadas para mostrar todavía.' : 'No hay citas para mostrar todavía.' }}
              </td>
            </tr>
          @endforelse
          </tbody>
        </table>
      </div>
    @else
      <div class="mt-4 grid grid-cols-5 gap-2">
        @forelse ($appointmentsByClient as $clientId => $clientData)
          @php
            $first = $clientData['appointments']->first();
            $isInactive = ! $first->cita_activa || $first->scheduledFor()->isPast();
          @endphp
          <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur transition-colors hover:bg-white/10 hover:border-white/20">
            <div class="mb-3 flex items-start justify-between gap-2">
              <div>
                <a href="{{ route('clients.appointments', $clientId) }}"
                   class="font-medium {{ $isInactive ? 'text-slate-400 hover:text-slate-300' : 'text-emerald-300 hover:text-emerald-200' }}">
                  {{ $first->client?->nombre }} {{ $first->client?->apellidos }}
                </a>
                <div class="text-xs text-slate-400 flex items-center gap-1 mt-1">
                  <x-iconos.telefono-mesa/>
                  {{ $first->client?->telefono }}
                </div>
              </div>
              <span class="shrink-0 inline-flex items-center rounded-full bg-sky-500/15 px-2 py-0.5 text-xs font-semibold text-sky-200 ring-1 ring-inset ring-sky-400/30">
              {{ $clientData['pendingCount'] }}
            </span>
            </div>
            <div class="space-y-2">
              @foreach ($clientData['appointments'] as $appointment)
                <a href="{{ route('clients.appointments', $clientId) }}"
                   class="block rounded-xl border border-white/5 bg-slate-900/40 p-3 transition-colors hover:bg-white/5">
                  <div class="flex items-center justify-between gap-2">
                    <span class="text-xs text-slate-300">{{ $appointment->fecha?->format('d/m/Y') }}</span>
                    <div class="flex items-center gap-1.5">
                      @if($appointment->enviado)
                        <span class="text-green-400"><x-iconos.doble-check clase="size-3.5"/></span>
                      @else
                        <span class="text-slate-600"><x-iconos.whatsapp clase="size-3.5"/></span>
                      @endif
                      @if($appointment->entregado)
                        <span class="text-green-400"><x-iconos.doble-check clase="size-3.5"/></span>
                      @endif
                      @if($appointment->whatsapp_read_at)
                        <span class="text-green-400"><x-iconos.doble-check clase="size-3.5"/></span>
                      @endif
                    </div>
                  </div>
                </a>
              @endforeach
            </div>
          </div>
        @empty
          <div class="col-span-5 py-8 text-center text-slate-500">
            No hay citas para mostrar todavía.
          </div>
        @endforelse
      </div>
    @endif

    <div class="mt-4">
      {{ $appointments->links('vendor.pagination.tailwind') }}
    </div>
  </div>

  @if ($appointmentPendingDeletion)
    <x-modales.confirmacion x-data="{ modalOpen: true }" x-trap.noscroll="modalOpen"
                            x-on:keydown.escape.window="$wire.cancelDelete()" titulo="Eliminar cita">
      <p class="mt-3 text-sm text-slate-300">
        ¿Seguro que quieres eliminar la cita de
        <span class="font-medium text-white">{{ $appointmentPendingDeletion->client?->full_name }}</span>
        del {{ $appointmentPendingDeletion->fecha?->format('d/m/Y') }} a
        las {{ Str::substr($appointmentPendingDeletion->hora, 0, 5) }}?
      </p>
      <p class="mt-2 text-sm text-slate-400">Esta acción no se puede deshacer.</p>

      <x-slot:actions>
        <x-botones.icono-buton color="amber" label="Cancelar" texto="Cancelar" icon="volver"
                               wire:click="cancelDelete"/>
        <x-botones.icono-buton color="red" icon="papelera" label="Eliminar cita" texto="Eliminar cita"
                               wire:click="deleteConfirmed"/>
      </x-slot:actions>
    </x-modales.confirmacion>
  @endif

  @if ($appointmentPendingResend)
    <x-modales.confirmacion x-data="{ modalOpen: true }" x-trap.noscroll="modalOpen"
                            x-on:keydown.escape.window="$wire.cancelResend()" titulo="Reenviar WhatsApp">
      <p class="mt-3 text-sm text-slate-300">Ya se ha enviado un WhatsApp de esta cita.</p>
      <p class="mt-2 text-sm text-slate-400">¿Quieres enviarlo de nuevo?</p>

      <x-slot:actions>
        <x-botones.icono-buton color="amber" label="Cancelar" texto="Cancelar" icon="volver"
                               wire:click="cancelResend"/>
        <x-botones.icono-buton color="emerald" icon="whatsapp" label="Reenviar" texto="Reenviar"
                               wire:click="resendConfirmed"/>
      </x-slot:actions>
    </x-modales.confirmacion>
  @endif

  @if ($bulkDeleteConfirmationOpen)
    <x-modales.confirmacion x-data="{ modalOpen: true }" x-trap.noscroll="modalOpen"
                            x-on:keydown.escape.window="$wire.$set('bulkDeleteConfirmationOpen', false)"
                            titulo="Eliminar citas seleccionadas">
      <p class="mt-3 text-sm text-slate-300">
        Se eliminarán <span class="font-medium text-white">{{ count($selectedAppointmentIds) }} cita(s)</span>.
      </p>
      <p class="mt-2 text-sm text-slate-400">Esta acción no se puede deshacer.</p>

      <x-slot:actions>
        <x-botones.icono-buton color="amber" icon="volver" label="Cancelar" texto="Cancelar"
                               wire:click="$set('bulkDeleteConfirmationOpen', false)"/>
        <x-botones.icono-buton color="red" icon="papelera" label="Eliminar citas" texto="Eliminar citas"
                               wire:click="deleteSelected"/>
      </x-slot:actions>
    </x-modales.confirmacion>
  @endif

  @script
  <script>
      if (localStorage.getItem('autoSyncAfterSend') === '1') {
          localStorage.removeItem('autoSyncAfterSend');
          setTimeout(() => $wire.syncDeliveryStatuses(), 3000);
      }
  </script>
@endscript
