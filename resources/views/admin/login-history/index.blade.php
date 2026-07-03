@extends('layouts.app')

@section('content')
    <div class="grid gap-6 max-w-6xl mx-auto">
        <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
            <h2 class="text-xl font-semibold">Historial de conexiones</h2>
            <div class="mt-4 overflow-hidden rounded-2xl border border-white/10">
                <table class="min-w-full divide-y divide-white/10 text-left text-sm">
                    <thead class="bg-slate-900/70 text-slate-300">
                        <tr>
                            <th class="px-4 py-3">Usuario</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">IP</th>
                            <th class="px-4 py-3">Navegador</th>
                            <th class="px-4 py-3">Fecha y hora</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10 bg-slate-950/40">
                        @forelse ($logins as $login)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $login->user->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ $login->user->email ?? '—' }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $login->ip_address ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs text-slate-400 max-w-xs truncate" title="{{ $login->user_agent }}">
                                    {{ \Illuminate\Support\Str::after($login->user_agent ?? '', '/') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $login->logged_in_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">Aún no hay registros de conexión.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $logins->links() }}
            </div>
        </div>
    </div>
@endsection
