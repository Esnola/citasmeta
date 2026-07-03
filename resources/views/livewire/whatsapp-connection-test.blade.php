<div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
  <div class="flex items-start justify-between gap-4">
    <div>
      <h3 class="text-lg font-semibold">Prueba de conexión</h3>
      <p class="mt-2 text-sm text-slate-300">
        Envía un mensaje corto al número que indiques para comprobar que Cloud API responde.
      </p>
    </div>
    <div class="rounded-2xl border border-white/10 bg-slate-900/60 px-4 py-3 text-xs uppercase tracking-[0.25em] text-slate-300">
      Cloud API
    </div>
  </div>

  @if ($status)
    <div @class([
        'mt-4 rounded-2xl border px-4 py-3 text-sm',
        'border-emerald-400/30 bg-emerald-500/10 text-emerald-200' => $statusType === 'success',
        'border-rose-400/30 bg-rose-500/10 text-rose-200' => $statusType === 'error',
        'border-white/10 bg-slate-900/60 text-slate-200' => ! in_array($statusType, ['success', 'error'], true),
    ])>
      {{ $status }}
    </div>
  @endif

  <div class="mt-4 grid gap-4 md:grid-cols-2">
    <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-4">
      <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Driver</p>
      <p class="mt-2 font-medium text-slate-100">{{ $previewPayload['provider'] ?? 'n/a' }}</p>
    </div>
    <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-4">
      <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Destino</p>
      <p class="mt-2 font-medium text-slate-100">{{ $previewPayload['request']['to'] ?? $previewPayload['request']['recipient'] ?? 'n/a' }}</p>
    </div>
  </div>

  <form class="mt-6 grid gap-4" wire:submit="sendTest">
    <div class="grid gap-4 md:grid-cols-2">
      <flux:field>
        <flux:label>Destino</flux:label>
        <x-formularios.input wire:model.live="recipient" placeholder="600123123 o +34600123123"/>
        <flux:error name="recipient"/>
      </flux:field>

      <flux:field>
        <flux:label>Mensaje</flux:label>
        <flux:textarea wire:model.live="body" rows="4"/>
        <flux:error name="body"/>
      </flux:field>
    </div>

    <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-4 text-sm text-slate-300">
      <p class="font-medium text-slate-200">Notas de prueba</p>
      <ul class="mt-2 space-y-1">
        <li>• Si usas un número local, se normaliza a formato internacional.</li>
        <li>• Asegúrate de que `META_WHATSAPP_PHONE_NUMBER_ID` y `META_WHATSAPP_ACCESS_TOKEN` estén configurados.</li>
        <li>• Si quieres el botón rápido, define `META_WHATSAPP_TEST_RECIPIENT`.</li>
      </ul>
    </div>

    <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
      <div class="flex items-center justify-between gap-3">
        <p class="text-sm font-medium text-slate-200">Vista previa del payload</p>
        <span class="text-xs uppercase tracking-[0.25em] text-slate-500">Antes de enviar</span>
      </div>
      <pre class="mt-3 overflow-x-auto rounded-xl border border-white/10 bg-slate-950/80 p-4 text-xs leading-5 text-slate-200">{{ json_encode($previewPayload['request'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>

    <div class="flex flex-wrap gap-3">
      <x-botones.icono-buton
              type="submit"
              icon="enviar"
              label="Enviar prueba"
              texto="Enviar prueba"
              especial="size-6"
      />

      <x-botones.icono-buton
              type="button"
              color="amber"
              icon="whatsapp"
              especial="size-6"
              label="Enviar Prueba"
              texto="Enviar prueba"
              wire:click="sendSavedRecipient"
      />

    </div>
  </form>

  @if (! empty($details))
    <div class="mt-6 rounded-2xl border border-white/10 bg-slate-950/40 p-4 text-sm text-slate-300">
      <p class="font-medium text-slate-200">Respuesta</p>
      <div class="mt-3 grid gap-2 md:grid-cols-3">
        <div>
          <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Proveedor</p>
          <p class="mt-1">{{ $details['provider'] ?? 'n/a' }}</p>
        </div>
        <div>
          <p class="text-xs uppercase tracking-[0.25em] text-slate-500">ID</p>
          <p class="mt-1">{{ $details['message_id'] ?? 'n/a' }}</p>
        </div>
        <div>
          <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Destino</p>
          <p class="mt-1">{{ $details['to'] ?? 'n/a' }}</p>
        </div>
      </div>
    </div>
  @endif
</div>
