<?php

namespace App\Support;

class Phone
{
    /**
     * Normalize a Brazilian phone number to digits-only E.164 (default country 55).
     * Returns null when there are not enough digits to be a valid number.
     */
    public static function normalizeBr(?string $raw): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $raw);

        if ($digits === '' || strlen($digits) < 10) {
            return null;
        }

        // Strip a leading 0 (national trunk) before adding country code.
        $digits = ltrim($digits, '0');

        // Already includes country code 55 with a sensible length.
        if (str_starts_with($digits, '55') && strlen($digits) >= 12) {
            return $digits;
        }

        return '55'.$digits;
    }

    /** Format for a wa.me deep link (digits only, no plus). */
    public static function waLink(?string $raw): ?string
    {
        $normalized = self::normalizeBr($raw);

        return $normalized ? 'https://wa.me/'.$normalized : null;
    }
}
