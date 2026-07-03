<div class="grid gap-6">
    <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
        <h2 class="text-xl font-semibold">Importar clientes desde CSV</h2>
        <p class="mt-2 text-sm text-slate-300">
            Sube un CSV con clientes y el sistema creará o actualizará registros por teléfono.
        </p>

        @if ($status)
            <div @class([
                'mt-4 rounded-2xl border px-4 py-3 text-sm',
                'border-emerald-400/30 bg-emerald-500/10 text-emerald-100' => $statusType === 'success',
                'border-rose-400/30 bg-rose-500/10 text-rose-100' => $statusType === 'error',
                'border-white/10 bg-slate-900/60 text-slate-200' => ! in_array($statusType, ['success', 'error'], true),
            ])>
                {{ $status }}
            </div>
        @endif

        <form class="mt-6 grid gap-4 lg:grid-cols-4 lg:items-end" wire:submit.prevent="import">
            <div class="lg:col-span-3">
                <flux:field>
                    <flux:label>Archivo</flux:label>
                    <x-formularios.input wire:model="file" type="file" accept=".csv" />
                    <flux:error name="file" />
                </flux:field>
            </div>
            <div class="flex flex-wrap gap-3">
                <x-botones.icono-buton
                    color="amber"
                    icon="ojo"
                    especial="size-6"
                    type="button"
                    wire:click="preview"
                    wire:loading.attr="disabled"
                    wire:target="preview"
                    label="Previsualizar"
                    texto="Previsualizar"
                />
                <x-botones.icono-buton
                    type="submit"
                    icon="check"
                    wire:loading.attr="disabled"
                    wire:target="import"
                    label="Importar"
                    texto="Importar"
                />
            </div>
        </form>
    </div>

    <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold">Vista previa del archivo</h2>
                <p class="mt-2 text-sm text-slate-300">
                    {{ $previewLoaded ? 'Los datos mostrados ya han sido interpretados.' : 'Pulsa previsualizar para cargar las filas del CSV.' }}
                </p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-slate-900/60 px-4 py-3 text-sm text-slate-300">
                {{ count($previewRows) }} filas visibles
            </div>
        </div>

        <div class="mt-4 overflow-hidden rounded-2xl border border-white/10">
            <table class="min-w-full divide-y divide-white/10 text-left text-sm">
                <thead class="bg-slate-900/70 text-slate-300">
                    <tr>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Apellidos</th>
                        <th class="px-4 py-3">Teléfono</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-slate-950/40">
                    @forelse ($previewRows as $row)
                        <tr>
                            <td class="px-4 py-3">{{ $row['nombre'] ?? '' }}</td>
                            <td class="px-4 py-3">{{ $row['apellidos'] ?? '' }}</td>
                            <td class="px-4 py-3">{{ $row['telefono'] ?? '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-slate-400" colspan="3">No hay filas para mostrar todavía.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
