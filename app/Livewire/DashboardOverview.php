<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\WhatsAppMessage;
use Illuminate\View\View;
use Livewire\Component;

class DashboardOverview extends Component
{
    public function render(): View
    {
        return view('livewire.dashboard-overview', [
            'totales' => Appointment::count(),
            'pendingCount' => Appointment::with('client')
                ->where('activo', true)
                ->where('whatsapp_sent_at', null)
                ->where('fecha', '>', now())
                ->count(),
            'caducados' => Appointment::with('client')
                ->where('activo', true)
                ->where('whatsapp_sent_at', null)
                ->where('entregado', false)
                ->where('fecha', '<', now())->count(),
            'cancelados' => Appointment::where('activo', false)
                ->where('whatsapp_sent_at', null)
                ->where('entregado', false)
                ->count(),
            'sentCount' => WhatsAppMessage::where('status', WhatsAppMessage::STATUS_SENT)->count(),
            'failedCount' => WhatsAppMessage::where('status', WhatsAppMessage::STATUS_FAILED)->count(),
        ]);
    }
}
