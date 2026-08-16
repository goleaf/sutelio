<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table): void {
            $table->index(
                ['workspace_id', 'user_id', 'created_at', 'id'],
                'activity_logs_workspace_user_created_index',
            );
            $table->index(
                ['workspace_id', 'event', 'created_at', 'id'],
                'activity_logs_workspace_event_created_index',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table): void {
            $table->dropIndex('activity_logs_workspace_user_created_index');
            $table->dropIndex('activity_logs_workspace_event_created_index');
        });
    }
};
