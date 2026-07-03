<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function create()
    {
        $users = User::query()->orderBy('id')->get();

        return view('admin.users.create', [
            'users' => $users,
            'adminCount' => $users->where('is_admin', true)->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(4)],
            'is_admin' => ['boolean'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => (bool) ($data['is_admin'] ?? false),
        ]);

        return redirect()->route('admin.users.create')->with('status', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user,
            'adminRoleLocked' => $user->is_admin && (int) Auth::id() === (int) $user->id,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'confirmed', Password::min(12)],
            'is_admin' => ['boolean'],
        ]);
        $isAdmin = (bool) ($data['is_admin'] ?? false);

        abort_if($user->is_admin && ! $isAdmin && (int) Auth::id() === (int) $user->id, 422, 'Otro administrador debe retirarte el rol.');

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->is_admin = $isAdmin;

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('admin.users.create')->with('status', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless((int) Auth::id() !== (int) $user->id, 422, 'No puedes eliminar tu propia cuenta.');
        abort_if($user->is_admin && User::query()->where('is_admin', true)->count() === 1, 422, 'No puedes eliminar al último administrador.');

        $user->delete();

        return redirect()->route('admin.users.create')->with('status', 'Usuario eliminado correctamente.');
    }
}
