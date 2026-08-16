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
        Schema::table('todos', function (Blueprint $table): void {
            $table->index(
                ['workspace_id', 'project_id', 'is_archived', 'status_id', 'position', 'id'],
                'todos_workspace_project_archive_status_position_index',
            );
            $table->index(
                ['workspace_id', 'project_id', 'is_archived', 'priority_id', 'position', 'id'],
                'todos_workspace_project_archive_priority_position_index',
            );
            $table->index(
                ['workspace_id', 'project_id', 'is_archived', 'assigned_to', 'position', 'id'],
                'todos_workspace_project_archive_assignee_position_index',
            );
            $table->index(
                ['workspace_id', 'project_id', 'is_archived', 'completed_at', 'due_date', 'id'],
                'todos_workspace_project_archive_completion_due_index',
            );
            $table->index(
                ['workspace_id', 'project_id', 'is_archived', 'due_date', 'position', 'id'],
                'todos_workspace_project_archive_due_position_index',
            );
            $table->index(
                ['workspace_id', 'project_id', 'is_archived', 'updated_at', 'id'],
                'todos_workspace_project_archive_updated_index',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('todos', function (Blueprint $table): void {
            $table->dropIndex('todos_workspace_project_archive_status_position_index');
            $table->dropIndex('todos_workspace_project_archive_priority_position_index');
            $table->dropIndex('todos_workspace_project_archive_assignee_position_index');
            $table->dropIndex('todos_workspace_project_archive_completion_due_index');
            $table->dropIndex('todos_workspace_project_archive_due_position_index');
            $table->dropIndex('todos_workspace_project_archive_updated_index');
        });
    }
};
