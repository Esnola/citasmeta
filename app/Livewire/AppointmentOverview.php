<?php

namespace App\Livewire;

use App\Models\Client;
use App\Services\WhatsApp\AppointmentDeliveryStatusSyncer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class AppointmentOverview extends Component
{
    use WithPagination;

    public string $filter_nombre = '';

    public string $filter_apellidos = '';

    public bool $filter_enviado = false;

    public bool $filter_activo = false;

    public bool $filter_entregado = false;

    public ?string $deliveryStatusesSyncedAt = null;

    public function mount(): void
    {
        $this->deliveryStatusesSyncedAt = Cache::get('appointment_delivery_statuses_synced_at');
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['filter_enviado', 'filter_activo', 'filter_entregado'], true) && $this->{$property}) {
            foreach (['filter_enviado', 'filter_activo', 'filter_entregado'] as $filter) {
                $this->{$filter} = $filter === $property;
            }
        }

        $this->resetPage('clientsPage');
    }

    public function syncDeliveryStatuses(AppointmentDeliveryStatusSyncer $syncer): void
    {
        $updated = $syncer->syncAll(force: true);
        $this->deliveryStatusesSyncedAt = now(config('app.timezone'))->format('H:i - d/m/Y');
        Cache::forever('appointment_delivery_statuses_synced_at', $this->deliveryStatusesSyncedAt);

        session()->flash('status', $updated > 0
            ? ($updated === 1 ? 'Se ha actualizado 1 cita' : 'Se han actualizado '.$updated.' citas')
            : 'Todos los registros de citas y demás datos están actualizados.');
    }

    public function render()
    {
        $now = Carbon::now(config('app.timezone'));
        $appointmentFilter = fn (Builder|HasMany $query) => $this->filterAppointments($query, $now);

        $clients = Client::query()
            ->when($this->filter_nombre, fn (Builder $query) => $query->where('nombre', 'like', '%'.$this->filter_nombre.'%'))
            ->when($this->filter_apellidos, fn (Builder $query) => $query->where('apellidos', 'like', '%'.$this->filter_apellidos.'%'))
            ->whereHas('appointments', $appointmentFilter)
            ->withCount(['appointments as appointments_count' => $appointmentFilter])
            ->with(['appointments' => fn (Builder|HasMany $query) => $appointmentFilter($query)
                ->orderBy('fecha')
                ->orderBy('hora')
                ->limit(1)])
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->paginate(30, pageName: 'clientsPage');

        return view('livewire.appointment-overview', [
            'clients' => $clients,
        ]);
    }

    private function filterAppointments(Builder|HasMany $query, Carbon $now): Builder|HasMany
    {
        return $query
            ->when($this->filter_entregado, fn (Builder $query) => $query->where('entregado', true))
            ->when($this->filter_enviado, fn (Builder $query) => $query->where('enviado', true))
            ->when($this->filter_activo, fn (Builder $query) => $query->where('activo', false))
            ->when(! $this->filter_entregado && ! $this->filter_enviado && ! $this->filter_activo, function (Builder $query) use ($now): void {
                $query->where('cita_activa', true)
                    ->whereDate('fecha', '>=', $now->toDateString());
            });
    }
}
