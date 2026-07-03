<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\WhatsAppMessage;
use App\Services\WhatsApp\AppointmentDeliveryStatusSyncer;
use App\Services\WhatsApp\AppointmentImmediateSender;
use App\Services\WhatsApp\WhatsAppSender;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class AppointmentList extends Component
{
    use WithPagination;

    public string $filter_nombre = '';

    public string $filter_apellidos = '';

    public bool $show_filters_nombre = true;

    public bool $filter_enviado = false;

    public bool $filter_activo = false;

    public bool $filter_entregado = false;

    public bool $showAllHistory = false;

    public bool $sentOnly = false;

    public bool $showAppointmentNavigation = false;

    public string $dateFilter = 'upcoming';

    public string $sort_by = 'fecha';

    public string $sort_direction = 'asc';

    public ?int $clientId = null;

    public ?int $appointmentPendingDeletionId = null;

    public ?int $appointmentPendingResendId = null;

    /** @var array<int, int|string> */
    public array $selectedAppointmentIds = [];

    public bool $bulkDeleteConfirmationOpen = false;

    public ?string $deliveryStatusesSyncedAt = null;

    private AppointmentImmediateSender $immediateSender;

    private AppointmentDeliveryStatusSyncer $deliveryStatusSyncer;

    public function boot(AppointmentImmediateSender $immediateSender, AppointmentDeliveryStatusSyncer $deliveryStatusSyncer): void
    {
        $this->immediateSender = $immediateSender;
        $this->deliveryStatusSyncer = $deliveryStatusSyncer;
    }

    public function mount(?int $clientId = null): void
    {
        $clientId ??= request()->integer('client');

        $this->showAppointmentNavigation = (request()->routeIs('appointments.index') || request()->routeIs('appointments.sent'))
          && request()->query() === [];

        if ($clientId > 0) {
            abort_unless(Client::query()->whereKey($clientId)->exists(), 404);

            $this->clientId = $clientId;
            $this->show_filters_nombre = false;
        }

        if (request()->routeIs('appointments.sent')) {
            $this->sentOnly = true;
            $this->filter_enviado = true;
            $this->filter_activo = false;
            $this->filter_entregado = false;
        }

        $this->deliveryStatusesSyncedAt = Cache::get('appointment_delivery_statuses_synced_at');
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['filter_nombre', 'filter_apellidos'], true)) {
            $this->resetAppointmentsPage();

            return;
        }

        if ($property === 'dateFilter') {
            if (! in_array($this->dateFilter, ['upcoming', 'all', 'past'], true)) {
                $this->dateFilter = 'upcoming';
            }

            $this->selectedAppointmentIds = [];
            $this->bulkDeleteConfirmationOpen = false;
            $this->resetAppointmentsPage();

            return;
        }

        if (in_array($property, ['filter_enviado', 'filter_activo', 'filter_entregado'], true)) {
            $this->selectedAppointmentIds = [];
            $this->bulkDeleteConfirmationOpen = false;
            $this->syncExclusiveDeliveryFilters($property);
            $this->resetAppointmentsPage();

            return;
        }

        if ($property === 'showAllHistory') {
            if ($this->showAllHistory) {
                $this->filter_enviado = false;
                $this->filter_entregado = false;
                $this->filter_activo = false;
                $this->dateFilter = 'all';
            }

            $this->resetAppointmentsPage();

            return;
        }

        if ($property === 'sort_by') {
            if (! in_array($this->sort_by, ['cliente', 'fecha'], true)) {
                $this->sort_by = 'fecha';
            }

            $this->resetAppointmentsPage();

            return;
        }

        if ($property === 'sort_direction') {
            if (! in_array($this->sort_direction, ['asc', 'desc'], true)) {
                $this->sort_direction = 'asc';
            }

            $this->resetAppointmentsPage();
        }
    }

    private function forceDeliveryStatusSync(): int
    {
        $updated = $this->deliveryStatusSyncer->syncAll($this->clientId, force: true);
        $this->deliveryStatusesSyncedAt = now(config('app.timezone'))->format('H:i - d/m/Y');
        Cache::forever('appointment_delivery_statuses_synced_at', $this->deliveryStatusesSyncedAt);

        return $updated;
    }

    public function sortByColumn(string $column): void
    {
        if (! in_array($column, ['cliente', 'fecha'], true)) {
            return;
        }

        if ($this->sort_by === $column) {
            $this->sort_direction = $this->sort_direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort_by = $column;
            $this->sort_direction = 'asc';
        }
    }

    public function confirmDelete(int $appointmentId): void
    {
        $this->appointmentPendingDeletionId = $appointmentId;
    }

    public function cancelDelete(): void
    {
        $this->appointmentPendingDeletionId = null;
    }

    public function toggleVisibleAppointments(array $appointmentIds): void
    {
        $appointmentIds = array_values(array_unique(array_map('intval', $appointmentIds)));
        $selectedIds = array_values(array_unique(array_map('intval', $this->selectedAppointmentIds)));

        $this->selectedAppointmentIds = array_diff($appointmentIds, $selectedIds) === []
          ? array_values(array_diff($selectedIds, $appointmentIds))
          : array_values(array_unique([...$selectedIds, ...$appointmentIds]));
    }

    public function confirmBulkDelete(): void
    {
        $this->bulkDeleteConfirmationOpen = $this->selectedAppointmentIds !== [];
    }

    public function deleteSelected(): void
    {
        if (! $this->clientId || $this->selectedAppointmentIds === []) {
            return;
        }

        $deleted = Appointment::query()
            ->where('client_id', $this->clientId)
            ->whereKey(array_map('intval', $this->selectedAppointmentIds))
            ->delete();

        $this->selectedAppointmentIds = [];
        $this->bulkDeleteConfirmationOpen = false;

        $this->redirectAfterAction(sprintf('%d cita(s) eliminada(s) correctamente.', $deleted));
    }

    public function updateSelectedActiveStatus(bool $activo): void
    {
        if (! $this->clientId || $this->selectedAppointmentIds === []) {
            return;
        }

        $appointmentIds = Appointment::query()
            ->where('client_id', $this->clientId)
            ->whereKey(array_map('intval', $this->selectedAppointmentIds))
            ->pending()
            ->upcoming()
            ->pluck('id');

        Appointment::query()->whereKey($appointmentIds)->update(['activo' => $activo]);

        if (! $activo) {
            WhatsAppMessage::query()
                ->whereIn('appointment_id', $appointmentIds)
                ->where('status', WhatsAppMessage::STATUS_PENDING)
                ->delete();
        }

        $this->selectedAppointmentIds = [];

        $this->dispatch('toast', message: sprintf(
            '%d cita(s) %s correctamente.',
            $appointmentIds->count(),
            $activo ? 'activada(s)' : 'desactivada(s)'
        ), type: 'success');
    }

    public function deleteConfirmed(): void
    {
        if (! $this->appointmentPendingDeletionId) {
            return;
        }

        Appointment::query()->whereKey($this->appointmentPendingDeletionId)->delete();
        $this->appointmentPendingDeletionId = null;

        $this->redirectAfterAction('Cita eliminada correctamente.');
    }

    public function updateActiveStatus(int $appointmentId, bool|string $activo): void
    {
        if (is_bool($activo)) {
            $isActive = $activo;
        } elseif (in_array($activo, ['0', '1'], true)) {
            $isActive = $activo === '1';
        } else {
            return;
        }

        $appointment = Appointment::query()->findOrFail($appointmentId);

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

        $this->dispatch('toast', message: 'Estado pendiente actualizado.', type: 'success');
    }

    public function updateAppointmentActiveStatus(int $appointmentId, bool|string $citaActiva): void
    {
        if (is_bool($citaActiva)) {
            $isActive = $citaActiva;
        } elseif (in_array($citaActiva, ['0', '1'], true)) {
            $isActive = $citaActiva === '1';
        } else {
            return;
        }

        $appointment = Appointment::query()->findOrFail($appointmentId);

        if (! $appointment->canBeChanged()) {
            $this->dispatch('toast', message: 'Esta cita no se puede modificar. Solo se puede eliminar.', type: 'error');

            return;
        }

        $appointment->update(['cita_activa' => $isActive]);

        $this->dispatch('toast', message: 'Estado de la cita actualizado.', type: 'success');
    }

    public function sendNow(int $appointmentId, WhatsAppSender $sender): void
    {
        $appointment = Appointment::query()
            ->with('client')
            ->findOrFail($appointmentId);

        if ($appointment->enviado) {
            $this->dispatch('toast', message: 'Esta cita ya tiene el WhatsApp enviado.', type: 'error');

            return;
        }

        if (! $appointment->isFuture()) {
            $this->dispatch('toast', message: 'Las citas pasadas no pueden enviarse.', type: 'error');

            return;
        }

        if (! $appointment->activo) {
            $this->dispatch('toast', message: 'Las citas no pendientes no pueden enviarse.', type: 'error');

            return;
        }

        $client = $appointment->client;

        if (! $client) {
            $this->dispatch('toast', message: 'No se pudo enviar el WhatsApp porque la cita no tiene cliente asociado.', type: 'error');

            return;
        }

        $result = $this->immediateSender->send(
            $appointment,
            $client,
            $sender,
            'WhatsApp enviado ahora correctamente.',
            'No se pudo enviar el WhatsApp.'
        );

        $this->dispatch('toast', message: $result['message'], type: str_contains($result['message'], 'correctamente') ? 'success' : 'error');
    }

    public function confirmResend(int $appointmentId): void
    {
        $this->appointmentPendingResendId = $appointmentId;
    }

    public function cancelResend(): void
    {
        $this->appointmentPendingResendId = null;
    }

    public function resendConfirmed(WhatsAppSender $sender): void
    {
        if (! $this->appointmentPendingResendId) {
            return;
        }

        $appointment = Appointment::query()->with('client')->findOrFail($this->appointmentPendingResendId);

        if (! $appointment->enviado || ! $appointment->isFuture()) {
            $this->dispatch('toast', message: 'Esta cita no se puede reenviar.', type: 'error');

            return;
        }

        if (! $appointment->client) {
            $this->dispatch('toast', message: 'No se pudo reenviar el WhatsApp porque la cita no tiene cliente asociado.', type: 'error');

            return;
        }

        $result = $this->immediateSender->send(
            $appointment,
            $appointment->client,
            $sender,
            'WhatsApp reenviado correctamente.',
            'No se pudo reenviar el WhatsApp.'
        );

        $this->appointmentPendingResendId = null;
        $this->dispatch('toast', message: $result['message'], type: str_contains($result['message'], 'correctamente') ? 'success' : 'error');
    }

    public function syncDeliveryStatuses(): void
    {
        $updated = $this->forceDeliveryStatusSync();

        if ($updated > 0) {
            $this->dispatch('toast', message: $updated === 1 ? 'Se ha actualizado 1 cita' : 'Se han actualizado '.$updated.' citas', type: 'success');

            return;
        }

        $this->dispatch('toast', message: 'Todos los registros de citas y demás datos están actualizados.', type: 'success');
    }

    public function render()
    {
        $selectedClient = $this->selectedClient();
        $now = Carbon::now(config('app.timezone'));
        $appointmentsQuery = $this->appointmentsQuery($selectedClient, $now);

        $appointments = $appointmentsQuery->paginate(30, ['appointments.*'], 'appointmentsPage');

        $showBulkActions = ! $this->sentOnly;
        $visibleAppointmentIds = $appointments->getCollection()->pluck('id')->all();
        $allVisibleAppointmentsSelected = $visibleAppointmentIds !== []
          && array_diff($visibleAppointmentIds, array_map('intval', $this->selectedAppointmentIds)) === [];

        $appointmentPendingDeletion = $this->appointmentPendingDeletionId
          ? Appointment::query()->with('client')->find($this->appointmentPendingDeletionId)
          : null;

        $appointmentPendingResend = $this->appointmentPendingResendId
          ? Appointment::query()->with('client')->find($this->appointmentPendingResendId)
          : null;

        $appointmentsByClient = $selectedClient
          ? null
          : $appointments->getCollection()->groupBy(fn (Appointment $a) => $a->client_id)->map(function ($group) {
              return [
                  'appointments' => $group->values(),
                  'pendingCount' => $group->filter(fn (Appointment $a) => $a->cita_activa)->count(),
              ];
          });

        return view('livewire.appointment-list', [
            'appointments' => $appointments,
            'appointmentsCount' => $appointments->total(),
            'appointmentPendingDeletion' => $appointmentPendingDeletion,
            'appointmentPendingResend' => $appointmentPendingResend,
            'selectedClient' => $selectedClient,
            'sentOnly' => $this->sentOnly,
            'showBulkActions' => $showBulkActions,
            'visibleAppointmentIds' => $visibleAppointmentIds,
            'allVisibleAppointmentsSelected' => $allVisibleAppointmentsSelected,
            'appointmentsByClient' => $appointmentsByClient,
        ]);
    }

    private function whereFutureAppointment(Builder $query, Carbon $now): void
    {
        $query->where(function (Builder $futureQuery) use ($now): void {
            $futureQuery
                ->whereDate('appointments.fecha', '>', $now->toDateString())
                ->orWhere(function (Builder $sameDayQuery) use ($now): void {
                    $sameDayQuery
                        ->whereDate('appointments.fecha', $now->toDateString())
                        ->where('appointments.hora', '>', $now->format('H:i:s'));
                });
        });
    }

    private function wherePastAppointment(Builder $query, Carbon $now): void
    {
        $query->where(function (Builder $pastQuery) use ($now): void {
            $pastQuery
                ->whereDate('appointments.fecha', '<', $now->toDateString())
                ->orWhere(function (Builder $sameDayQuery) use ($now): void {
                    $sameDayQuery
                        ->whereDate('appointments.fecha', $now->toDateString())
                        ->where('appointments.hora', '<=', $now->format('H:i:s'));
                });
        });
    }

    private function resetAppointmentsPage(): void
    {
        $this->resetPage('appointmentsPage');
    }

    private function syncExclusiveDeliveryFilters(string $activeFilter): void
    {
        if ($activeFilter === 'filter_enviado' && $this->filter_enviado) {
            $this->filter_activo = false;
            $this->filter_entregado = false;
        }

        if ($activeFilter === 'filter_activo' && $this->filter_activo) {
            $this->filter_enviado = false;
            $this->filter_entregado = false;
        }

        if ($activeFilter === 'filter_entregado' && $this->filter_entregado) {
            $this->filter_enviado = false;
            $this->filter_activo = false;
        }
    }

    private function selectedClient(): ?Client
    {
        return $this->clientId ? Client::query()->find($this->clientId) : null;
    }

    private function redirectAfterAction(string $status): void
    {
        $client = $this->selectedClient();

        if ($client && ! $this->appointmentsQuery($client, Carbon::now(config('app.timezone')))->exists()) {
            session()->flash('status', 'No hay citas para el cliente '.$client->full_name);
            $this->redirect(route('appointments.index'));

            return;
        }

        session()->flash('status', $status);
        $this->redirect($client ? route('clients.appointments', $client) : url()->previous());
    }

    private function appointmentsQuery(?Client $selectedClient, Carbon $now): Builder
    {
        return Appointment::query()
            ->select('appointments.*')
            ->with(['client', 'latestWhatsAppMessage'])
            ->leftJoin('clients', 'clients.id', '=', 'appointments.client_id')
            ->when($selectedClient, fn (Builder $query) => $query->where('appointments.client_id', $selectedClient->id))
            ->when($this->filter_nombre, fn (Builder $query) => $query->whereHas('client', fn ($clientQuery) => $clientQuery->where('nombre', 'like', '%'.$this->filter_nombre.'%')))
            ->when($this->filter_apellidos, fn (Builder $query) => $query->whereHas('client', fn ($clientQuery) => $clientQuery->where('apellidos', 'like', '%'.$this->filter_apellidos.'%')))
            ->when($this->sentOnly, fn (Builder $query) => $query->where('appointments.enviado', true))
            ->when(! $this->showAllHistory && $selectedClient && ! $this->sentOnly && $this->dateFilter === 'upcoming', fn (Builder $query) => $query->whereDate('appointments.fecha', '>=', $now->toDateString()))
            ->when(! $this->showAllHistory && ! $this->sentOnly && $this->dateFilter === 'past', fn (Builder $query) => $this->wherePastAppointment($query, $now))
            ->when(! $this->showAllHistory && ! $this->sentOnly && $this->filter_entregado, fn (Builder $query) => $query->where('appointments.entregado', true))
            ->when(! $this->showAllHistory && ! $this->sentOnly && $this->filter_enviado, fn (Builder $query) => $query->where('appointments.enviado', true))
            ->when(! $this->showAllHistory && ! $this->sentOnly && $this->dateFilter === 'upcoming' && ! $this->filter_entregado && ! $this->filter_enviado, function (Builder $query) use ($now): void {
                $query->where('appointments.cita_activa', true)
                    ->whereDate('appointments.fecha', '>=', $now->toDateString());
            })
            ->when($this->sort_by === 'cliente', function (Builder $query): void {
                $query
                    ->orderBy('clients.nombre', $this->sort_direction)
                    ->orderBy('clients.apellidos', $this->sort_direction)
                    ->orderBy('appointments.fecha', $this->sort_direction)
                    ->orderBy('appointments.hora', $this->sort_direction);
            }, function (Builder $query): void {
                $query
                    ->orderBy('appointments.fecha', $this->sort_direction)
                    ->orderBy('appointments.hora', $this->sort_direction);
            });
    }
}
