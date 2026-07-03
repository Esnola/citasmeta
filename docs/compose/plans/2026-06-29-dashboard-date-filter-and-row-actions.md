# Dashboard Date Filter & Row Actions Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use compose:subagent (recommended) or compose:execute to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add date filter buttons (Mañana, Pasado mañana, En 3 días) to the dashboard with Sunday-skip logic, and add Filament-style row actions (client link, view appointments, edit client) to the appointment list.

**Architecture:** Extend the existing `DashboardOverview` Livewire component with a `selectedDateOffset` property and helper methods for date computation. Update the Blade view with a button group and enhanced row actions. Tests verify Sunday skip logic and button behavior.

**Tech Stack:** Laravel 13, Livewire 4, Flux UI, Tailwind CSS 4, PHPUnit 12, Carbon

## Global Constraints

- PHP 8.4, Laravel 13, Livewire 4, PHPUnit 12
- Spanish field names: `nombre`, `apellidos`, `telefono`, `fecha`, `hora`
- Run `vendor/bin/pint --dirty --format agent` after any PHP edit
- Tests use SQLite in-memory (`phpunit.xml` sets `DB_DATABASE=:memory:`)
- Use `App\Models\Appointment`, `App\Models\Client` — no new models
- WhatsApp driver `log` for tests

---

## File Structure

| File | Action | Purpose |
|------|--------|---------|
| `app/Livewire/DashboardOverview.php` | Modify | Add date offset property, date computation, Sunday skip logic |
| `resources/views/livewire/dashboard-overview.blade.php` | Modify | Add date buttons, warning banner, row actions |
| `tests/Feature/DashboardOverviewTest.php` | Modify | Add tests for date filter and row actions |

---

### Task 1: Add date filter logic to DashboardOverview component

**Covers:** Date filter buttons with Sunday skip

**Files:**
- Modify: `app/Livewire/DashboardOverview.php`
- Test: `tests/Feature/DashboardOverviewTest.php`

**Interfaces:**
- Consumes: `Appointment` model, `Carbon` for date arithmetic
- Produces: `selectedDateOffset` property, `targetDate()` method, `targetDates` computed property, `selectDate(int $offset)` action, `sundayWarning` computed property

- [ ] **Step 1: Write failing tests for date filter**

Add to `tests/Feature/DashboardOverviewTest.php`:

