<?php

namespace App\Livewire;

use App\Models\Client;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class ClientList extends Component
{
    use WithPagination;

    public bool $showAllClients = false;

    public string $filter_nombre = '';

    public string $filter_apellidos = '';

    public string $filter_telefono = '';

    public string $sort_direction = 'asc';

    public ?int $clientPendingDeletionId = null;

    public function updatedFilterNombre(): void
    {
        $this->resetPage();
    }

    public function updatedFilterApellidos(): void
    {
        $this->resetPage();
    }

    public function updatedFilterTelefono(): void
    {
        $this->resetPage();
    }

    public function sortByName(): void
    {
        $this->sort_direction = $this->sort_direction === 'asc' ? 'desc' : 'asc';

        $this->resetPage();
    }

    public function confirmDelete(int $clientId): void
    {
        $this->clientPendingDeletionId = $clientId;
    }

    public function cancelDelete(): void
    {
        $this->clientPendingDeletionId = null;
    }

    public function deleteConfirmed(): void
    {
        if (! $this->clientPendingDeletionId) {
            return;
        }

        Client::query()->find($this->clientPendingDeletionId)?->delete();
        $this->clientPendingDeletionId = null;

        session()->flash('status', 'Cliente eliminado correctamente.');

        $this->redirect(url()->previous());
    }

    public function getHasClientSearchProperty(): bool
    {
        return max(
            mb_strlen($this->filter_nombre),
            mb_strlen($this->filter_apellidos),
            mb_strlen($this->filter_telefono),
        ) >= 1;
    }

    public function render()
    {
        $shouldShowClients = $this->showAllClients || $this->hasClientSearch;

        $clients = $shouldShowClients
            ? Client::query()
                ->when($this->filter_nombre, fn ($query) => $query->where('nombre', 'like', '%'.$this->filter_nombre.'%'))
                ->when($this->filter_apellidos, fn ($query) => $query->where('apellidos', 'like', '%'.$this->filter_apellidos.'%'))
                ->when($this->filter_telefono, fn ($query) => $query->where('telefono', 'like', '%'.$this->filter_telefono.'%'))
                ->orderBy('nombre', $this->sort_direction)
                ->orderBy('apellidos', $this->sort_direction)
                ->paginate(15)
            : new LengthAwarePaginator([], 0, 15);

        return view('livewire.client-list', [
            'clients' => $clients,
            'clientPendingDeletion' => $this->clientPendingDeletionId
                ? Client::query()->find($this->clientPendingDeletionId)
                : null,
            'hasClientSearch' => $this->hasClientSearch,
            'showAllClients' => $this->showAllClients,
        ]);
    }
}
