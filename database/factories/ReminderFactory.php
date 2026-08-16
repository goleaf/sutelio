<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReminderStatus;
use App\Enums\ReminderType;
use App\Models\Reminder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reminder>
 */
class ReminderFactory extends Factory
{
    protected $model = Reminder::class;

    public function configure(): static
    {
        return $this->afterMaking(function (Reminder $reminder): void {
            if ($reminder->is_sent && $reminder->status === ReminderStatus::Pending) {
                $reminder->status = ReminderStatus::Delivered;
                $reminder->delivered_at ??= now();
            }
        });
    }

    public function definition(): array
    {
        return [
            'todo_id' => TodoFactory::new(),
            'user_id' => UserFactory::new(),
            'reminded_at' => fake()->dateTimeBetween('now', '+1 week'),
            'is_sent' => false,
            'type' => fake()->randomElement(ReminderType::cases()),
            'status' => ReminderStatus::Pending,
            'claim_token' => null,
            'attempts' => 0,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (): array => [
            'status' => ReminderStatus::Pending,
            'is_sent' => false,
            'delivered_at' => null,
            'failed_at' => null,
            'cancelled_at' => null,
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (): array => [
            'status' => ReminderStatus::Delivered,
            'is_sent' => true,
            'delivered_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => ReminderStatus::Failed,
            'is_sent' => false,
            'attempts' => Reminder::MAX_ATTEMPTS,
            'failed_at' => now(),
            'last_error' => 'Test delivery failure',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => ReminderStatus::Cancelled,
            'is_sent' => false,
            'cancelled_at' => now(),
        ]);
    }
}
