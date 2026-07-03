<?php

namespace App\Models;

use App\Traits\NormalizesPhone;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Client extends Model
{
    use HasFactory, NormalizesPhone, SoftDeletes;

    protected $fillable = [
        'nombre',
        'apellidos',
        'telefono',
    ];

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->nombre,
            $this->apellidos,
        ])));
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    protected function telefono(): Attribute
    {
        return Attribute::set(fn (string $value): string => static::normalizePhone($value));
    }

    public static function isValidPhone(string $phone): bool
    {
        $normalized = static::normalizePhone($phone);

        if ($normalized === '') {
            return false;
        }

        $digits = preg_replace('/\D+/', '', $normalized) ?? '';

        return strlen($digits) >= 7 && strlen($digits) <= 15;
    }

    public static function upsertFromImport(array $data): self
    {
        $rawPhone = trim((string) ($data['telefono'] ?? ''));
        $rawName = trim(implode(' ', array_filter([
            (string) ($data['nombre'] ?? ''),
            (string) ($data['apellidos'] ?? ''),
        ])));
        $normalizedPhone = static::normalizePhone($rawPhone);
        $payload = [
            'nombre' => trim((string) ($data['nombre'] ?? '')),
            'apellidos' => trim((string) ($data['apellidos'] ?? '')),
            'telefono' => $normalizedPhone,
        ];

        $lookupName = static::normalizeImportValue($rawName);

        $client = static::withTrashed()
            ->get()
            ->first(fn (self $candidate): bool => static::matchesImportIdentity($candidate, $normalizedPhone, $lookupName));

        if ($client) {
            if ($client->trashed()) {
                $client->restore();
            }

            return $client;
        }

        return static::query()->create($payload);
    }

    private static function matchesImportIdentity(self $client, string $lookupPhone, string $lookupName): bool
    {
        return static::normalizeImportValue(trim($client->nombre.' '.$client->apellidos)) === $lookupName
            && static::normalizePhone((string) $client->telefono) === $lookupPhone;
    }

    private static function normalizeImportValue(string $value): string
    {
        $value = Str::ascii(trim($value));
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return mb_strtolower($value);
    }
}
