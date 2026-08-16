<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use LogicException;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('Demo data may only be seeded in local or testing environments.');
        }

        $this->call([
            UserSeeder::class,
            WorkspaceSeeder::class,
            ProjectSeeder::class,
            LabelSeeder::class,
            TagSeeder::class,
            TodoSeeder::class,
            ChecklistSeeder::class,
            CommentSeeder::class,
            ReminderSeeder::class,
            ActivityLogSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
