<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use LogicException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('Demo data may only be seeded in local or testing environments.');
        }

        $this->call(DemoSeeder::class);
    }
}
