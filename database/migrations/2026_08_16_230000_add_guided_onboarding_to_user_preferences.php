<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_preferences', function (Blueprint $table): void {
            $table->string('week_start')->default('sunday');
            $table->unsignedInteger('onboarding_version')->default(1);
            $table->string('onboarding_step')->default('welcome');
            $table->uuid('onboarding_run_id')->nullable();
            $table->json('onboarding_state')->nullable();
            $table->timestamp('onboarding_started_at')->nullable();
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->timestamp('onboarding_skipped_at')->nullable();
            $table->timestamp('onboarding_checklist_dismissed_at')->nullable();
        });

        $now = now();

        DB::table('user_preferences')
            ->select('id')
            ->orderBy('id')
            ->each(function (object $preferences) use ($now): void {
                DB::table('user_preferences')
                    ->where('id', $preferences->id)
                    ->update([
                        'onboarding_step' => 'results',
                        'onboarding_run_id' => (string) Str::uuid(),
                        'onboarding_state' => json_encode([], JSON_THROW_ON_ERROR),
                        'onboarding_started_at' => $now,
                        'onboarding_completed_at' => $now,
                        'onboarding_checklist_dismissed_at' => $now,
                    ]);
            });

        Schema::create('onboarding_operations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->uuid('run_id');
            $table->string('step');
            $table->uuid('request_key');
            $table->uuid('result_id')->nullable();
            $table->timestamps();

            $table->unique(
                ['user_id', 'version', 'run_id', 'step'],
                'onboarding_operations_run_step_unique',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboarding_operations');

        Schema::table('user_preferences', function (Blueprint $table): void {
            $table->dropColumn([
                'week_start',
                'onboarding_version',
                'onboarding_step',
                'onboarding_run_id',
                'onboarding_state',
                'onboarding_started_at',
                'onboarding_completed_at',
                'onboarding_skipped_at',
                'onboarding_checklist_dismissed_at',
            ]);
        });
    }
};
