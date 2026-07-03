<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\User;

class LoginHistoryController extends Controller
{
    public function index()
    {
        $logins = LoginHistory::with('user')
            ->latest('logged_in_at')
            ->paginate(50);

        $users = User::orderBy('name')->get();

        return view('admin.login-history.index', [
            'logins' => $logins,
            'users' => $users,
        ]);
    }
}
