<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentIndexController
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $clientId = $request->integer('client');

        return $clientId > 0
            ? redirect()->route('clients.appointments', $clientId)
            : view('appointments.index');
    }
}
