<?php

declare(strict_types=1);

namespace App\Enums;

enum RememberedEmailStorageStatus
{
    case Found;
    case NotFound;
    case Unavailable;
    case Failed;
}
