<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\WhatsAppMessage;
use App\Services\WhatsApp\AppointmentDeliveryStatusSyncer;
use App\Services\WhatsApp\AppointmentImmediateSender;
use App\Services\WhatsApp\WhatsAppSender;
use Livewire\Component;

class ClientForm extends Component
{
    public ?int $selectedClientId = null;

    public string $nombre = '';

    public string $apellidos = '';

    public string $telefono = '';

    private AppointmentImmediateSender $immediateSender;

    private AppointmentDeliveryStatusSyncer $deliveryStatusSyncer;

    private bool $skipDeliverySync = false;

    public function boot(AppointmentImmediateSender $immediateSender, AppointmentDeliveryStatusSyncer $deliveryStatusSyncer): void
    {
        $this->immediateSender = $immediateSender;
        $this->deliveryStatusSyncer = $deliveryStatusSyncer;
    }

    public function mount(?int $client = null): void
    {
        $clientId = $client ?: (int) request()->route('client');

        if ($clientId > 0) {
            $this->loadClient($clientId);

            return;
        }

        $queryClientId = request()->integer('client');

        if ($queryClientId > 0) {
            $this->loadClient($queryClientId);
        }
    }

    public function save(): void
    {
        $data = $this->validate();

        $payload = [
            'nombre' => $data['nombre'],
            'apellidos' => $data['apellidos'],
            'telefono' => Client::normalizePhone($data['telefono']),
        ];

        if ($this->selectedClientId) {
            Client::query()->whereKey($this->selectedClientId)->update($payload);
            session()->flash('status', 'Cliente actualizado correctamente.');
            $this->redirect(url()->previous());
        } else {
            $client = Client::upsertFromImport($payload);

            $this->selectedClientId = $client->id;
            session()->flash('status', $client->wasRecentlyCreated ? 'Cliente creado correctamente.' : 'Cliente ya existente; no se ha duplicado.');
            $this->redirect(url()->previous());
        }
    }

    public function updateAppointmentActiveStatus(int $appointmentId, bool|string $activo): void
    {
        if (is_bool($activo)) {
            $isActive = $activo;
        } elseif (in_array($activo, ['0', '1'], true)) {
            $isActive = $activo === '1';
        } else {
            return;
        }

        $appointment = Appointment::query()
            ->where('client_id', $this->selectedClientId)
            ->findOrFail($appointmentId);

        if (! $appointment->canBeChanged()) {
            $this->dispatch('toast', message: 'Esta cita no se puede modificar. Solo se puede eliminar.', type: 'error');

            return;
        }

        $appointment->update([
            'activo' => $isActive,
        ]);

        if (! $isActive) {
            $appointment->whatsAppMessages()
                ->where('status', WhatsAppMessage::STATUS_PENDING)
                ->delete();
        }

        $this->dispatch('toast', message: 'Estado activo actualizado.', type: 'success');
    }

    public function deleteAppointment(int $appointmentId): void
    {
        Appointment::query()
            ->where('client_id', $this->selectedClientId)
            ->whereKey($appointmentId)
            ->delete();

        session()->flash('status', 'Cita eliminada correctamente.');

        $this->redirect(url()->previous());
    }

    public function sendAppointmentNow(int $appointmentId, WhatsAppSender $sender): void
    {
        $appointment = Appointment::query()
            ->with('client')
            ->where('client_id', $this->selectedClientId)
            ->findOrFail($appointmentId);

        if ($appointment->enviado) {
            session()->flash('status', 'Esta cita ya tiene el WhatsApp enviado.');
            $this->redirect(url()->previous());

            return;
        }

        if (! $appointment->isFuture()) {
            session()->flash('status', 'Las citas pasadas no pueden enviarse.');
            $this->redirect(url()->previous());

            return;
        }

        if (! $appointment->activo) {
            session()->flash('status', 'Las citas inactivas no pueden enviarse.');
            $this->redirect(url()->previous());

            return;
        }

        $client = $appointment->client;

        if (! $client) {
            session()->flash('status', 'No se pudo enviar el WhatsApp porque la cita no tiene cliente asociado.');
            $this->redirect(url()->previous());

            return;
        }

        $result = $this->immediateSender->send(
            $appointment,
            $client,
            $sender,
            'WhatsApp enviado ahora correctamente.',
            'No se pudo enviar el WhatsApp.'
        );

        $this->skipDeliverySync = true;

        session()->flash('status', $result['message']);

        $this->redirect(url()->previous());
    }

    public function getSelectedClientProperty(): ?Client
    {
        if (! $this->selectedClientId) {
            return null;
        }

        return Client::query()->find($this->selectedClientId);
    }

    public function render()
    {
        return view('livewire.client-form', [
            'selectedClient' => $this->selectedClient,
        ]);
    }

    protected function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:40'],
        ];
    }

    private function loadClient(int $clientId): void
    {
        $client = Client::query()->findOrFail($clientId);

        $this->selectedClientId = $client->id;
        $this->nombre = $client->nombre;
        $this->apellidos = $client->apellidos;
        $this->telefono = $client->telefono;
    }
}
