<?php

namespace Tests\Feature;

use App\Livewire\ClientForm;
use App\Livewire\ClientList;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use App\Models\WhatsAppMessage;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClientManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_clients_screen_filters_and_updates_clients(): void
    {
        Carbon::setTestNow('2026-06-22 10:00:00');

        Client::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Pérez',
            'telefono' => '600123123',
        ]);

        Carbon::setTestNow('2026-06-23 11:15:00');

        Client::query()->create([
            'nombre' => 'Luis',
            'apellidos' => 'Gómez',
            'telefono' => '699999999',
        ]);

        Livewire::test(ClientList::class)
            ->set('filter_nombre', 'Ana')
            ->assertSee('Ana Pérez')
            ->assertDontSee('Luis Gómez');

        Carbon::setTestNow();
    }

    public function test_client_list_searches_after_one_character(): void
    {
        $client = Client::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Pérez',
            'telefono' => '600123123',
        ]);

        $component = Livewire::test(ClientList::class)
            ->assertSee('Las coincidencias aparecerán aquí')
            ->assertDontSee('Ana Pérez');

        $this->assertFalse($component->instance()->getHasClientSearchProperty());

        $component->set('filter_nombre', 'A')
            ->assertSee('Ana Pérez')
            ->assertSee('600123123')
            ->assertSeeHtml('href="'.route('clients.appointments', $client).'"')
            ->assertSeeHtml('href="'.route('appointments.create', ['client' => $client->id]).'"');
        $this->assertTrue($component->instance()->getHasClientSearchProperty());
    }

    public function test_client_list_orders_by_name(): void
    {
        $marta = Client::query()->create([
            'nombre' => 'Marta',
            'apellidos' => 'Gómez',
            'telefono' => '699999999',
        ]);
        $ana = Client::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Pérez',
            'telefono' => '600123123',
        ]);

        Livewire::test(ClientList::class)
            ->set('filter_nombre', 'a')
            ->assertSeeHtmlInOrder([
                'wire:key="client-'.$ana->id.'"',
                'wire:key="client-'.$marta->id.'"',
            ])
            ->call('sortByName')
            ->assertSeeHtmlInOrder([
                'wire:key="client-'.$marta->id.'"',
                'wire:key="client-'.$ana->id.'"',
            ]);
    }

    public function test_client_deletion_uses_a_confirmation_modal(): void
    {
        $client = Client::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Pérez',
            'telefono' => '600123123',
        ]);

        Livewire::test(ClientList::class)
            ->set('filter_nombre', 'Ana')
            ->call('confirmDelete', $client->id)
            ->assertSee('Eliminar cliente')
            ->assertSee('Ana Pérez')
            ->assertSeeHtml('aria-label="Cancelar"')
            ->assertSeeHtml('aria-label="Eliminar cliente"')
            ->assertSee('Esta acción no se puede deshacer.')
            ->call('cancelDelete')
            ->assertSet('clientPendingDeletionId', null)
            ->call('confirmDelete', $client->id)
            ->assertSet('clientPendingDeletionId', $client->id)
            ->call('deleteConfirmed')
            ->assertSet('clientPendingDeletionId', null);

        $this->assertSoftDeleted($client);
    }

    public function test_clients_screen_can_edit_selected_client(): void
    {
        Carbon::setTestNow('2026-06-22 10:00:00');

        $client = Client::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Pérez',
            'telefono' => '600123123',
        ]);

        Livewire::test(ClientForm::class, ['client' => $client->id])
            ->set('nombre', 'Ana Maria')
            ->set('apellidos', 'Pérez López')
            ->set('telefono', '611222333')
            ->call('save')
            ->assertSee('Cliente actualizado correctamente.');

        $client->refresh();

        $this->assertSame('Ana Maria', $client->nombre);
        $this->assertSame('Pérez López', $client->apellidos);
        $this->assertSame('611222333', $client->telefono);
        $this->assertSame('2026-06-22', $client->created_at->toDateString());

        Carbon::setTestNow();
    }

    public function test_clients_screen_can_create_client(): void
    {
        Livewire::test(ClientForm::class)
            ->set('nombre', 'Marta')
            ->set('apellidos', 'Soler')
            ->set('telefono', '600111222')
            ->call('save');

        $this->assertSame(1, Client::query()->count());
        $this->assertDatabaseHas('clients', [
            'nombre' => 'Marta',
            'apellidos' => 'Soler',
            'telefono' => '600111222',
        ]);
    }

    public function test_clients_screen_does_not_duplicate_an_existing_client_with_a_normalized_phone(): void
    {
        $existing = Client::query()->create([
            'nombre' => 'Marta',
            'apellidos' => 'Soler',
            'telefono' => '600111222',
        ]);

        Livewire::test(ClientForm::class)
            ->set('nombre', 'Marta')
            ->set('apellidos', 'Soler')
            ->set('telefono', '600111222')
            ->call('save')
            ->assertSet('selectedClientId', $existing->id);

        $this->assertSame(1, Client::query()->count());
        $this->assertSame($existing->id, Client::query()->firstOrFail()->id);
    }

    public function test_clients_page_can_open_selected_client_from_query_string(): void
    {
        $admin = User::factory()->create();
        Carbon::setTestNow('2026-06-25 12:40:00');
        $client = Client::query()->create([
            'nombre' => 'Lucía',
            'apellidos' => 'Martín',
            'telefono' => '+34666777888',
        ]);

        $this->actingAs($admin)
            ->get(route('clients.edit', $client))
            ->assertOk()
            ->assertSee('Editar cliente')
            ->assertDontSee('Nueva cita')
            ->assertDontSee('client-form-appointment-');

        Carbon::setTestNow();
    }

    public function test_clients_list_page_displays_clients(): void
    {
        $admin = User::factory()->create();

        $client = Client::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Pérez',
            'telefono' => '600123123',
        ]);

        $this->actingAs($admin)
            ->get(route('clients.list'))
            ->assertOk()
            ->assertSee('Listado de clientes')
            ->assertSee('Ana Pérez')
            ->assertSeeHtml('href="'.route('clients.appointments', $client).'"')
            ->assertSeeHtml('href="'.route('appointments.create', ['client' => $client->id]).'"')
            ->assertSee('Citas')
            ->assertSee('Nuevo cliente');
    }

    public function test_selected_client_edit_page_does_not_show_appointments(): void
    {
        Carbon::setTestNow('2026-06-23 09:00:00');

        $client = Client::query()->create([
            'nombre' => 'Lucía',
            'apellidos' => 'Martín',
            'telefono' => '+34666777888',
        ]);

        $laterAppointment = Appointment::query()->create([
            'client_id' => $client->id,
            'fecha' => '2026-07-01',
            'hora' => '10:15',
            'enviado' => false,
            'activo' => true,
        ]);
        $earlierAppointment = Appointment::query()->create([
            'client_id' => $client->id,
            'fecha' => '2026-06-30',
            'hora' => '09:00',
            'enviado' => false,
            'activo' => true,
        ]);

        Livewire::test(ClientForm::class, ['client' => $client->id])
            ->assertSee('Editar cliente')
            ->assertDontSee('Citas')
            ->assertDontSee('01/07/2026')
            ->assertDontSee('10:15')
            ->assertDontSee('Pendiente');

        Carbon::setTestNow();
    }

    public function test_selected_client_edit_page_does_not_show_appointment_actions(): void
    {
        Carbon::setTestNow('2026-06-23 09:00:00');

        $client = Client::query()->create([
            'nombre' => 'Lucía',
            'apellidos' => 'Martín',
            'telefono' => '+34666777888',
        ]);

        Appointment::query()->create([
            'client_id' => $client->id,
            'fecha' => '2026-06-01',
            'hora' => '09:00',
            'enviado' => false,
            'activo' => true,
        ]);
        Appointment::query()->create([
            'client_id' => $client->id,
            'fecha' => '2026-07-01',
            'hora' => '09:00',
            'enviado' => true,
            'activo' => true,
        ]);
        Appointment::query()->create([
            'client_id' => $client->id,
            'fecha' => '2026-07-02',
            'hora' => '09:00',
            'enviado' => false,
            'activo' => true,
        ]);
        Appointment::query()->create([
            'client_id' => $client->id,
            'fecha' => '2026-07-03',
            'hora' => '09:00',
            'enviado' => false,
            'activo' => false,
        ]);

        Livewire::test(ClientForm::class, ['client' => $client->id])
            ->assertSee('Editar cliente')
            ->assertDontSee('No Enviado!')
            ->assertDontSee('Enviado')
            ->assertDontSee('Pendiente')
            ->assertDontSee('Inactivo');

        Carbon::setTestNow();
    }

    public function test_selected_client_card_can_update_future_unsent_appointment_active_status(): void
    {
        Carbon::setTestNow('2026-06-23 09:00:00');

        $client = Client::query()->create([
            'nombre' => 'Lucía',
            'apellidos' => 'Martín',
            'telefono' => '+34666777888',
        ]);

        $appointment = Appointment::query()->create([
            'client_id' => $client->id,
            'fecha' => '2026-07-01',
            'hora' => '10:15',
            'enviado' => false,
            'activo' => true,
        ]);

        WhatsAppMessage::query()->create([
            'client_id' => $client->id,
            'appointment_id' => $appointment->id,
            'nombre' => 'Lucía',
            'apellidos' => 'Martín',
            'telefono' => '+34666777888',
            'scheduled_for' => now()->addDay(),
            'message' => 'Recordatorio',
            'source' => WhatsAppMessage::SOURCE_APPOINTMENT,
            'status' => WhatsAppMessage::STATUS_PENDING,
        ]);

        Livewire::test(ClientForm::class, ['client' => $client->id])
            ->call('updateAppointmentActiveStatus', $appointment->id, false)
            ->assertDispatched('toast', fn ($n, $p) => $p['message'] === 'Estado activo actualizado.' && $p['type'] === 'success');

        $this->assertFalse($appointment->refresh()->activo);
        $this->assertSame(0, WhatsAppMessage::query()->where('appointment_id', $appointment->id)->count());

        Carbon::setTestNow();
    }

    public function test_selected_client_card_deletes_locked_appointment(): void
    {
        Carbon::setTestNow('2026-06-23 09:00:00');

        $client = Client::query()->create([
            'nombre' => 'Lucía',
            'apellidos' => 'Martín',
            'telefono' => '+34666777888',
        ]);

        $appointment = Appointment::query()->create([
            'client_id' => $client->id,
            'fecha' => '2026-07-01',
            'hora' => '10:15',
            'enviado' => true,
            'activo' => true,
        ]);

        Livewire::test(ClientForm::class, ['client' => $client->id])
            ->call('deleteAppointment', $appointment->id)
            ->assertSee('Cita eliminada correctamente.');

        $this->assertDatabaseMissing('appointments', [
            'id' => $appointment->id,
        ]);

        Carbon::setTestNow();
    }

    public function test_client_list_page_is_separate_from_client_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('clients.index'))
            ->assertOk()
            ->assertSee('Clientes')
            ->assertSee('Nuevo cliente')
            ->assertDontSee('Crear cliente');
    }
}
