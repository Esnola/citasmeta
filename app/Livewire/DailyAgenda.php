<?php

namespace App\Livewire;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\View\View;
use Livewire\Component;

class DailyAgenda extends Component
{
    public int $selectedDateOffset = 0;

    public function selectDate(string $offset): void
    {
        $this->selectedDateOffset = (int) $offset;
    }

    public function render(): View
    {
        Carbon::setLocale('es');
        $targetDate = $this->targetDate();

        return view('livewire.daily-agenda', [
            'nextAppointments' => Appointment::query()
                ->with('client')
                ->whereDate('fecha', $targetDate->toDateString())
                ->orderBy('hora')
                ->get()
                ->groupBy(fn (Appointment $appointment) => $appointment->hora),
            'targetDates' => $this->targetDates(),
            'selectedDate' => $targetDate,
            'sundayWarning' => $this->sundayWarning(),
            'resolvedDates' => $this->resolvedDates(),
            'futureDayOptions' => range(2, 10),
        ]);
    }

    /** @return array<int, array{label: string, classes: string}> */
    public function appointmentIncidences(Appointment $appointment): array
    {
        $incidences = [];

        if (! $appointment->activo) {
            $incidences[] = [
                'label' => 'Desactivada',
                'classes' => 'border-red-500/20 bg-red-500/10 text-red-300',
            ];
        }

        if (! $appointment->enviado) {
            $incidences[] = [
                'label' => 'Sin enviar',
                'classes' => 'border-amber-500/20 bg-amber-500/10 text-amber-300',
            ];
        } elseif (! $appointment->entregado) {
            $incidences[] = [
                'label' => 'No entregada',
                'classes' => 'border-orange-500/20 bg-orange-500/10 text-orange-300',
            ];
        } elseif ($appointment->whatsapp_read_at) {
            $incidences[] = [
                'label' => 'Leída',
                'icono' => true,
                'classes' => 'border-green-500/20 bg-green-500/10 text-green-300',
            ];
        }

        return $incidences;
    }

    private function targetDate(): Carbon
    {
        return $this->resolvedDates()[$this->selectedDateOffset];
    }

    private function resolvedDates(): array
    {
        $now = now(config('app.timezone'));
        $dates = [0 => $now->copy()->startOfDay()];

        for ($offset = 1; $offset <= 10; $offset++) {
            $date = $now->copy()->addDays($offset);

            if ($date->isSunday()) {
                $date->addDay();
            }

            if (isset($dates[$offset - 1]) && $date->toDateString() === $dates[$offset - 1]->toDateString()) {
                $date->addDay();
                if ($date->isSunday()) {
                    $date->addDay();
                }
            }

            $dates[$offset] = $date;
        }

        return $dates;
    }

    private function targetDates(): array
    {
        $resolved = $this->resolvedDates();

        return collect([0, 1])
            ->mapWithKeys(fn (int $offset) => [
                $offset => [
                    'offset' => $offset,
                    'label' => $offset === 0 ? 'Hoy' : 'Mañana',
                    'date' => $resolved[$offset],
                ],
            ])
            ->all();
    }

    private function sundayWarning(): ?string
    {
        if ($this->selectedDateOffset === 0) {
            return null;
        }

        $rawDate = now(config('app.timezone'))->addDays($this->selectedDateOffset);

        if (! $rawDate->isSunday()) {
            return null;
        }

        return 'La fecha seleccionada es domingo, mostrando las citas del '.$this->targetDate()->translatedFormat('l d');
    }
}
