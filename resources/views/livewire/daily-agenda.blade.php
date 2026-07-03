
<div class="rounded-3xl border border-white/10 bg-slate-900/30 backdrop-blur-xl">
  <div class="flex flex-col gap-6 border-b border-white/5 p-8 md:flex-row md:items-center md:justify-between">
    <div class="space-y-1">
      <h1 class="flex items-center gap-3 text-2xl font-bold tracking-tight text-white">
        <x-iconos.agenda clase="size-6 text-emerald-400"/>
        Agenda del día
      </h1>
      <p class="text-sm text-slate-400">
        Mostrando citas para el <span class="font-semibold text-emerald-300">{{ $selectedDate->translatedFormat('l, d \\d\\e F') }}</span>
      </p>
    </div>

    <div class="flex flex-wrap items-center gap-3">
      <div class="inline-flex rounded-2xl border border-white/10 bg-slate-950/60 p-1">
        @foreach ($targetDates as $date)
          <button wire:click="selectDate({{ $date['offset'] }})"
                  class="rounded-xl px-4 py-2.5 text-sm font-semibold transition-all duration-200 {{ $selectedDate->toDateString() === $date['date']->toDateString() ? 'bg-emerald-500/20 text-emerald-300 shadow-md' : 'text-slate-400 hover:text-white' }}">
            {{ $date['label'] }}
          </button>
        @endforeach
      </div>

      <div class="relative">
        <select wire:change="selectDate($event.target.value)"
                class="cursor-pointer appearance-none rounded-2xl border border-white/10 bg-slate-950/60 py-2.5 pr-10 pl-4 text-sm font-semibold text-slate-300 transition-all hover:border-white/20 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none">
          <option value="">Otros días...</option>
          @foreach ($futureDayOptions as $days)
            <option value="{{ $days }}" @selected($selectedDate->toDateString() === $resolvedDates[$days]->toDateString())>
              En {{ $days }} días
            </option>
          @endforeach
        </select>
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
          <x-iconos.down clase="size-4"/>
        </div>
      </div>
    </div>
  </div>

  <div class="p-8">
    @if ($sundayWarning)
      <div class="mb-6 flex items-start gap-3 rounded-2xl border border-amber-500/20 bg-amber-500/10 p-5 text-sm text-amber-300">
        <x-iconos.alert clase="mt-0.5 size-5 shrink-0"/>
        <div><span class="font-semibold">Nota de agenda:</span> {{ $sundayWarning }}</div>
      </div>
    @endif

    <div x-data="{
        abierto: null,
        fijados: [],
        estaAbierto(hora) { return this.abierto === hora || this.fijados.includes(hora) },
        alternar(hora) {
            if (this.estaAbierto(hora)) {
                this.fijados = this.fijados.filter((fijado) => fijado !== hora)
                if (this.abierto === hora) this.abierto = null
            } else {
                this.abierto = hora
            }
        },
        mantener(hora, activo) {
            if (activo && ! this.fijados.includes(hora)) this.fijados.push(hora)
            if (! activo) this.fijados = this.fijados.filter((fijado) => fijado !== hora)
            if (this.abierto === hora) this.abierto = null
        },
    }" class="space-y-6">
      @forelse ($nextAppointments as $hora => $citasHora)
        <div wire:key="appointment-group-{{ $selectedDate->toDateString() }}-{{ $hora }}" class="space-y-3">
          <div class="flex items-center gap-3">
            <button type="button"
                    x-on:click="alternar(@js($hora))"
                    x-bind:aria-expanded="estaAbierto(@js($hora)).toString()"
                    aria-controls="appointments-{{ str_replace(':', '-', $hora) }}"
                    class="flex cursor-pointer items-center gap-2 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-3 py-1.5">
              <x-iconos.reloj-agujas clase="size-4 text-emerald-400"/>
              <span class="text-sm font-bold text-emerald-300">
                {{ \Carbon\Carbon::parse($hora)->format('H:i') }} · {{ $citasHora->count() }} {{ Str::plural('cita', $citasHora->count()) }}
              </span>
            </button>
            <div class="h-px flex-1 bg-white/5"></div>
            <label class="flex cursor-pointer items-center gap-2 text-[11px] font-medium text-slate-500">
              <span>Mantener abierto</span>
              <input type="checkbox" x-bind:checked="fijados.includes(@js($hora))"
                     x-on:change="mantener(@js($hora), $event.target.checked)"
                     class="size-4 cursor-pointer rounded border-white/20 bg-slate-950/50 text-emerald-500 accent-emerald-500 focus:ring-2 focus:ring-emerald-400/40 focus:ring-offset-0">
            </label>
          </div>

          <div id="appointments-{{ str_replace(':', '-', $hora) }}" x-show="estaAbierto(@js($hora))" x-cloak
               class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
            @foreach ($citasHora as $appointment)
              @php($incidences = $this->appointmentIncidences($appointment))
              <div class="group flex flex-col gap-3 rounded-2xl border border-white/5 bg-slate-950/40 p-4 transition-all duration-300 hover:border-white/10 hover:bg-slate-950/80">
                <div class="space-y-2">
                  <div class="flex flex-col items-center justify-between gap-3">
                    <a href="{{ route('clients.edit', $appointment->client_id) }}"
                       class="block truncate text-sm font-bold text-slate-200 transition-colors hover:text-emerald-300 hover:underline"
                       aria-label="Editar datos del cliente" title="Editar datos del cliente">
                      {{ $appointment->client?->full_name }}
                    </a>
                    <div class="flex gap-2">
                      <x-botones.icono-buton
                              color="indigo" icon="ojo"
                              especialtexto="text-xs! gap-2! w-full! flex "
                              especial="size-6 fill-indigo-500/80"
                              label="Ver citas de {{ $appointment->client?->full_name }}"
                              texto="Ver todas las citas"
                              onclick="window.location.href='{{ route('clients.appointments', $appointment->client_id) }}'"
                      />
                      {{--<x-botones.icono-buton color="blue" icon="lapiz"
                                             especial2="fill-blue-500/50 size-4 group-hover:fill-blue-500/80"
                                               label="Editar cita de {{ $appointment->client?->full_name }}"
                                               onclick="window.location.href='{{ route('clients.appointments', $appointment->client_id) }}'"/>--}}
                    </div>
                  </div>
{{--                  @if ($incidences)
                    <div class="flex flex-wrap gap-2">
                      @foreach ($incidences as $incidence)
                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold tracking-wide uppercase {{ $incidence['classes'] }}">
                          @if (isset($incidence['icono']))
                            <x-iconos.check clase="mr-2 size-4 rounded-3xl text-green-300"/>
                          @endif
                          {{ $incidence['label'] }}
                        </span>
                      @endforeach
                    </div>
                  @endif--}}
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @empty
        <div class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-white/5 bg-slate-950/20 py-16 text-center">
          <div class="rounded-2xl border border-white/5 bg-slate-900/60 p-5 text-slate-500">
            <x-iconos.calendar clase="size-10"/>
          </div>
          <h3 class="mt-4 text-base font-bold text-slate-300">No hay citas en esta fecha</h3>
          <p class="mt-1 max-w-xs text-sm text-slate-500">No se han encontrado citas para este día.</p>
        </div>
      @endforelse
    </div>
  </div>
</div>
