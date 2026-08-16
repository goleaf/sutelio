<?php

declare(strict_types=1);

namespace App\Enums;

enum ActivityCategory: string
{
    case Creation = 'creation';
    case Changes = 'changes';
    case Completion = 'completion';
    case Organization = 'organization';
    case Automation = 'automation';

    /** @return list<ActivityEvent> */
    public function events(): array
    {
        return match ($this) {
            self::Creation => [ActivityEvent::Created],
            self::Changes => [
                ActivityEvent::Updated,
                ActivityEvent::Attached,
                ActivityEvent::Detached,
            ],
            self::Completion => [
                ActivityEvent::Completed,
                ActivityEvent::Uncompleted,
            ],
            self::Organization => [
                ActivityEvent::Deleted,
                ActivityEvent::Restored,
                ActivityEvent::Archived,
                ActivityEvent::Unarchived,
                ActivityEvent::Pinned,
                ActivityEvent::Unpinned,
                ActivityEvent::Favorited,
                ActivityEvent::Unfavorited,
            ],
            self::Automation => [ActivityEvent::RecurrenceGenerated],
        };
    }
}
