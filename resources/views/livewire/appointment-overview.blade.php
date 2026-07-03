<div class="grid gap-6">
  <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div class="flex items-center gap-6">
        <x-iconos.calendar/>
        <h2 class="text-xl font-semibold">Citas registradas</h2>
        <h3 class="rounded-2xl border border-green-300/70 bg-slate-900/60 px-4 py-3 text-sm text-slate-300 shadow-xs shadow-green-300">
          {{ $clients->total() }} cliente{{ $clients->total() !== 1 ? 's' : '' }}
        </h3>
        @if ($deliveryStatusesSyncedAt)
          <span class="text-xs font-medium text-slate-400">Sincronizado: {{ $deliveryStatusesSyncedAt }}</span>
        @endif
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <x-botones.icono-buton color="indigo" icon="reload" label="Actualizar datos" texto="Actualizar datos"
                               wire:click="syncDeliveryStatuses" wire:loading.attr="disabled"
                               wire:target="syncDeliveryStatuses"/>
        <x-botones.icono-buton icon="calendario-filtro" label="Citas enviadas" texto="Citas enviadas"
                               onclick="window.location.href='{{ route('appointments.sent') }}'"/>
        <x-botones.icono-buton color="emerald" icon="nueva-cita" label="Nueva cita" texto="Nueva cita"
                               onclick="window.location.href='{{ route('appointments.create') }}'"/>
      </div>
    </div>

    <div class="mt-4 flex flex-wrap items-end gap-4">
      <flux:field>
        <flux:label>Nombre</flux:label>
        <x-formularios.input wire:model.live.debounce.300ms="filter_nombre" placeholder="Filtrar por nombre"/>
      </flux:field>
      <flux:field>
        <flux:label>Apellidos</flux:label>
        <x-formularios.input wire:model.live.debounce.300ms="filter_apellidos" placeholder="Filtrar por apellidos"/>
      </flux:field>
      <div class="group flex items-center gap-2">
        <button type="button"
                class="cursor-pointer rounded-full p-2"
                aria-label="Mostrar información"
                title="Mostrar información"
                onclick="document.querySelector('[data-info]').classList.toggle('hidden');
                       document.querySelector('[data-bombilla]').classList.toggle('text-amber-200!')">
          <x-iconos.bombilla
                  data-bombilla
                  clase="size-8 text-slate-400/30 transition-colors group-hover:text-amber-300!"/>
        </button>

        <div data-info class="text-xs hidden group-hover:block">
          <div class="flex items-center gap-2 text-white/50"><span class="size-2 bg-white/50 rounded-full"></span>
            Se muestran {{ $clients->total() }} cliente{{ $clients->total() !== 1 ? 's' : '' }} con citas
            pendientes
          </div>
          <div class="flex items-center gap-2 text-white/50"><span class="size-2 bg-white/50 rounded-full"></span>
            Se muestra la cita mas próxima.
          </div>
          <div class="flex items-center gap-2 text-white/50">
            <x-iconos.doble-check clase="size-4 text-green-400"/>
            Recordatorio Leído
            <x-iconos.doble-check clase="size-4 text-gray-400"/>
            Recordatorio enviado y No Leído
            <x-iconos.whatsapp clase="size-4 text-gray-500/70"/>
            Recordatorio pendiente de envío o no enviado
          </div>
        </div>
      </div>
      {{--      <div class="flex items-center gap-4">
              <flux:field class="flex flex-col">
                <flux:label>Enviadas</flux:label>
                <x-formularios.toggle wire:model.live="filter_enviado"/>
              </flux:field>
              <flux:field class="flex flex-col">
                <flux:label>Entregadas</flux:label>
                <x-formularios.toggle wire:model.live="filter_entregado"/>
              </flux:field>
              <flux:field class="flex flex-col">
                <flux:label>Suspendidas</flux:label>
                <x-formularios.toggle wire:model.live="filter_activo"/>
              </flux:field>
    </div>--}}
    </div>

    <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
      @forelse ($clients as $client)
        @php $appointment = $client->appointments->first(); @endphp
        <a wire:key="appointment-client-{{ $client->id }}"
           href="{{ route('clients.appointments', $client) }}"
           class="group rounded-2xl border border-white/10 p-4 backdrop-blur transition-colors hover:border-white/50 hover:bg-white/5 hover:scale-105 shadow hover:shadow-2xs">
          <div class="flex items-start justify-between gap-2">
            <div>
              <span class="font-medium text-emerald-300">{{ $client->full_name }}</span>
              <div class="mt-1 flex items-center gap-1 text-xs text-slate-400">
                <x-iconos.telefono-mesa/>
                {{ $client->telefono }}
              </div>
            </div>
            <span class="rounded-full bg-sky-500/15 px-2 py-0.5 text-xs font-semibold text-sky-200 ring-1 ring-inset ring-sky-400/30">
             Citas:  {{ $client->appointments_count }}
            </span>
          </div>
          @if ($appointment)
            <div class="mt-3 flex items-center justify-between gap-2 rounded-xl border border-white/5 bg-slate-900/40 p-3">
              <span class="text-xs text-slate-300">{{ $appointment->fecha?->format('d/m/Y') }} · {{ Str::substr($appointment->hora, 0, 5) }}</span>
              <span class="text-xs {{ $appointment->enviado ? 'text-green-400' : 'text-slate-500' }}">
                @if ($appointment->enviado)
                  <x-iconos.doble-check clase="size-4"/>
                @else
                  <x-iconos.whatsapp clase="size-4 text-gray-500/70"/>
                @endif
              </span>
            </div>
          @endif
        </a>
      @empty
        <div class="py-8 text-center text-slate-500 sm:col-span-2 lg:col-span-3 xl:col-span-5">
          No hay citas para mostrar todavía.
        </div>
      @endforelse
    </div>

    <div class="mt-4">{{ $clients->links('vendor.pagination.tailwind') }}</div>
  </div>
</div>
