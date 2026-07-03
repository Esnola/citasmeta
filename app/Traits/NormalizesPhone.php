<?php

namespace App\Traits;

trait NormalizesPhone
{
    public static function normalizePhone(string $phone): string
    {
        $cleaned = preg_replace('/^whatsapp:/i', '', trim($phone)) ?? trim($phone);
        $digits = preg_replace('/\D+/', '', $cleaned) ?? '';

        if ($digits === '') {
            return '';
        }

        $countryCode = preg_replace('/\D+/', '', (string) config('whatsapp.default_country_code', '+34')) ?? '34';

        if (str_starts_with($digits, $countryCode)) {
            return substr($digits, strlen($countryCode));
        }

        return str_starts_with($cleaned, '+') ? '+'.$digits : $digits;
    }

    public static function normalizeInternationalPhone(string $phone): string
    {
        $normalized = static::normalizePhone($phone);

        if ($normalized === '' || str_starts_with($normalized, '+')) {
            return $normalized;
        }

        return (string) config('whatsapp.default_country_code', '+34').$normalized;
    }

    public static function normalizeWhatsAppAddress(string $address): string
    {
        return str_starts_with($address, 'whatsapp:') ? $address : 'whatsapp:'.ltrim($address);
    }

    public static function normalizeWhatsAppRecipient(string $recipient): string
    {
        $normalized = static::normalizeInternationalPhone($recipient);

        return $normalized !== '' ? 'whatsapp:'.$normalized : '';
    }
}
