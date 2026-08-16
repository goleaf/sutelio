<?php

declare(strict_types=1);

use App\Models\UserPreference;

test('start page preferences resolve only supported named routes', function () {
    expect(UserPreference::startRoute('dashboard'))->toBe('dashboard')
        ->and(UserPreference::startRoute('tasks'))->toBe('todos.index')
        ->and(UserPreference::startRoute('projects'))->toBe('projects')
        ->and(UserPreference::startRoute('calendar'))->toBe('calendar')
        ->and(UserPreference::startRoute('unsupported'))->toBe('dashboard')
        ->and(UserPreference::startRoute(null))->toBe('dashboard');
});
