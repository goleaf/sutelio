<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\RememberedEmailStorageStatus;

final readonly class RememberedEmailStorageResult
{
    public function __construct(
        public RememberedEmailStorageStatus $status,
        public ?string $value = null,
    ) {}

    public function found(): bool
    {
        return $this->status === RememberedEmailStorageStatus::Found;
    }

    public function missing(): bool
    {
        return $this->status === RememberedEmailStorageStatus::NotFound;
    }
}
