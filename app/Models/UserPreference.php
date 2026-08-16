<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Enums\OnboardingStep;
use Database\Factories\UserPreferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UserPreference extends Model
{
    /** @use HasFactory<UserPreferenceFactory> */
    use HasFactory, HasUuid;

    public const int ONBOARDING_VERSION = 1;

    public const array DATE_FORMATS = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd.m.Y'];

    public const array TIME_FORMATS = ['H:i', 'h:i A'];

    public const array DEFAULT_VIEWS = ['list', 'board', 'calendar'];

    public const array START_PAGE_ROUTES = [
        'dashboard' => 'dashboard',
        'tasks' => 'todos.index',
        'projects' => 'projects',
        'calendar' => 'calendar',
    ];

    public const array START_PAGES = ['dashboard', 'tasks', 'projects', 'calendar'];

    public const array WEEK_STARTS = ['sunday', 'monday'];

    protected $attributes = [
        'week_start' => 'sunday',
        'onboarding_version' => self::ONBOARDING_VERSION,
        'onboarding_step' => 'welcome',
    ];

    protected $fillable = [
        'user_id', 'timezone', 'language', 'date_format', 'time_format',
        'theme', 'default_view', 'start_page', 'week_start',
        'notification_email', 'notification_browser', 'notification_in_app',
        'onboarding_version', 'onboarding_step', 'onboarding_run_id', 'onboarding_state',
        'onboarding_started_at', 'onboarding_completed_at', 'onboarding_skipped_at',
        'onboarding_checklist_dismissed_at',
    ];

    protected function casts(): array
    {
        return [
            'notification_email' => 'boolean',
            'notification_browser' => 'boolean',
            'notification_in_app' => 'boolean',
            'onboarding_step' => OnboardingStep::class,
            'onboarding_state' => 'array',
            'onboarding_started_at' => 'datetime',
            'onboarding_completed_at' => 'datetime',
            'onboarding_skipped_at' => 'datetime',
            'onboarding_checklist_dismissed_at' => 'datetime',
        ];
    }

    /**
     * @return array{timezone: string, language: string, date_format: string, time_format: string, theme: string, default_view: string, start_page: string, week_start: string, notification_email: bool, notification_browser: bool, notification_in_app: bool}
     */
    public static function defaults(): array
    {
        return [
            'timezone' => 'UTC',
            'language' => 'en',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'theme' => 'system',
            'default_view' => 'list',
            'start_page' => 'dashboard',
            'week_start' => 'sunday',
            'notification_email' => true,
            'notification_browser' => true,
            'notification_in_app' => true,
        ];
    }

    /**
     * @return array{onboarding_version: int, onboarding_step: string, onboarding_run_id: string, onboarding_state: array<never, never>, onboarding_started_at: null, onboarding_completed_at: null, onboarding_skipped_at: null, onboarding_checklist_dismissed_at: null}
     */
    public static function pendingOnboardingDefaults(): array
    {
        return [
            'onboarding_version' => self::ONBOARDING_VERSION,
            'onboarding_step' => OnboardingStep::Welcome->value,
            'onboarding_run_id' => (string) Str::uuid(),
            'onboarding_state' => [],
            'onboarding_started_at' => null,
            'onboarding_completed_at' => null,
            'onboarding_skipped_at' => null,
            'onboarding_checklist_dismissed_at' => null,
        ];
    }

    public function requiresOnboarding(): bool
    {
        return $this->onboarding_run_id !== null
            && $this->onboarding_completed_at === null
            && $this->onboarding_skipped_at === null;
    }

    public function onboardingStep(): OnboardingStep
    {
        $step = $this->getAttribute('onboarding_step');

        return $step instanceof OnboardingStep
            ? $step
            : OnboardingStep::tryFrom((string) $step) ?? OnboardingStep::Welcome;
    }

    public static function startRoute(?string $startPage): string
    {
        return self::START_PAGE_ROUTES[$startPage ?? 'dashboard'] ?? 'dashboard';
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