```php
public function test_date_buttons_render_with_correct_labels(): void
{
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(DashboardOverview::class)
        ->assertSee('Mañana')
        ->assertSee('Pasado mañana')
        ->assertSee('En 3 días');
}

public function test_selecting_date_offset_updates_appointments(): void
{
    $now = Carbon::parse('2026-06-29 10:00:00')->next(Carbon::MONDAY); // Monday
    Carbon::setTestNow($now);
    $user = User::factory()->create();

    $client = Client::query()->create([
        'nombre' => 'Ana',
        'apellidos' => 'Pérez',
        'telefono' => '+34600123123',
    ]);

    // Appointment 2 days from now (Wednesday)
    $twoDaysLater = $now->copy()->addDays(2)->setTime(14, 0);
    Appointment::query()->create([
        'client_id' => $client->id,
        'fecha' => $twoDaysLater->toDateString(),
        'hora' => $twoDaysLater->format('H:i:s'),
        'enviado' => false,
        'activo' => true,
    ]);

    $this->actingAs($user);

    Livewire::test(DashboardOverview::class)
        ->assertDontSee('Ana Pérez')
        ->call('selectDate', 2)
        ->assertSee('Ana Pérez');
}

public function test_sunday_skip_when_tomorrow_is_sunday(): void
{
    // Saturday → tomorrow is Sunday → should skip to Monday
    $now = Carbon::parse('2026-06-27 10:00:00'); // Saturday
    Carbon::setTestNow($now);
    $user = User::factory()->create();

    $client = Client::query()->create([
        'nombre' => 'Lucía',
        'apellidos' => 'Martín',
        'telefono' => '+34666777888',
    ]);

    // Monday appointment
    $monday = $now->copy()->next(Carbon::MONDAY)->setTime(9, 0);
    Appointment::query()->create([
        'client_id' => $client->id,
        'fecha' => $monday->toDateString(),
        'hora' => $monday->format('H:i:s'),
        'enviado' => false,
        'activo' => true,
    ]);

    $this->actingAs($user);

    Livewire::test(DashboardOverview::class)
        ->assertSee('Lucía Martín')
        ->assertSee('lunes');
}

public function test_sunday_warning_not_shown_on_regular_days(): void
{
    $now = Carbon::parse('2026-06-29 10:00:00'); // Monday
    Carbon::setTestNow($now);
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(DashboardOverview::class)
        ->assertDontSee('domingo');
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --compact --filter=test_date_buttons_render|test_selecting_date_offset|test_sunday_skip|test_sunday_warning`
Expected: FAIL (methods/properties don't exist yet)

- [ ] **Step 3: Implement date filter logic**

Replace `app/Livewire/DashboardOverview.php`:

```php
<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\WhatsAppMessage;
use Carbon\Carbon;
use Illuminate\View\View;
use Livewire\Component;

class DashboardOverview extends Component
{
    public int $selectedDateOffset = 1;

    public function selectDate(int $offset): void
    {
        $this->selectedDateOffset = $offset;
    }

    public function render(): View
    {
        $targetDate = $this->targetDate();

        return view('livewire.dashboard-overview', [
            'pendingCount' => WhatsAppMessage::pending()->count(),
            'sentCount' => WhatsAppMessage::where('status', WhatsAppMessage::STATUS_SENT)->count(),
            'failedCount' => WhatsAppMessage::where('status', WhatsAppMessage::STATUS_FAILED)->count(),
            'nextAppointments' => Appointment::query()
                ->with('client')
                ->where('activo', true)
                ->whereDate('fecha', $targetDate->toDateString())
                ->orderBy('hora')
                ->limit(5)
                ->get(),
            'targetDates' => $this->targetDates(),
            'selectedDate' => $targetDate,
            'sundayWarning' => $this->sundayWarning(),
        ]);
    }

    private function targetDate(): Carbon
    {
        return $this->computeDateSkippingSunday($this->selectedDateOffset);
    }

    /**
     * @return array{offset:int,label:string,date:Carbon}
     */
    private function targetDates(): array
    {
        return collect([1, 2, 3])
            ->mapWithKeys(fn (int $offset) => [
                $offset => [
                    'offset' => $offset,
                    'label' => match ($offset) {
                        1 => 'Mañana',
                        2 => 'Pasado mañana',
                        3 => 'En 3 días',
                    },
                    'date' => $this->computeDateSkippingSunday($offset),
                ],
            ])
            ->all();
    }

    private function computeDateSkippingSunday(int $offset): Carbon
    {
        $now = now(config('app.timezone'));
        $date = $now->copy()->addDays($offset);

        if ($date->isSunday()) {
            $date = $date->addDay();
        }

        return $date;
    }

    private function sundayWarning(): ?string
    {
        $now = now(config('app.timezone'));
        $rawDate = $now->copy()->addDays($this->selectedDateOffset);

        if (! $rawDate->isSunday()) {
            return null;
        }

        $resolvedDate = $this->targetDate();

        return 'La fecha seleccionada cae en domingo, se mostrarán las citas del '.$resolvedDate->translatedFormat('l d \\d\\e F).';
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --compact --filter=test_date_buttons_render|test_selecting_date_offset|test_sunday_skip|test_sunday_warning`
Expected: PASS

- [ ] **Step 5: Run pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 6: Commit**

```bash
git add app/Livewire/DashboardOverview.php tests/Feature/DashboardOverviewTest.php
git commit -m "feat: add date filter logic with Sunday skip to dashboard"
```

---

### Task 2: Update dashboard Blade view with date buttons and warning

**Covers:** Date filter UI, Sunday warning banner

**Files:**
- Modify: `resources/views/livewire/dashboard-overview.blade.php`

**Interfaces:**
- Consumes: `$targetDates` (array), `$selectedDate` (Carbon), `$sundayWarning` (?string), `$selectedDateOffset` (int)
- Produces: Rendered button group and warning banner

- [ ] **Step 1: Update the Blade view**

Replace `resources/views/livewire/dashboard-overview.blade.php`:

```blade
<div class="grid gap-6">
    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <p class="text-sm text-slate-400">Pendientes</p>
            <p class="mt-2 text-3xl font-semibold">{{ $pendingCount }}</p>
        </div>
        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <p class="text-sm text-slate-400">Enviados</p>
            <p class="mt-2 text-3xl font-semibold">{{ $sentCount }}</p>
        </div>
        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <p class="text-sm text-slate-400">Fallidos</p>
            <p class="mt-2 text-3xl font-semibold">{{ $failedCount }}</p>
        </div>
    </div>

    <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-xl font-semibold">Próximas citas</h2>
            <div class="flex gap-2">
                @foreach ($targetDates as $offset => $target)
                    <button
                        type="button"
                        wire:click="selectDate({{ $offset }})"
                        class="rounded-full border px-4 py-2 text-sm font-medium transition-colors
                            {{ $selectedDateOffset === $offset
                                ? 'border-emerald-400/40 bg-emerald-400/15 text-emerald-200'
                                : 'border-white/10 bg-white/5 text-slate-300 hover:border-white/20 hover:bg-white/10 hover:text-white' }}"
                    >
                        {{ $target['label'] }}
                        <span class="ml-1 text-xs text-slate-400">
                            {{ $target['date']->format('d/m') }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        @if ($sundayWarning)
            <div class="mt-3 rounded-2xl border border-yellow-400/25 bg-yellow-400/10 px-4 py-3 text-sm text-yellow-200">
                {{ $sundayWarning }}
            </div>
        @endif

        <div class="mt-4 space-y-3">
            @forelse ($nextAppointments as $appointment)
                <div class="flex flex-col gap-2 rounded-2xl border border-white/10 bg-slate-900/50 p-4 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center gap-3">
                        <div>
                            <a href="{{ route('appointments.index', ['client' => $appointment->client_id]) }}"
                               class="font-medium text-emerald-300 hover:text-emerald-200 hover:underline">
                                {{ $appointment->client?->full_name }}
                            </a>
                            <p class="text-sm text-slate-400">{{ $appointment->client?->telefono }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-slate-300">
                            {{ $appointment->scheduledFor()->format('d/m/Y H:i') }}
                        </span>
                        <a href="{{ route('appointments.index', ['client' => $appointment->client_id]) }}"
                           class="inline-flex items-center justify-center rounded-full border border-white/10 bg-white/5 p-2 text-slate-300 transition-colors hover:border-white/20 hover:bg-white/10 hover:text-white"
                           title="Ver citas del cliente">
                            <svg viewBox="0 0 14 14" class="size-3.5" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M7 3c-3.489 0-6.514 2.032-8 5 1.486 2.968 4.511 5 8 5s6.514-2.032 8-5c-1.486-2.968-4.511-5-8-5z"/>
                                <circle cx="7" cy="7" r="2"/>
                            </svg>
                        </a>
                        <a href="{{ route('clients.edit', $appointment->client_id) }}"
                           class="inline-flex items-center justify-center rounded-full border border-white/10 bg-white/5 p-2 text-slate-300 transition-colors hover:border-white/20 hover:bg-white/10 hover:text-white"
                           title="Editar cliente">
                            <svg viewBox="0 0 14 14" class="size-3.5" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M8.5 2.5l3 3M1 13l1-4L10.5 0.5c0.8-0.8 2-0.8 2.8 0l0.2 0.2c0.8 0.8 0.8 2 0 2.8L5 12l-4 1z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-400">No hay citas próximas.</p>
            @endforelse
        </div>
    </div>
</div>
```

- [ ] **Step 2: Run existing tests to verify nothing broke**

Run: `php artisan test --compact --filter=DashboardOverview`
Expected: PASS

- [ ] **Step 3: Run pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 4: Commit**

```bash
git add resources/views/livewire/dashboard-overview.blade.php
git commit -m "feat: add date filter buttons and row actions to dashboard view"
```

---

### Task 3: Add row action tests

**Covers:** Client link, view appointments button, edit client button

**Files:**
- Modify: `tests/Feature/DashboardOverviewTest.php`

**Interfaces:**
- Consumes: `DashboardOverview` component, `Client` model
- Produces: Test assertions for row actions

- [ ] **Step 1: Write tests for row actions**

Add to `tests/Feature/DashboardOverviewTest.php`:

```php
public function test_client_name_links_to_appointments(): void
{
    $now = Carbon::parse('2026-06-29 10:00:00')->next(Carbon::FRIDAY);
    Carbon::setTestNow($now);
    $user = User::factory()->create();

    $client = Client::query()->create([
        'nombre' => 'Ana',
        'apellidos' => 'Pérez',
        'telefono' => '+34600123123',
    ]);

    $appointmentAt = $now->copy()->addDay()->setTime(11, 20);
    Appointment::query()->create([
        'client_id' => $client->id,
        'fecha' => $appointmentAt->toDateString(),
        'hora' => $appointmentAt->format('H:i:s'),
        'enviado' => false,
        'activo' => true,
    ]);

    $this->actingAs($user);

    Livewire::test(DashboardOverview::class)
        ->assertSee(route('appointments.index', ['client' => $client->id]))
        ->assertSee(route('clients.edit', $client->id));
}

public function test_edit_client_button_present(): void
{
    $now = Carbon::parse('2026-06-29 10:00:00')->next(Carbon::FRIDAY);
    Carbon::setTestNow($now);
    $user = User::factory()->create();

    $client = Client::query()->create([
        'nombre' => 'Carlos',
        'apellidos' => 'Ruiz',
        'telefono' => '+34611222333',
    ]);

    $appointmentAt = $now->copy()->addDay()->setTime(9, 0);
    Appointment::query()->create([
        'client_id' => $client->id,
        'fecha' => $appointmentAt->toDateString(),
        'hora' => $appointmentAt->format('H:i:s'),
        'enviado' => false,
        'activo' => true,
    ]);

    $this->actingAs($user);

    Livewire::test(DashboardOverview::class)
        ->assertSee('Editar cliente')
        ->assertSee('Ver citas del cliente');
}
```

- [ ] **Step 2: Run tests to verify they pass**

Run: `php artisan test --compact --filter=test_client_name_links|test_edit_client_button`
Expected: PASS

- [ ] **Step 3: Run pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 4: Commit**

```bash
git add tests/Feature/DashboardOverviewTest.php
git commit -m "test: add tests for dashboard row actions"
```

---

### Task 4: Run full test suite and verify

**Covers:** All sections — regression check

**Files:** None (verification only)

- [ ] **Step 1: Run full test suite**

Run: `php artisan test --compact`
Expected: ALL PASS

- [ ] **Step 2: Run pint one final time**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 3: Final commit if any formatting changes**

```bash
git add -A
git commit -m "style: apply pint formatting"
```
