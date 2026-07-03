<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class WhatsAppTemplate extends Model
{
    use HasFactory;

    protected static ?Collection $catalogCache = null;

    protected $table = 'whatsapp_templates';

    protected $fillable = [
        'key',
        'label',
        'message',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'bool',
            'is_active' => 'bool',
            'sort_order' => 'int',
        ];
    }

    public static function catalog(): Collection
    {
        if (static::$catalogCache !== null) {
            return static::$catalogCache;
        }

        if (! Schema::hasTable((new static)->getTable())) {
            return static::$catalogCache = collect(config('whatsapp.templates', []))
                ->map(function (array $template, string $key): array {
                    return [
                        'key' => $key,
                        'label' => $template['label'] ?? $key,
                        'message' => $template['message'] ?? '',
                        'is_default' => $key === config('whatsapp.default_template'),
                        'is_active' => true,
                        'sort_order' => 0,
                    ];
                })
                ->values();
        }

        $templates = static::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get()
            ->map(fn (self $template) => [
                'key' => $template->key,
                'label' => $template->label,
                'message' => $template->message,
                'is_default' => $template->is_default,
                'is_active' => $template->is_active,
                'sort_order' => $template->sort_order,
            ]);

        if ($templates->isNotEmpty()) {
            return static::$catalogCache = $templates;
        }

        return static::$catalogCache = collect(config('whatsapp.templates', []))
            ->map(function (array $template, string $key): array {
                return [
                    'key' => $key,
                    'label' => $template['label'] ?? $key,
                    'message' => $template['message'] ?? '',
                    'is_default' => $key === config('whatsapp.default_template'),
                    'is_active' => true,
                    'sort_order' => 0,
                ];
            })
            ->values();
    }

    public static function flushCatalogCache(): void
    {
        static::$catalogCache = null;
    }

    public static function templateOptions(): array
    {
        return static::catalog()
            ->map(fn (array $template) => [
                'key' => $template['key'],
                'label' => $template['label'],
                'message' => $template['message'],
            ])
            ->values()
            ->all();
    }

    public static function resolve(?string $key = null): array
    {
        $catalog = static::catalog();
        $defaultKey = config('whatsapp.default_template');

        $template = $key ? $catalog->firstWhere('key', $key) : null;
        $template ??= $catalog->firstWhere('key', $defaultKey);
        $template ??= $catalog->first();

        if (! $template) {
            return [
                'key' => $key ?: $defaultKey,
                'label' => $key ?: $defaultKey,
                'message' => '',
            ];
        }

        return [
            'key' => $template['key'],
            'label' => $template['label'],
            'message' => $template['message'],
        ];
    }

    public static function hasKey(string $key): bool
    {
        return static::catalog()->contains(fn (array $template) => $template['key'] === $key);
    }

    public static function defaultKey(): string
    {
        $catalog = static::catalog();

        $default = $catalog->firstWhere('is_default', true);

        if ($default) {
            return $default['key'];
        }

        $fallback = $catalog->first();

        if ($fallback) {
            return $fallback['key'];
        }

        return config('whatsapp.default_template');
    }

    public static function generateKey(string $label, ?int $exceptId = null): string
    {
        if (! Schema::hasTable((new static)->getTable())) {
            return Str::slug($label) ?: 'template';
        }

        $base = Str::slug($label);
        $base = $base !== '' ? $base : 'template';
        $key = $base;
        $suffix = 1;

        while (static::query()
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->where('key', $key)
            ->exists()) {
            $key = $base.'-'.$suffix++;
        }

        return $key;
    }

    protected function defaultBadge(): Attribute
    {
        return Attribute::get(fn () => $this->is_default ? 'Predeterminada' : null);
    }
}
