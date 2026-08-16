<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/** @mixin ActivityLog */
class ActivityLogResource extends JsonResource
{
    /** @var list<string> */
    private const SAFE_CHANGED_FIELDS = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'start_date',
        'assigned_to',
        'project_id',
        'is_archived',
        'position',
    ];

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $user = $this->getRelation('user');

        return [
            'id' => $this->id,
            'subject_type' => class_basename($this->subject_type),
            'subject_id' => $this->subject_id,
            'subject_label' => $this->subjectLabel(),
            'changed_field' => $this->changedField(),
            'event' => $this->event,
            'user' => $user instanceof User ? [
                'id' => $user->id,
                'name' => $user->name,
            ] : null,
            'created_at' => $this->created_at,
        ];
    }

    private function subjectLabel(): ?string
    {
        $properties = $this->resource->getAttribute('properties');

        if (! is_array($properties)) {
            return null;
        }

        $label = Arr::first([
            Arr::get($properties, 'title'),
            Arr::get($properties, 'name'),
        ], fn (mixed $value): bool => is_string($value) && $value !== '');

        return is_string($label) ? Str::limit(Str::squish($label), 180) : null;
    }

    private function changedField(): ?string
    {
        $properties = $this->resource->getAttribute('properties');
        $field = is_array($properties) ? Arr::get($properties, 'field') : null;

        return is_string($field) && in_array($field, self::SAFE_CHANGED_FIELDS, true)
            ? $field
            : null;
    }
}
