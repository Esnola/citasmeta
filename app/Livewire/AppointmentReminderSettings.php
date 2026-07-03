<?php

namespace App\Livewire;

use App\Models\AppointmentReminderPreference;
use Livewire\Component;

class AppointmentReminderSettings extends Component
{
    /**
     * @var list<int>
     */
    public array $whatsappLeadDays = [];

    /**
     * @var list<int>
     */
    public array $emailLeadDays = [];

    public string $status = '';

    public function mount(): void
    {
        $selections = AppointmentReminderPreference::selections();

        $this->whatsappLeadDays = $selections[AppointmentReminderPreference::CHANNEL_WHATSAPP] ?? [];
        $this->emailLeadDays = $selections[AppointmentReminderPreference::CHANNEL_EMAIL] ?? [];
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->is_admin, 403);

        $data = $this->validate();

        AppointmentReminderPreference::saveSelections([
            AppointmentReminderPreference::CHANNEL_WHATSAPP => $data['whatsappLeadDays'],
            AppointmentReminderPreference::CHANNEL_EMAIL => $data['emailLeadDays'],
        ]);

        $this->whatsappLeadDays = AppointmentReminderPreference::enabledLeadDaysFor(AppointmentReminderPreference::CHANNEL_WHATSAPP);
        $this->emailLeadDays = AppointmentReminderPreference::enabledLeadDaysFor(AppointmentReminderPreference::CHANNEL_EMAIL);
        $this->status = 'Preferencias de recordatorios guardadas.';
    }

    public function render()
    {
        return view('livewire.appointment-reminder-settings', [
            'leadDayOptions' => AppointmentReminderPreference::leadDayOptions(),
        ]);
    }

    protected function rules(): array
    {
        $allowedLeadDays = implode(',', array_keys(AppointmentReminderPreference::leadDayOptions()));

        return [
            'whatsappLeadDays' => ['array'],
            'whatsappLeadDays.*' => ['integer', 'in:'.$allowedLeadDays],
            'emailLeadDays' => ['array'],
            'emailLeadDays.*' => ['integer', 'in:'.$allowedLeadDays],
        ];
    }
}
