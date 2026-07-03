<div class="mx-auto max-w-3xl rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
  <div class="flex flex-wrap items-center justify-between ">
    <div class="flex items-center gap-6">
      <h1 class="text-xl font-semibold d uppercase tracking-[0.08em] text-emerald-300/80">Listado de
        clientes</h1>
      <div class="rounded-2xl border border-white/10 bg-slate-900/60 px-4 py-3 text-sm text-slate-300">
        {{ $clients->total() }} cliente{{ $clients->total() === 1 ? '' : 's' }}
      </div>
    </div>

    <x-botones.icono-buton
            color="emerald"
            icon="usuario-plus"
            label="Nuevo Cliente"
            texto="Nuevo Cliente"
            onclick="window.location.href='{{ route('clients.create') }}'"
            />
  </div>

  <div class="mt-4 grid gap-4 sm:grid-cols-3">
    <flux:field>
      <flux:label>Nombre</flux:label>
      <x-formularios.input wire:model.live.debounce.300ms="filter_nombre" placeholder="Buscar por nombre"/>
    </flux:field>

    <flux:field>
      <flux:label>Apellidos</flux:label>
      <x-formularios.input wire:model.live.debounce.300ms="filter_apellidos" placeholder="Buscar por apellidos"/>
    </flux:field>

    <flux:field>
      <flux:label>Teléfono</flux:label>
      <x-formularios.input wire:model.live.debounce.300ms="filter_telefono" placeholder="Buscar por teléfono"/>
    </flux:field>
  </div>

  <div class="mt-4 overflow-hidden rounded-2xl border border-white/10">
    <table class="min-w-full divide-y divide-white/10 text-left text-sm">
      <thead class="bg-slate-900/70 text-slate-300">
      <tr>
        <th class="px-4 py-3">
          <button type="button"
                  class="inline-flex cursor-pointer items-center gap-1 font-semibold text-slate-200 hover:text-white"
                  wire:click="sortByName">
            Nombre completo
            <span class="text-xs text-slate-400">{{ $sort_direction === 'asc' ? '↑' : '↓' }}</span>
          </button>
        </th>
        <th class="px-4 py-3">Teléfono</th>
        <th class="px-4 py-3 text-center">Acciones</th>
      </tr>
      </thead>
      <tbody class="divide-y divide-white/10 bg-slate-950/40">
      @forelse ($clients as $client)
        <tr wire:key="client-{{ $client->id }}">
          <td class="px-4 py-3">{{ $client->nombre }} {{ $client->apellidos }}</td>
          <td class="px-4 py-3">{{ $client->telefono }}</td>
          <td class="px-4 py-3 text-right">
            <div class="flex justify-center  gap-4">
              <x-botones.icono-buton
                      color="amber"
                      icon="ojo"
                      label="Ver Cliente"
                      onclick="window.location ='{{ route('clients.appointments', $client) }}'"
              />
              <x-botones.icono-buton
                      color="sky"
                      icon="cita"
                      label="Crear cita"
                      onclick="window.location ='{{ route('appointments.create', ['client' => $client->id]) }}'"
              />
              <x-botones.icono-buton
                      color="sky"
                      icon="lapiz"
                      label="Editar cliente"
                      onclick="window.location ='{{ route('clients.edit', $client) }}'"
              />
              <x-botones.icono-buton
                      color="red"
                      icon="papelera"
                      label="Eliminar cliente"
                      wire:click="confirmDelete({{ $client->id }})"
              />
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td class="px-4 py-6 text-slate-400" colspan="4">
            @if ($showAllClients || $hasClientSearch)
              No hay clientes para esa búsqueda.
            @else
              Las coincidencias aparecerán aquí cuando escribas al menos un carácter.
            @endif
          </td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>

  @if ($showAllClients || $hasClientSearch)
    <div class="mt-4">
      {{ $clients->links('vendor.pagination.tailwind') }}
    </div>
  @endif

  @if ($clientPendingDeletion)
    <x-modales.confirmacion x-data="{ modalOpen: true }" x-trap.noscroll="modalOpen"
                             x-on:keydown.escape.window="$wire.cancelDelete()" titulo="Eliminar cliente">
      <p class="mt-3 text-sm text-slate-300">
        ¿Seguro que quieres eliminar a
        <span class="font-medium text-white">{{ $clientPendingDeletion->nombre }} {{ $clientPendingDeletion->apellidos }}</span>?
      </p>
      <p class="mt-2 text-sm text-slate-400">Esta acción no se puede deshacer.</p>

      <x-slot:actions>
        <x-botones.icono-buton color="amber" icon="volver" label="Cancelar" texto="Cancelar"
                                 wire:click="cancelDelete" />
        <x-botones.icono-buton color="red" icon="papelera" label="Eliminar cliente" texto="Eliminar cliente"
                                 wire:click="deleteConfirmed" />
      </x-slot:actions>
    </x-modales.confirmacion>
  @endif
</div>
