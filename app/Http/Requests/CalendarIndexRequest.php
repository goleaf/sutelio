<?php

namespace App\Http\Requests;

use App\Models\User;
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
     * @return array{view: string, anchor_date: string, today_date: string, start_date: string, end_date: string}
     */
    public function calendarState(string $timezone): array
    {
        $validatedView = $this->validated('view');
        $view = is_string($validatedView) ? $validatedView : 'month';
        $validatedDate = $this->validated('date');
        $anchorDate = is_string($validatedDate)
            ? CarbonImmutable::createFromFormat('!Y-m-d', $validatedDate, $timezone)
            : CarbonImmutable::now($timezone)->startOfDay();

        [$startDate, $endDate] = match ($view) {
            'week' => [
                $anchorDate->startOfWeek(CarbonInterface::SUNDAY),
                $anchorDate->endOfWeek(CarbonInterface::SATURDAY),
            ],
            'agenda' => [
                $anchorDate->startOfDay(),
                $anchorDate->addDays(30)->endOfDay(),
            ],
            default => [
                $anchorDate->startOfMonth()->startOfWeek(CarbonInterface::SUNDAY),
                $anchorDate->endOfMonth()->endOfWeek(CarbonInterface::SATURDAY),
            ],
        };

        return [
            'view' => $view,
            'anchor_date' => $anchorDate->toDateString(),
            'today_date' => CarbonImmutable::now($timezone)->toDateString(),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ];
    }
}
