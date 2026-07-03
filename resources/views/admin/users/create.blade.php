@extends('layouts.app')

@section('content')
    <div class="grid gap-6 max-w-4xl mx-auto">
        <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
            <h2 class="text-xl font-semibold">Crear usuario</h2>
            <form class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div>
                    <label class="mb-2 block text-sm text-slate-300">Nombre</label>
                    <x-formularios.input name="name" value="{{ old('name') }}" required autofocus />
                    @error('name') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm text-slate-300">Email</label>
                    <x-formularios.input name="email" type="email" value="{{ old('email') }}" required />
                    @error('email') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm text-slate-300">Contraseña</label>
                    <x-formularios.input name="password" type="password" required />
                    @error('password') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm text-slate-300">Confirmar contraseña</label>
                    <x-formularios.input name="password_confirmation" type="password" required />
                </div>
                <div class="flex flex-col gap-8">

                  <flux:checkbox name="is_admin" label="Administrador" :checked="old('is_admin')" />
                  <x-botones.icono-buton
                    color="emerald"
                    type="submit"
                    icon="usuario-plus"
                    label="Crear usuario"
                    texto="Crear usuario"
                    class="max-w-fit"
                  />


                </div>
            </form>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
            <h2 class="text-xl font-semibold">Usuarios existentes</h2>
            <div class="mt-4 overflow-hidden rounded-2xl border border-white/10">
                <table class="min-w-full divide-y divide-white/10 text-left text-sm">
                    <thead class="bg-slate-900/70 text-slate-300">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Rol</th>
                            <th class="px-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10 bg-slate-950/40">
                        @foreach ($users as $user)
                            <tr>
                                <td class="px-4 py-3">{{ $user->id }}</td>
                                <td class="px-4 py-3">{{ $user->name }}</td>
                                <td class="px-4 py-3">{{ $user->email }}</td>
                                <td class="px-4 py-3">
                                    @if ($user->is_admin)
                                        <span class="inline-flex items-center rounded-full bg-amber-400/15 px-2.5 py-0.5 text-xs font-semibold text-amber-200 ring-1 ring-inset ring-amber-400/30">Admin</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-slate-400/15 px-2.5 py-0.5 text-xs font-semibold text-slate-300 ring-1 ring-inset ring-slate-400/30">Usuario</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3" x-data="{ confirmDelete: false }">
                                      <x-botones.icono-buton
                                              color="blue"
                                              icon="lapiz"
                                              label="Edita usuario"
                                              onclick="window.location.href='{{ route('admin.users.edit', $user) }}'"
                                      />

                                        @if ($user->is_admin && $adminCount === 1)
                                            <span class="text-slate-500">Protegido</span>
                                        @elseif ($user->id === Auth::id())
                                            <span class="text-slate-500">Tu cuenta</span>
                                        @else
                                            <x-botones.icono-buton color="red" icon="user-menos" label="Eliminar usuario"
                                                                     x-on:click="confirmDelete = true" />

                                            <x-modales.confirmacion x-show="confirmDelete" x-cloak
                                                                     x-trap.noscroll="confirmDelete"
                                                                     x-on:keydown.escape.window="confirmDelete = false"
                                                                     titulo="Eliminar usuario">
                                                <p class="mt-3 text-sm text-slate-300">
                                                    ¿Seguro que quieres eliminar a
                                                    <span class="font-medium text-white">{{ $user->name }}</span>
                                                    ({{ $user->email }})?
                                                </p>
                                                <p class="mt-2 text-sm text-slate-400">Esta acción no se puede deshacer.</p>

                                                <x-slot:actions>
                                                    <form class="flex flex-wrap justify-end gap-2" method="POST"
                                                          action="{{ route('admin.users.destroy', $user) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <x-botones.icono-buton color="amber" icon="volver" label="Cancelar"
                                                                                 texto="Cancelar" x-on:click="confirmDelete = false" />
                                                        <x-botones.icono-buton color="red" icon="user-menos" label="Eliminar usuario"
                                                                                 texto="Eliminar usuario" type="submit" />
                                                    </form>
                                                </x-slot:actions>
                                            </x-modales.confirmacion>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
