<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\RememberedEmailStorageStatus;
use Illuminate\Contracts\Encryption\StringEncrypter;
use Illuminate\Filesystem\Filesystem;
use Throwable;

class EncryptedRememberedEmailStorage
{
    private readonly string $path;

    public function __construct(
        private StringEncrypter $encrypter,
        private Filesystem $filesystem,
        ?string $path = null,
    ) {
        $this->path = $path ?? storage_path(
            'app/private/native/email-assistance/remembered-emails.v1.enc',
        );
    }

    public function read(): RememberedEmailStorageResult
    {
        try {
            if (! $this->filesystem->exists($this->path)) {
                return new RememberedEmailStorageResult(
                    RememberedEmailStorageStatus::NotFound,
                );
            }

            return new RememberedEmailStorageResult(
                RememberedEmailStorageStatus::Found,
                $this->encrypter->decryptString(
                    $this->filesystem->get($this->path, true),
                ),
            );
        } catch (Throwable) {
            return new RememberedEmailStorageResult(
                RememberedEmailStorageStatus::Unavailable,
            );
        }
    }

    public function set(string $value): bool
    {
        try {
            $directory = dirname($this->path);

            if (! $this->filesystem->isDirectory($directory)) {
                $created = $this->filesystem->makeDirectory(
                    $directory,
                    0700,
                    true,
                    true,
                );

                if (! $created) {
                    return false;
                }
            }

            $this->filesystem->chmod($directory, 0700);

            $encrypted = $this->encrypter->encryptString($value);

            $this->filesystem->replace($this->path, $encrypted, 0600);
            $this->filesystem->chmod($this->path, 0600);

            return $this->filesystem->exists($this->path)
                && hash_equals($encrypted, $this->filesystem->get($this->path, true));
        } catch (Throwable) {
            return false;
        }
    }

    public function delete(): bool
    {
        try {
            return ! $this->filesystem->exists($this->path)
                || $this->filesystem->delete($this->path);
        } catch (Throwable) {
            return false;
        }
    }
}
