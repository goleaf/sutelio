<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TaskPriority;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<TaskPriority> */
class TaskPriorityFactory extends Factory
{
    protected $model = TaskPriority::class;

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
            'position' => fake()->numberBetween(5, 40),
            'is_default' => false,
            'is_archived' => false,
        ];
    }

    public function asDefault(): static
    {
        return $this
            ->state(fn (): array => ['is_default' => true])
            ->afterMaking(function (TaskPriority $priority): void {
                TaskPriority::query()
                    ->where('workspace_id', $priority->workspace_id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            });
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'is_archived' => true,
            'is_default' => false,
        ]);
    }
}
