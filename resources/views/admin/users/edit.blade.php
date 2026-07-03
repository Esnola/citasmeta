@extends('layouts.app')
@section('content')
    <div class="grid gap-6">
        <div class="rounded-3xl lg:max-w-2xl border border-white/10 bg-white/5 p-6 backdrop-blur">
          <h2 class="text-xl ">Editar usuario:&nbsp; <span class="font-semibold">{{ $user->name }}</span></h2>
            <form class="mt-6 grid gap-4 sm:grid-cols-2" method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-2 block text-sm text-slate-300">Nombre</label>
                    <x-formularios.input name="name" value="{{ old('name', $user->name) }}" required autofocus />
                    @error('name') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm text-slate-300">Email</label>
                    <x-formularios.input name="email" type="email" value="{{ old('email', $user->email) }}" required />
                    @error('email') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm text-slate-300">Nueva contraseña</label>
                    <x-formularios.input name="password" type="password" />
                    @error('password') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm text-slate-300">Confirmar nueva contraseña</label>
                    <x-formularios.input name="password_confirmation" type="password" />
                </div>
                <div class="flex flex-col gap-4 pt-2">
                  @if ($adminRoleLocked)
                    <input type="hidden" name="is_admin" value="1">
                  @endif
                  <flux:checkbox value="1" name="is_admin" label="Administrador"
                  :disabled="$adminRoleLocked" :checked="old('is_admin',$user->is_admin)" />
                  @if ($adminRoleLocked)
                    <span class="text-red-400 text-xs">No puedes auto-desvincularte co.</span>
                  @endif
                  <div class="flex gap-4 justify-end mt-12">

                    <x-botones.icono-buton
                            type="submit"
                            icon="disquete"
                            especial="size-5"
                            label="Guardar Usuario"
                            texto="Guardar" />

                    <x-botones.icono-buton
                            color="amber"
                            icon="salir"
                            especial="size-5"
                            label="Guardar Usuario"
                            texto="Volver"
                            onclick="window.location.href='{{ route('admin.users.create') }}'" />
                  </div>
                </div>
            </form>
        </div>
    </div>
@endsection
