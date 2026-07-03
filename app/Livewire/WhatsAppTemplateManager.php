<?php

namespace App\Livewire;

use App\Models\WhatsAppTemplate;
use Livewire\Component;

class WhatsAppTemplateManager extends Component
{
    public ?int $editingTemplateId = null;

    public ?int $templatePendingDeletionId = null;

    public string $key = '';

    public string $label = '';

    public string $message = '';

    public bool $is_default = false;

    public bool $is_active = true;

    public int $sort_order = 0;

    public function rules(): array
    {
        return [
            'key' => ['nullable', 'string', 'max:80'],
            'label' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:2000'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
        ];
    }

    public function mount(): void
    {
        $this->resetForm();
    }

    public function create(): void
    {
        $this->resetForm();
    }

    public function edit(int $templateId): void
    {
        $template = WhatsAppTemplate::query()->findOrFail($templateId);

        $this->editingTemplateId = $template->id;
        $this->key = $template->key;
        $this->label = $template->label;
        $this->message = $template->message;
        $this->is_default = $template->is_default;
        $this->is_active = $template->is_active;
        $this->sort_order = $template->sort_order;
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingTemplateId) {
            $template = WhatsAppTemplate::query()->findOrFail($this->editingTemplateId);
            $data['key'] = $template->key;
            $template->update($data);
        } else {
            $data['key'] = $this->key !== ''
                ? WhatsAppTemplate::generateKey($this->key)
                : WhatsAppTemplate::generateKey($data['label']);
            WhatsAppTemplate::query()->create($data);
        }

        if ($data['is_default']) {
            WhatsAppTemplate::query()
                ->where('key', '!=', $data['key'])
                ->update(['is_default' => false]);
        }

        WhatsAppTemplate::flushCatalogCache();

        $this->resetForm();

        session()->flash('status', 'Plantilla guardada correctamente.');

        $this->redirect(url()->previous());
    }

    public function delete(int $templateId): void
    {
        $template = WhatsAppTemplate::query()->findOrFail($templateId);
        $template->delete();

        if ($template->is_default) {
            $fallback = WhatsAppTemplate::query()->orderBy('sort_order')->first();
            if ($fallback) {
                $fallback->update(['is_default' => true]);
            }
        }

        if ($this->editingTemplateId === $templateId) {
            $this->resetForm();
        }

        WhatsAppTemplate::flushCatalogCache();

        session()->flash('status', 'Plantilla eliminada.');

        $this->redirect(url()->previous());
    }

    public function confirmDelete(int $templateId): void
    {
        $this->templatePendingDeletionId = $templateId;
    }

    public function cancelDelete(): void
    {
        $this->templatePendingDeletionId = null;
    }

    public function deleteConfirmed(): void
    {
        if (! $this->templatePendingDeletionId) {
            return;
        }

        $this->delete($this->templatePendingDeletionId);
        $this->templatePendingDeletionId = null;
    }

    public function setDefault(int $templateId): void
    {
        WhatsAppTemplate::query()->update(['is_default' => false]);
        WhatsAppTemplate::query()->whereKey($templateId)->update(['is_default' => true]);
        WhatsAppTemplate::flushCatalogCache();
        $this->resetForm();

        session()->flash('status', 'Plantilla predeterminada actualizada.');

        $this->redirect(url()->previous());
    }

    public function render()
    {
        return view('livewire.whatsapp-template-manager', [
            'templates' => WhatsAppTemplate::query()
                ->orderBy('sort_order')
                ->orderBy('label')
                ->get(),
            'templatePendingDeletion' => $this->templatePendingDeletionId
                ? WhatsAppTemplate::query()->find($this->templatePendingDeletionId)
                : null,
        ]);
    }

    private function resetForm(): void
    {
        $this->editingTemplateId = null;
        $this->key = '';
        $this->label = '';
        $this->message = '';
        $this->is_default = false;
        $this->is_active = true;
        $this->sort_order = 0;
    }
}
