<?php

namespace App\Livewire;

use App\Jobs\SendWhatsAppMessage;
use App\Models\Client;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsApp\WhatsAppSender;
use App\Traits\ValidatesSelectableDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ClientMessageScheduler extends Component
{
    use ValidatesSelectableDate, WithPagination;

    public string $filter_nombre = '';

    public string $filter_apellidos = '';

    public string $filter_telefono = '';

    public ?int $selectedClientId = null;

    public string $scheduled_date = '';

    public string $scheduled_time = '';

    public string $template_key = '';

    public string $status = '';

    public string $statusType = 'success';

    public function mount(): void
    {
        $this->template_key = WhatsAppTemplate::defaultKey();

        $clientId = request()->integer('client');

        if ($clientId > 0) {
            $this->selectClient($clientId);
        }
    }

    public function updatedFilterNombre(): void
    {
        $this->resetPage('clientsPage');
    }

    public function updatedFilterApellidos(): void
    {
        $this->resetPage('clientsPage');
    }

    public function updatedFilterTelefono(): void
    {
        $this->resetPage('clientsPage');
    }

    public function selectClient(int $clientId): void
    {
        $client = Client::query()->findOrFail($clientId);

        $this->selectedClientId = $client->id;
        $this->scheduled_date = $this->nextSelectableDate();
        $this->scheduled_time = now()->format('H:i');
        $this->template_key = $this->template_key ?: WhatsAppTemplate::defaultKey();
    }

    public function clearSelection(): void
    {
        $this->selectedClientId = null;
        $this->scheduled_date = '';
        $this->scheduled_time = '';
        $this->resetValidation();
    }

    public function save(): void
    {
        $data = $this->validate();
        $this->validateSelectableDate($data['scheduled_date'], 'scheduled_date');

        $client = Client::query()->findOrFail($data['selectedClientId']);
        $scheduledFor = Carbon::parse("{$data['scheduled_date']} {$data['scheduled_time']}");

        $this->createManualMessage($client, $scheduledFor, $data['template_key']);

        $this->clearSelection();
        $this->template_key = WhatsAppTemplate::defaultKey();

        $this->statusType = 'success';
        $this->status = 'Mensaje programado desde la ficha del cliente.';
    }

    public function sendNow(WhatsAppSender $sender): void
    {
        $data = $this->validate();
        $this->validateSelectableDate($data['scheduled_date'], 'scheduled_date');

        $client = Client::query()->findOrFail($data['selectedClientId']);
        $scheduledFor = Carbon::parse("{$data['scheduled_date']} {$data['scheduled_time']}");
        $message = $this->createManualMessage($client, $scheduledFor, $data['template_key'], [
            'immediate_send' => true,
            'immediate_sent_at' => now()->toDateTimeString(),
        ]);

        SendWhatsAppMessage::dispatchSync($message->id);

        $message->refresh();

        if ($message->status === WhatsAppMessage::STATUS_SENT) {
            $this->statusType = 'success';
            $this->status = 'WhatsApp enviado ahora y registrado correctamente.';
        } else {
            $this->statusType = 'error';
            $this->status = 'No se pudo enviar el WhatsApp. El intento ha quedado registrado como fallido.';
        }
    }

    public function render()
    {
        $clients = Client::query()
            ->when($this->filter_nombre, fn ($query) => $query->where('nombre', 'like', '%'.$this->filter_nombre.'%'))
            ->when($this->filter_apellidos, fn ($query) => $query->where('apellidos', 'like', '%'.$this->filter_apellidos.'%'))
            ->when($this->filter_telefono, fn ($query) => $query->where('telefono', 'like', '%'.$this->filter_telefono.'%'))
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'clientsPage');

        $selectedClient = $this->selectedClientId
            ? Client::query()->find($this->selectedClientId)
            : null;

        return view('livewire.client-message-scheduler', [
            'clients' => $clients,
            'selectedClient' => $selectedClient,
            'templateOptions' => WhatsAppMessage::templateOptions(),
            'previewMessage' => $selectedClient
                ? WhatsAppMessage::buildMessage([
                    'nombre' => $selectedClient->nombre,
                    'apellidos' => $selectedClient->apellidos,
                    'telefono' => $selectedClient->telefono,
                    'scheduled_for' => Carbon::parse(
                        ($this->scheduled_date ?: now()->toDateString()).' '.($this->scheduled_time ?: now()->format('H:i'))
                    ),
                ], $this->template_key ?: config('whatsapp.default_template'))
                : 'Selecciona un cliente para generar su cita.',
            'minimumSelectableDate' => now()->addDay()->toDateString(),
        ]);
    }

    protected function rules(): array
    {
        $templateKeys = implode(',', array_column(WhatsAppMessage::templateOptions(), 'key'));

        return [
            'selectedClientId' => ['required', 'integer', 'exists:clients,id'],
            'scheduled_date' => ['required', Rule::date()->afterToday()],
            'scheduled_time' => ['required', 'date_format:H:i'],
            'template_key' => ['required', 'in:'.$templateKeys],
        ];
    }

    protected function messages(): array
    {
        return [
            'scheduled_date.after' => 'La fecha debe ser posterior a hoy.',
        ];
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function createManualMessage(Client $client, Carbon $scheduledFor, string $templateKey, array $metadata = []): WhatsAppMessage
    {
        $message = WhatsAppMessage::buildMessage([
            'nombre' => $client->nombre,
            'apellidos' => $client->apellidos,
            'telefono' => $client->telefono,
            'scheduled_for' => $scheduledFor,
        ], $templateKey);

        return WhatsAppMessage::create([
            'user_id' => Auth::id(),
            'client_id' => $client->id,
            'nombre' => $client->nombre,
            'apellidos' => $client->apellidos,
            'telefono' => $client->telefono,
            'scheduled_for' => $scheduledFor,
            'message' => $message,
            'source' => WhatsAppMessage::SOURCE_MANUAL,
            'status' => WhatsAppMessage::STATUS_PENDING,
            'metadata' => [
                'template_key' => $templateKey,
                'origin_client_id' => $client->id,
                ...$metadata,
            ],
        ]);
    }

    private function nextSelectableDate(): string
    {
        $date = now()->addDay();

        while ($date->isSunday()) {
            $date->addDay();
        }

        return $date->toDateString();
    }
}
