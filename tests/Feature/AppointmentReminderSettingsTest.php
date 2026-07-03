<?php

namespace Tests\Feature;

use App\Livewire\AppointmentReminderSettings;
use App\Models\AppointmentReminderPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AppointmentReminderSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminder_settings_can_save_whatsapp_and_email_lead_days(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(AppointmentReminderSettings::class)
            ->assertSet('whatsappLeadDays', [1])
            ->assertSet('emailLeadDays', [])
            ->set('whatsappLeadDays', [1, 3, 7])
            ->set('emailLeadDays', [2, 7])
            ->call('save')
            ->assertSet('status', 'Preferencias de recordatorios guardadas.');

        $this->assertSame(
            [1, 3, 7],
            AppointmentReminderPreference::enabledLeadDaysFor(AppointmentReminderPreference::CHANNEL_WHATSAPP),
        );

        $this->assertSame(
            [2, 7],
            AppointmentReminderPreference::enabledLeadDaysFor(AppointmentReminderPreference::CHANNEL_EMAIL),
        );
    }

    public function test_settings_page_shows_reminder_selection(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Recordatorios')
            ->assertSee('1 día antes')
            ->assertSee('1 semana antes')
            ->assertSee('Guardar');
    }
}
