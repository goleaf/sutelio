<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Seeder;
use LogicException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('Demo users may only be seeded in local or testing environments.');
        }

        $users = [
            [
                'name' => 'Demo User',
                'email' => 'demo@example.com',
                'preferences' => [
                    'timezone' => 'America/New_York',
                    'language' => 'en',
                    'default_view' => 'list',
                ],
            ],
            [
                'name' => 'Alice Chen',
                'email' => 'alice@example.com',
                'preferences' => [
                    'timezone' => 'Asia/Tokyo',
                    'language' => 'lt',
                    'default_view' => 'list',
                ],
            ],
            [
                'name' => 'Bob Smith',
                'email' => 'bob@example.com',
                'preferences' => [
                    'timezone' => 'Europe/Vilnius',
                    'language' => 'ru',
                    'default_view' => 'board',
                ],
            ],
        ];

        foreach ($users as $data) {
            $preferences = $data['preferences'];
            unset($data['preferences']);

            $user = User::query()->firstOrCreate(
                ['email' => $data['email']],
                User::factory()->make($data)->getAttributes(),
            );

            UserPreference::query()->updateOrCreate(
                ['user_id' => $user->id],
                $preferences,
            );
        }

        $this->command->info('Created 3 users with preferences.');
    }
}
