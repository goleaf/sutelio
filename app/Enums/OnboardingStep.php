<?php

declare(strict_types=1);

namespace App\Enums;

enum OnboardingStep: string
{
    case Welcome = 'welcome';
    case Preferences = 'preferences';
    case Workspace = 'workspace';
    case Project = 'project';
    case Task = 'task';
    case ProductMap = 'product_map';
    case Safety = 'safety';
    case Results = 'results';

    /** @return list<self> */
    public static function ordered(): array
    {
        return [
            self::Welcome,
            self::Preferences,
            self::Workspace,
            self::Project,
            self::Task,
            self::ProductMap,
            self::Safety,
            self::Results,
        ];
    }

    public function position(): int
    {
        return match ($this) {
            self::Welcome => 1,
            self::Preferences => 2,
            self::Workspace => 3,
            self::Project => 4,
            self::Task => 5,
            self::ProductMap => 6,
            self::Safety => 7,
            self::Results => 8,
        };
    }

    public function percent(): int
    {
        return (int) round(($this->position() / count(self::ordered())) * 100);
    }
}
