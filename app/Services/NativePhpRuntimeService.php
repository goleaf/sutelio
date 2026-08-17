<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Config\Repository;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;

class NativePhpRuntimeService
{
    public function __construct(
        private Repository $config,
        private Factory $views,
    ) {}

    public function configure(): void
    {
        if (! in_array($this->config->get('nativephp-internal.platform'), ['android', 'ios'], true)) {
            return;
        }

        $this->config->set('mail.default', 'array');

        $finder = $this->views->getFinder();

        if (! $finder instanceof FileViewFinder) {
            return;
        }

        foreach ($finder->getHints() as $namespace => $paths) {
            $availablePaths = array_values(array_filter(
                $paths,
                static fn (mixed $path): bool => is_string($path) && is_dir($path),
            ));

            $finder->replaceNamespace($namespace, $availablePaths);
        }
    }
}
