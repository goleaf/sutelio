<?php

declare(strict_types=1);

namespace App\Enums;

use Illuminate\Support\Str;

enum TimeZoneRegion: string
{
    case Utc = 'utc';
    case Africa = 'africa';
    case America = 'america';
    case Antarctica = 'antarctica';
    case Arctic = 'arctic';
    case Asia = 'asia';
    case Atlantic = 'atlantic';
    case Australia = 'australia';
    case Europe = 'europe';
    case Indian = 'indian';
    case Pacific = 'pacific';
    case Other = 'other';

    public static function forIdentifier(string $identifier): self
    {
        if ($identifier === 'UTC') {
            return self::Utc;
        }

        return match (Str::before($identifier, '/')) {
            'Africa' => self::Africa,
            'America' => self::America,
            'Antarctica' => self::Antarctica,
            'Arctic' => self::Arctic,
            'Asia' => self::Asia,
            'Atlantic' => self::Atlantic,
            'Australia' => self::Australia,
            'Europe' => self::Europe,
            'Indian' => self::Indian,
            'Pacific' => self::Pacific,
            default => self::Other,
        };
    }

    public function label(UserLanguage $language): string
    {
        $key = "ui.timezones.regions.{$this->value}";
        $translation = trans($key, [], $language->value);

        return is_string($translation) ? $translation : $key;
    }
}
