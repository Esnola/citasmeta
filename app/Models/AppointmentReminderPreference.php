<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

use function collect;

class AppointmentReminderPreference extends Model
{
    public const CHANNEL_WHATSAPP = 'whatsapp';

    public const CHANNEL_EMAIL = 'email';

    protected $fillable = [
        'channel',
        'lead_days',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'lead_days' => 'integer',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function leadDayOptions(): array
    {
        return [
            1 => '1 día antes',
            2 => '2 días antes',
            3 => '3 días antes',
            7 => '1 semana antes',
        ];
    }

    /**
     * @return list<string>
     */
    public static function channels(): array
    {
        return [
            self::CHANNEL_WHATSAPP,
            self::CHANNEL_EMAIL,
        ];
    }

    /**
     * @return list<int>
     */
    public static function enabledLeadDaysFor(string $channel): array
    {
        $preferences = static::query()
            ->where('channel', $channel)
            ->orderBy('lead_days')
            ->get();

        if ($preferences->isEmpty()) {
            return static::defaultLeadDaysFor($channel);
        }

        return static::selectedLeadDays($preferences);
    }

    /**
     * @return array<string, list<int>>
     */
    public static function selections(): array
    {
        $preferences = static::query()
            ->whereIn('channel', static::channels())
            ->get()
            ->groupBy('channel');

        return collect(static::channels())
            ->mapWithKeys(fn (string $channel): array => [
                $channel => $preferences->has($channel)
                    ? static::selectedLeadDays($preferences->get($channel, collect()))
                    : static::defaultLeadDaysFor($channel),
            ])
            ->all();
    }

    /**
     * @param  array<string, list<int>>  $selections
     */
    public static function saveSelections(array $selections): void
    {
        foreach (static::channels() as $channel) {
            $selectedLeadDays = collect($selections[$channel] ?? [])
                ->map(fn ($leadDays) => (int) $leadDays)
                ->intersect(array_keys(static::leadDayOptions()))
                ->values();

            foreach (array_keys(static::leadDayOptions()) as $leadDays) {
                static::query()->updateOrCreate(
                    [
                        'channel' => $channel,
                        'lead_days' => $leadDays,
                    ],
                    [
                        'enabled' => $selectedLeadDays->contains($leadDays),
                    ],
                );
            }
        }
    }

    /**
     * @param  Collection<int, self>  $preferences
     * @return list<int>
     */
    private static function selectedLeadDays(Collection $preferences): array
    {
        return $preferences
            ->where('enabled', true)
            ->pluck('lead_days')
            ->map(fn ($leadDays) => (int) $leadDays)
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return list<int>
     */
    private static function defaultLeadDaysFor(string $channel): array
    {
        return $channel === self::CHANNEL_WHATSAPP ? [1] : [];
    }
}
