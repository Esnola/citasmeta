@extends('layouts.app')

@section('content')
    <div class="grid gap-6 max-w-4xl mx-auto">
        <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
            <h2 class="text-xl font-semibold">Importar y Exportar</h2>

            <div class="mt-6 grid gap-6 sm:grid-cols-2">
                <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-6">
                    <h3 class="text-lg font-medium text-violet-300">Importar</h3>
                    <p class="mt-2 text-sm text-slate-400">Importar clientes desde un archivo CSV.</p>
                    <div class="mt-4">
                        <a href="{{ route('imports.index') }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-violet-600/20 px-4 py-2 text-sm font-medium text-violet-300 transition-colors hover:bg-violet-600/30">
                            <x-iconos.excel clase="size-5"/>
                            Importar CSV
                        </a>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-6">
                    <h3 class="text-lg font-medium text-emerald-300">Exportar</h3>
                    <p class="mt-2 text-sm text-slate-400">Descargar listas de clientes, citas y usuarios en formato CSV.</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('admin.export.clients') }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-emerald-600/20 px-4 py-2 text-sm font-medium text-emerald-300 transition-colors hover:bg-emerald-600/30">
                            <x-iconos.excel clase="size-5"/>
                            Clientes CSV
                        </a>
                        <a href="{{ route('admin.export.appointments') }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-emerald-600/20 px-4 py-2 text-sm font-medium text-emerald-300 transition-colors hover:bg-emerald-600/30">
                            <x-iconos.excel clase="size-5"/>
                            Citas CSV
                        </a>
                        <a href="{{ route('admin.export.users') }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-emerald-600/20 px-4 py-2 text-sm font-medium text-emerald-300 transition-colors hover:bg-emerald-600/30">
                            <x-iconos.usuarios clase="size-5"/>
                            Usuarios CSV
                        </a>
                        <a href="{{ route('admin.export.database') }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-emerald-600/20 px-4 py-2 text-sm font-medium text-emerald-300 transition-colors hover:bg-emerald-600/30">
                            <x-iconos.disquete clase="size-5"/>
                            Base de datos ZIP
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
