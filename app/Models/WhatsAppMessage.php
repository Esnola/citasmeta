<?php

namespace App\Models;

use App\Traits\NormalizesPhone;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessage extends Model
{
    use HasFactory, NormalizesPhone;

    protected $table = 'whatsapp_messages';

    public const STATUS_PENDING = 'pending';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_CSV = 'csv';

    public const SOURCE_APPOINTMENT = 'appointment';

    protected $fillable = [
        'user_id',
        'client_id',
        'appointment_id',
        'nombre',
        'apellidos',
        'telefono',
        'scheduled_for',
        'message',
        'source',
        'status',
        'sent_at',
        'last_error',
        'provider_message_id',
        'provider_payload',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'datetime',
            'sent_at' => 'datetime',
            'provider_payload' => 'array',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->nombre,
            $this->apellidos,
        ])));
    }

    public static function buildMessage(array $data, ?string $template = null): string
    {
        $templateKey = $template ?: WhatsAppTemplate::defaultKey();
        $template = WhatsAppTemplate::hasKey($templateKey)
            ? WhatsAppTemplate::resolve($templateKey)['message']
            : $templateKey;
        $scheduled = $data['scheduled_for'] ?? null;

        $replacements = [
            '[NOMBRE]' => (string) ($data['nombre'] ?? ''),
            '[APELLIDOS]' => (string) ($data['apellidos'] ?? ''),
            '[TELEFONO]' => (string) ($data['telefono'] ?? ''),
            '[DIA]' => $scheduled?->format('d/m/Y') ?? '',
            '[HORA]' => $scheduled?->format('H:i') ?? '',
        ];

        return strtr($template, $replacements);
    }

    public static function templateOptions(): array
    {
        return WhatsAppTemplate::templateOptions();
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeDue($query)
    {
        return $query->pending()->where('scheduled_for', '<=', now());
    }

    public function isRead(): bool
    {
        return $this->deliveryStatus() === 'read';
    }

    public function isDelivered(): bool
    {
        return in_array($this->deliveryStatus(), ['delivered', 'read'], true);
    }

    public function deliveredAt(): ?Carbon
    {
        if (! $this->isDelivered()) {
            return null;
        }

        $timestamp = data_get($this->provider_payload, 'callback.received_at')
            ?? data_get($this->provider_payload, 'sync.received_at');

        return $this->parseTimestamp($timestamp) ?? $this->sent_at ?? $this->created_at;
    }

    public function readAt(): ?Carbon
    {
        if (! $this->isRead()) {
            return null;
        }

        return $this->deliveredAt();
    }

    public function deliveryStatus(): string
    {
        $callbackStatus = strtolower(trim((string) data_get($this->provider_payload, 'callback.message_status', '')));
        $callbackEventType = strtoupper(trim((string) data_get($this->provider_payload, 'callback.event_type', '')));
        $rawStatus = strtolower(trim((string) data_get($this->provider_payload, 'raw.status', '')));

        if (in_array($callbackStatus, ['delivered', 'read'], true)) {
            return $callbackStatus;
        }

        if ($callbackEventType === 'READ') {
            return 'read';
        }

        if (in_array($rawStatus, ['delivered', 'read'], true)) {
            return $rawStatus;
        }

        return $rawStatus;
    }

    public function normalizedPhone(): string
    {
        return static::normalizeInternationalPhone((string) $this->telefono);
    }

    protected function telefono(): Attribute
    {
        return Attribute::set(fn (string $value): string => static::normalizePhone($value));
    }

    protected function formattedScheduledFor(): Attribute
    {
        return Attribute::get(fn () => $this->scheduled_for?->timezone(config('app.timezone'))?->format('d/m/Y H:i'));
    }

    private function parseTimestamp(mixed $timestamp): ?Carbon
    {
        if (! is_string($timestamp) || trim($timestamp) === '') {
            return null;
        }

        try {
            return Carbon::parse($timestamp)->timezone(config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }
}
