<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TaskStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<TaskStatus> */
class TaskStatusFactory extends Factory
{
    protected $model = TaskStatus::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->word().' '.fake()->word();

        return [
            'workspace_id' => WorkspaceFactory::new(),
            'key' => Str::slug($name, '_').'_'.Str::lower(Str::random(5)),
            'name' => Str::headline($name),
            'translation_key' => null,
            'color' => fake()->hexColor(),
            'position' => fake()->numberBetween(3, 40),
            'is_default' => false,
            'is_completed' => false,
            'is_completion_target' => false,
            'is_archived' => false,
        ];
    }

    public function asDefault(): static
    {
        return $this
            ->state(fn (): array => ['is_default' => true])
            ->afterMaking(function (TaskStatus $status): void {
                TaskStatus::query()
                    ->where('workspace_id', $status->workspace_id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            });
    }

    public function completed(): static
    {
        return $this
            ->state(fn (): array => [
                'is_completed' => true,
                'is_completion_target' => true,
            ])
            ->afterMaking(function (TaskStatus $status): void {
                TaskStatus::query()
                    ->where('workspace_id', $status->workspace_id)
                    ->where('is_completion_target', true)
                    ->update(['is_completion_target' => false]);
            });
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'is_archived' => true,
            'is_default' => false,
            'is_completion_target' => false,
        ]);
    }
}
