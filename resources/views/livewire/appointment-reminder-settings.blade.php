<div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <h3 class="text-lg font-semibold">Anticipación de recordatorios</h3>
      <p class="mt-2 text-sm text-slate-300">
        Selecciona cuándo se preparan los avisos automáticos antes de cada cita activa.
      </p>
    </div>

  </div>

  @if ($status)
    <div class="mt-4 rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
      {{ $status }}
    </div>
  @endif

  <form class="mt-6 grid gap-5" wire:submit="save">
    <div class="grid gap-4 md:grid-cols-2">
      <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-4">
        <flux:checkbox.group wire:model="whatsappLeadDays" label="WhatsApp">
          <div class="mt-3 grid gap-3">
            @foreach ($leadDayOptions as $leadDays => $label)
              <flux:checkbox
                      value="{{ $leadDays }}"
                      label="{{ $label }}"
              />
            @endforeach
          </div>
        </flux:checkbox.group>
        <flux:error name="whatsappLeadDays"/>
        <flux:error name="whatsappLeadDays.*"/>
      </div>

      <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-4">
        <flux:checkbox.group wire:model="emailLeadDays" label="Email">
          <div class="mt-3 grid gap-3">
            @foreach ($leadDayOptions as $leadDays => $label)
              <flux:checkbox
                      value="{{ $leadDays }}"
                      label="{{ $label }}"
              />
            @endforeach
          </div>
        </flux:checkbox.group>
        <flux:error name="emailLeadDays"/>
        <flux:error name="emailLeadDays.*"/>
      </div>
    </div>

    <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4 text-sm text-slate-300">
      WhatsApp ya usa esta selección para generar mensajes pendientes. Email queda preparado como preferencia hasta activar el envío de correos.
    </div>

    <div>
      <x-botones.icono-buton
              icon="disquete"
              type="submit"
              label="Guardar"
              texto="Guardar" />
    </div>
  </form>
</div>
