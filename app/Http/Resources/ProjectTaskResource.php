<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Label;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Todo */
class ProjectTaskResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'assigned_to' => $this->assigned_to,
            'assignee' => $this->relationLoaded('assignee') && $this->assignee
                ? $this->assignee->only(['id', 'name'])
                : null,
            'status' => $this->statusKey(),
            'status_id' => $this->status_id,
            'status_definition' => $this->relationLoaded('statusDefinition') && $this->statusDefinition
                ? $this->statusDefinition($this->statusDefinition)
                : null,
            'priority' => $this->priorityKey(),
            'priority_id' => $this->priority_id,
            'priority_definition' => $this->relationLoaded('priorityDefinition') && $this->priorityDefinition
                ? $this->priorityDefinition($this->priorityDefinition)
                : null,
            'labels' => $this->relationLoaded('labels')
                ? $this->labels->map(fn (Label $label): array => $label->only(['id', 'name', 'color']))->values()->all()
                : [],
            'is_completed' => $this->relationLoaded('statusDefinition')
                ? (bool) $this->statusDefinition?->is_completed
                : $this->completed_at !== null,
            'due_date' => $this->due_date?->toDateString(),
            'position' => $this->position,
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /** @return array{id: string, key: string, name: string, translation_key: string|null, color: string, position: int, is_completed: bool} */
    private function statusDefinition(TaskStatus $status): array
    {
        return [
            'id' => $status->id,
            'key' => $status->key,
            'name' => $this->localizedName($status->translation_key, $status->name),
            'translation_key' => $status->translation_key,
            'color' => $status->color,
            'position' => $status->position,
            'is_completed' => $status->is_completed,
        ];
    }

    /** @return array{id: string, key: string, name: string, translation_key: string|null, color: string, position: int} */
    private function priorityDefinition(TaskPriority $priority): array
    {
        return [
            'id' => $priority->id,
            'key' => $priority->key,
            'name' => $this->localizedName($priority->translation_key, $priority->name),
            'translation_key' => $priority->translation_key,
            'color' => $priority->color,
            'position' => $priority->position,
        ];
    }

    private function localizedName(?string $translationKey, string $fallback): string
    {
        $translatedName = $translationKey !== null ? __($translationKey) : $fallback;

        return is_string($translatedName) ? $translatedName : $fallback;
    }
}
