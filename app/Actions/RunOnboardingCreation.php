<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OnboardingStep;
use App\Models\UserPreference;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LogicException;

class RunOnboardingCreation
{
    /** @param Closure(): string $create */
    public function handle(
        UserPreference $preferences,
        OnboardingStep $step,
        string $requestKey,
        Closure $create,
    ): string {
        $runId = $preferences->onboarding_run_id;

        if (! is_string($runId) || $runId === '') {
            throw new LogicException('Onboarding creation requires an active run identifier.');
        }

        return DB::transaction(function () use ($preferences, $step, $requestKey, $create, $runId): string {
            $now = now();

            DB::table('onboarding_operations')->insertOrIgnore([
                'id' => (string) Str::uuid(),
                'user_id' => $preferences->user_id,
                'version' => $preferences->onboarding_version,
                'run_id' => $runId,
                'step' => $step->value,
                'request_key' => $requestKey,
                'result_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $operation = DB::table('onboarding_operations')
                ->where('user_id', $preferences->user_id)
                ->where('version', $preferences->onboarding_version)
                ->where('run_id', $runId)
                ->where('step', $step->value)
                ->first();

            if ($operation === null) {
                throw new LogicException('Onboarding operation could not be resolved.');
            }

            $existingResultId = $operation->result_id ?? null;

            if (is_string($existingResultId) && $existingResultId !== '') {
                return $existingResultId;
            }

            $resultId = $create();

            DB::table('onboarding_operations')
                ->where('id', $operation->id)
                ->update([
                    'result_id' => $resultId,
                    'updated_at' => now(),
                ]);

            return $resultId;
        }, attempts: 5);
    }
}
