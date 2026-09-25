<?php

namespace App\Support;

final class EnglishDigits
{
    /**
     * Keep only digits, converting Arabic-Indic and Persian digits to English.
     * Returns null when the value has no digits.
     */
    public static function from(mixed $value): ?string
    {
        if (is_int($value)) {
            $digits = (string) $value;
        } elseif (is_string($value) || is_float($value)) {
            $digits = preg_replace('/\D+/', '', self::toEnglish((string) $value)) ?? '';
        } else {
            return null;
        }

        $digits = ltrim($digits, '0');

        return $digits === '' ? null : $digits;
    }

    public static function toEnglish(string $value): string
    {
        return strtr($value, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);
    }
}
