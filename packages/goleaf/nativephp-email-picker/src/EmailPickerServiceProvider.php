<?php

declare(strict_types=1);

namespace GoLeaf\NativeEmailPicker;

use Illuminate\Support\ServiceProvider;

class EmailPickerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // The application calls this plugin through NativePHP's generic bridge.
    }
}
