<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Config\Repository;
use JsonException;
use Throwable;

class RememberedEmailStore
{
    private const int MAX_EMAILS = 5;

    private bool $hasLoaded = false;

    /** @var list<string>|null */
    private ?array $cachedEmails = null;

    public function __construct(
        private Repository $config,
        private EncryptedRememberedEmailStorage $storage,
    ) {}

    /** @return list<string> */
    public function emails(): array
    {
        return $this->load() ?? [];
    }

    public function remember(string $email): bool
    {
        if (! $this->isNative()) {
            return false;
        }

        $normalizedEmail = $this->normalize($email);
        $emails = $this->load();

        if ($normalizedEmail === null || $emails === null) {
            return false;
        }

        $remembered = [$normalizedEmail];

        foreach ($emails as $existingEmail) {
            if ($this->identity($existingEmail) === $this->identity($normalizedEmail)) {
                continue;
            }

            $remembered[] = $existingEmail;
        }

        return $this->write(array_slice($remembered, 0, self::MAX_EMAILS));
    }

    public function forget(string $email): bool
    {
        if (! $this->isNative()) {
            return false;
        }

        $normalizedEmail = $this->normalize($email);
        $emails = $this->load();

        if ($normalizedEmail === null || $emails === null) {
            return false;
        }

        $remaining = array_values(array_filter(
            $emails,
            fn (string $existingEmail): bool => $this->identity($existingEmail) !== $this->identity($normalizedEmail),
        ));

        if ($remaining === $emails) {
            return true;
        }

        if ($remaining === []) {
            try {
                $deleted = $this->storage->delete();
            } catch (Throwable) {
                return false;
            }

            if ($deleted) {
                $this->cachedEmails = [];
            }

            return $deleted;
        }

        return $this->write($remaining);
    }

    public function isNative(): bool
    {
        return in_array(
            $this->config->get('nativephp-internal.platform'),
            ['android', 'ios'],
            true,
        );
    }

    public function deviceEmailPickerAvailable(): bool
    {
        return $this->config->get('nativephp-internal.platform') === 'android';
    }

    /** @return list<string>|null */
    private function load(): ?array
    {
        if ($this->hasLoaded) {
            return $this->cachedEmails;
        }

        $this->hasLoaded = true;

        if (! $this->isNative()) {
            return $this->cachedEmails = [];
        }

        try {
            $result = $this->storage->read();
        } catch (Throwable) {
            return null;
        }

        if ($result->missing()) {
            return $this->cachedEmails = [];
        }

        if (! $result->found() || $result->value === null) {
            return null;
        }

        try {
            $payload = json_decode($result->value, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $this->cachedEmails = [];
        }

        if (! is_array($payload) || ($payload['version'] ?? null) !== 1 || ! is_array($payload['emails'] ?? null)) {
            return $this->cachedEmails = [];
        }

        $emails = [];
        $identities = [];

        foreach ($payload['emails'] as $candidate) {
            if (! is_string($candidate)) {
                continue;
            }

            $normalizedEmail = $this->normalize($candidate);

            if ($normalizedEmail === null) {
                continue;
            }

            $identity = $this->identity($normalizedEmail);

            if (isset($identities[$identity])) {
                continue;
            }

            $emails[] = $normalizedEmail;
            $identities[$identity] = true;

            if (count($emails) === self::MAX_EMAILS) {
                break;
            }
        }

        return $this->cachedEmails = $emails;
    }

    /** @param list<string> $emails */
    private function write(array $emails): bool
    {
        try {
            $payload = json_encode([
                'version' => 1,
                'emails' => $emails,
            ], JSON_THROW_ON_ERROR);
            $stored = $this->storage->set($payload);
        } catch (Throwable) {
            return false;
        }

        if ($stored) {
            $this->cachedEmails = $emails;
        }

        return $stored;
    }

    private function normalize(string $email): ?string
    {
        $email = trim($email);

        if (strlen($email) > 254 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return null;
        }

        return $email;
    }

    private function identity(string $email): string
    {
        return mb_strtolower($email);
    }
}
