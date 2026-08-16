<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Models\UserPreference;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CalendarIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'view' => ['sometimes', 'string', Rule::in(['month', 'week', 'agenda'])],
            'date' => ['sometimes', Rule::date()->format('Y-m-d')],
        ];
    }

    /**
     * @return array{view: string, anchor_date: string, today_date: string, start_date: string, end_date: string, week_start: string}
     */
    public function calendarState(string $timezone, string $weekStart): array
    {
        $validatedView = $this->validated('view');
        $view = is_string($validatedView) ? $validatedView : 'month';
        $validatedDate = $this->validated('date');
        $anchorDate = is_string($validatedDate)
            ? CarbonImmutable::createFromFormat('!Y-m-d', $validatedDate, $timezone)
            : CarbonImmutable::now($timezone)->startOfDay();
        $normalizedWeekStart = in_array($weekStart, UserPreference::WEEK_STARTS, true)
            ? $weekStart
            : 'sunday';
        $firstDay = $normalizedWeekStart === 'monday'
            ? CarbonInterface::MONDAY
            : CarbonInterface::SUNDAY;
        $lastDay = $normalizedWeekStart === 'monday'
            ? CarbonInterface::SUNDAY
            : CarbonInterface::SATURDAY;

        [$startDate, $endDate] = match ($view) {
            'week' => [
                $anchorDate->startOfWeek($firstDay),
                $anchorDate->endOfWeek($lastDay),
            ],
            'agenda' => [
                $anchorDate->startOfDay(),
                $anchorDate->addDays(30)->endOfDay(),
            ],
            default => [
                $anchorDate->startOfMonth()->startOfWeek($firstDay),
                $anchorDate->endOfMonth()->endOfWeek($lastDay),
            ],
        };

        return [
            'view' => $view,
            'anchor_date' => $anchorDate->toDateString(),
            'today_date' => CarbonImmutable::now($timezone)->toDateString(),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'week_start' => $normalizedWeekStart,
        ];
    }
}
