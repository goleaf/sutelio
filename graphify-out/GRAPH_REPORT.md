# Graph Report - xiaomi-mimo  (2026-08-15)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 3454 nodes · 7855 edges · 223 communities (191 shown, 32 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 77 edges (avg confidence: 0.67)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `e8528081`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Community 0
- Community 1
- Community 2
- Community 3
- Community 4
- Community 5
- Community 6
- Community 7
- Community 8
- Community 9
- Community 10
- Community 11
- Community 12
- Community 13
- Community 14
- Community 15
- Community 16
- Community 17
- Community 18
- Community 19
- Community 20
- Community 21
- Community 22
- Community 23
- Community 24
- Community 25
- Community 26
- Community 27
- Community 28
- Community 29
- Community 30
- Community 31
- Community 32
- Community 33
- Community 34
- Community 35
- Community 36
- Community 37
- Community 38
- Community 39
- Community 40
- Community 41
- Community 42
- Community 43
- Community 44
- Community 45
- Community 46
- Community 47
- Community 48
- Community 49
- Community 50
- Community 51
- Community 52
- Community 53
- Community 54
- Community 55
- Community 56
- Community 57
- Community 58
- Community 59
- Community 60
- Community 61
- Community 62
- Community 63
- Community 64
- Community 65
- Community 66
- Community 67
- Community 68
- Community 69
- Community 70
- Community 71
- Community 72
- Community 73
- Community 74
- Community 75
- Community 76
- Community 77
- Community 78
- Community 79
- Community 80
- Community 81
- Community 82
- Community 83
- Community 84
- Community 85
- Community 86
- Community 87
- Community 88
- Community 89
- Community 90
- Community 91
- Community 92
- Community 93
- Community 94
- Community 95
- Community 96
- Community 97
- Community 98
- Community 99
- Community 100
- Community 101
- Community 102
- Community 103
- Community 104
- Community 105
- Community 106
- Community 107
- Community 108
- Community 109
- Community 110
- Community 111
- Community 112
- Community 113
- Community 114
- Community 115
- Community 116
- Community 117
- Community 118
- Community 119
- Community 120
- Community 121
- Community 122
- Community 123
- Community 124
- Community 125
- Community 126
- Community 127
- Community 128
- Community 129
- Community 130
- Community 131
- Community 132
- Community 133
- Community 134
- Community 155
- Community 156
- Community 157
- Community 159
- Community 160
- Community 161
- Community 162
- Community 163
- Community 164
- Community 167
- Community 216
- Community 217
- Community 218
- Community 219
- Community 220
- Community 221
- Community 222

## God Nodes (most connected - your core abstractions)
1. `Workspace` - 353 edges
2. `User` - 257 edges
3. `Todo` - 235 edges
4. `cn()` - 97 edges
5. `WorkspaceMember` - 84 edges
6. `useUi()` - 76 edges
7. `Controller` - 69 edges
8. `Project` - 63 edges
9. `Label` - 54 edges
10. `Tag` - 53 edges

## Surprising Connections (you probably didn't know these)
- `insertInboxNotification()` --references_constant--> `User`  [EXTRACTED]
  tests/Feature/NotificationInboxTest.php → app/Models/User.php
- `createApiChecklistUser()` --calls--> `User`  [EXTRACTED]
  tests/Feature/Api/ApiChecklistTest.php → app/Models/User.php
- `createApiCommentUser()` --calls--> `User`  [EXTRACTED]
  tests/Feature/Api/ApiCommentTest.php → app/Models/User.php
- `createVersionedApiActor()` --calls--> `User`  [EXTRACTED]
  tests/Feature/Api/ApiContractTest.php → app/Models/User.php
- `createApiMetadataActor()` --calls--> `User`  [EXTRACTED]
  tests/Feature/Api/ApiLabelTagTest.php → app/Models/User.php

## Import Cycles
- 3-file cycle: `resources/js/components/ui/sidebar/SidebarMenuButton.vue -> resources/js/components/ui/sidebar/SidebarMenuButtonChild.vue -> resources/js/components/ui/sidebar/index.ts -> resources/js/components/ui/sidebar/SidebarMenuButton.vue`

## Communities (223 total, 32 thin omitted)

### Community 0 - "Community 0"
Cohesion: 0.04
Nodes (51): passwordInput, Props, { t }, { verify, isLoading, error, isSupported }, inputRef, props, showPassword, { t } (+43 more)

### Community 1 - "Community 1"
Cohesion: 0.05
Nodes (44): columns, draggedTodoId, drop(), emit, { formatDate, t }, move(), openWithKeyboard(), props (+36 more)

### Community 2 - "Community 2"
Cohesion: 0.04
Nodes (49): Props, props, props, props, props, props, props, props (+41 more)

### Community 3 - "Community 3"
Cohesion: 0.05
Nodes (9): ArchiveTodo, CreateTodo, SyncTodoTag, DetachTagRequest, StoreTodoRequest, Tag, Todo, TodoSeeder (+1 more)

### Community 4 - "Community 4"
Cohesion: 0.05
Nodes (49): Props, { t }, uniqueErrors, { hasSetupData, clearTwoFactorAuthData }, Props, showSetupModal, { t }, emit (+41 more)

### Community 5 - "Community 5"
Cohesion: 0.06
Nodes (21): WorkspaceRole, Workspace, WorkspaceMember, Illuminate\Database\Eloquent\Relations\HasMany, Illuminate\Foundation\Testing\RefreshDatabase, createApiChecklistUser(), createApiCommentUser(), createApiMetadataActor() (+13 more)

### Community 6 - "Community 6"
Cohesion: 0.05
Nodes (17): CreateNewUser, ResetUserPassword, ApiRegisterRequest, PasswordUpdateRequest, ProfileDeleteRequest, ProfileUpdateRequest, TwoFactorAuthenticationRequest, Illuminate\Contracts\Validation\ValidationRule (+9 more)

### Community 7 - "Community 7"
Cohesion: 0.09
Nodes (21): archiveProject(), completedTodos, deletingTodo, duplicateProject(), filteredTodos, { formatDate: formatLocalizedDate, formatNumber, t }, openTodos, processingProjectAction (+13 more)

### Community 8 - "Community 8"
Cohesion: 0.07
Nodes (27): ActivityController, UserController, WorkspaceOwnershipController, CalendarController, Controller, DashboardController, ProjectIndexController, BackupController (+19 more)

### Community 9 - "Community 9"
Cohesion: 0.07
Nodes (20): TaskPriorityController, TaskStatusController, WorkspaceMemberController, TodoIndexController, ActivityLogResource, AttachmentResource, ChecklistItemResource, ChecklistResource (+12 more)

### Community 10 - "Community 10"
Cohesion: 0.06
Nodes (20): DeleteAttachment, DownloadAttachment, UploadAttachment, AttachmentController, AttachmentController, ExportController, StoreAttachmentRequest, Attachment (+12 more)

### Community 11 - "Community 11"
Cohesion: 0.05
Nodes (18): Illuminate\Auth\Access\AuthorizationException, Illuminate\Database\QueryException, Illuminate\Database\UniqueConstraintViolationException, Illuminate\Foundation\ArrayMaintenanceMode, Illuminate\Support\Arr, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\File, Illuminate\Support\Facades\Validator (+10 more)

### Community 12 - "Community 12"
Cohesion: 0.06
Nodes (16): LogActivity, ActivityLog, Comment, ActivityService, DateTimeInterface, Illuminate\Database\Eloquent\Concerns\HasUuids, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model (+8 more)

### Community 13 - "Community 13"
Cohesion: 0.04
Nodes (51): apply(), clear(), currentFilters(), direction, emit, mobileFiltersOpen, perPage, priority (+43 more)

### Community 14 - "Community 14"
Cohesion: 0.06
Nodes (32): className, Props, Props, Props, Props, Props, { breadcrumbs = [] }, browserPermission (+24 more)

### Community 15 - "Community 15"
Cohesion: 0.08
Nodes (11): User, CommentPolicy, DatabaseBackupPolicy, TodoPolicy, WorkspacePolicy, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Laravel\Fortify\Contracts\PasskeyUser (+3 more)

### Community 16 - "Community 16"
Cohesion: 0.04
Nodes (40): emits, forwarded, props, delegatedProps, emits, forwarded, props, delegatedProps (+32 more)

### Community 17 - "Community 17"
Cohesion: 0.04
Nodes (40): { formatDate: formatLocalizedDate, formatNumber, t }, maxVal, props, toneClasses, props, props, formatDate(), activeNavItem (+32 more)

### Community 18 - "Community 18"
Cohesion: 0.04
Nodes (38): colors, { copy }, emit, form, iconOptions, ProjectForm, ProjectResponse, props (+30 more)

### Community 19 - "Community 19"
Cohesion: 0.07
Nodes (10): CreateReminder, AuthController, ReminderController, ProjectController, TaskStatusController, ApiLoginRequest, ManageTaskStatusRequest, ReorderTaskStatusesRequest (+2 more)

### Community 20 - "Community 20"
Cohesion: 0.09
Nodes (9): CreateDatabaseBackup, RestoreDatabaseBackup, BackupController, CreateDatabaseBackupRequest, RestoreDatabaseBackupRequest, BackupService, Illuminate\Contracts\Foundation\MaintenanceMode, SQLite3 (+1 more)

### Community 21 - "Community 21"
Cohesion: 0.06
Nodes (33): auth, { isCurrentUrl, whenCurrentUrl }, mainNavItems, page, Props, rightNavItems, { t }, Props (+25 more)

### Community 22 - "Community 22"
Cohesion: 0.19
Nodes (7): NativeServiceProvider, Illuminate\Cache\RateLimiting\Limit, Illuminate\Foundation\Events\DiagnosingHealth, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\ServiceProvider, Illuminate\Validation\Rules\Password, Laravel\Fortify\Fortify

### Community 23 - "Community 23"
Cohesion: 0.08
Nodes (10): CreateWorkspace, DeleteWorkspace, DuplicateWorkspace, UpdateWorkspace, WorkspaceController, WorkspaceController, DuplicateWorkspaceRequest, StoreWorkspaceRequest (+2 more)

### Community 24 - "Community 24"
Cohesion: 0.09
Nodes (8): DeleteProfileAvatar, UpdateProfileAvatar, ProfileController, ProfileAvatarDeleteRequest, ProfileAvatarUpdateRequest, SqliteHealthService, Illuminate\Config\Repository, Illuminate\Database\DatabaseManager

### Community 25 - "Community 25"
Cohesion: 0.09
Nodes (7): ManageTaskPriority, TaskPriorityController, DeleteTaskPriorityRequest, ManageTaskPriorityRequest, ReorderTaskPrioritiesRequest, StoreTaskPriorityRequest, TaskPriority

### Community 26 - "Community 26"
Cohesion: 0.17
Nodes (8): HandleAppearance, SetLocale, LoginResponse, Closure, Illuminate\Support\Facades\App, Illuminate\Support\Facades\View, Laravel\Fortify\Contracts\LoginResponse, Symfony\Component\HttpFoundation\Response

### Community 27 - "Community 27"
Cohesion: 0.05
Nodes (39): concurrently, eslint, eslint-config-prettier, eslint-import-resolver-typescript, @eslint/js, eslint-plugin-import, eslint-plugin-vue, @laravel/vite-plugin-wayfinder (+31 more)

### Community 28 - "Community 28"
Cohesion: 0.06
Nodes (27): createWorkspace(), deleteRequest, deleteWorkspace(), deletingWorkspace, duplicateForm, duplicateWorkspace(), duplicatingWorkspace, editForm (+19 more)

### Community 29 - "Community 29"
Cohesion: 0.07
Nodes (10): Project, UserPreference, ProjectPolicy, ProjectSeeder, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\Queue (+2 more)

### Community 30 - "Community 30"
Cohesion: 0.09
Nodes (9): CreateTag, DeleteTag, UpdateTag, TagController, TagController, AttachTagRequest, DeleteTagRequest, StoreTagRequest (+1 more)

### Community 31 - "Community 31"
Cohesion: 0.05
Nodes (37): axios, class-variance-authority, clsx, @dnd-kit/core, @dnd-kit/sortable, @dnd-kit/utilities, @inertiajs/core, @inertiajs/vite (+29 more)

### Community 32 - "Community 32"
Cohesion: 0.05
Nodes (25): emits, forwarded, props, delegatedProps, emits, forwarded, props, props (+17 more)

### Community 33 - "Community 33"
Cohesion: 0.10
Nodes (8): CreateLabel, DeleteLabel, UpdateLabel, LabelController, LabelController, AttachLabelRequest, DeleteLabelRequest, StoreLabelRequest

### Community 34 - "Community 34"
Cohesion: 0.06
Nodes (27): navigationMenuTriggerStyle, delegatedProps, emits, forwarded, props, delegatedProps, emits, forwarded (+19 more)

### Community 35 - "Community 35"
Cohesion: 0.05
Nodes (30): props, editForm, { formatDate, formatNumber, t }, props, showEditDialog, switchRequest, toast, WorkspaceResponse (+22 more)

### Community 36 - "Community 36"
Cohesion: 0.09
Nodes (25): cancelEditingLabel(), cancelEditingTag(), canManage, createLabel(), createTag(), deleteMetadata(), deleteRequest, DeleteTarget (+17 more)

### Community 37 - "Community 37"
Cohesion: 0.06
Nodes (26): emit, EmptyStateStatus, confirm(), confirmationMatches, confirmationValue, emit, props, attachments (+18 more)

### Community 38 - "Community 38"
Cohesion: 0.09
Nodes (8): ArchiveProject, CreateProject, DuplicateProject, UpdateProject, ProjectController, StoreProjectRequest, UpdateProjectRequest, ProjectResource

### Community 39 - "Community 39"
Cohesion: 0.08
Nodes (26): AssignableWorkspaceRole, avatarTones, cancelInvitation(), cancelRequest, filteredMembers, { formatDate, formatNumber, t }, invitationToCancel, inviteForm (+18 more)

### Community 40 - "Community 40"
Cohesion: 0.10
Nodes (6): CompleteTodo, DeleteTodo, UncompleteTodo, TodoController, UpdateTodoRequest, TodoDetailQuery

### Community 41 - "Community 41"
Cohesion: 0.07
Nodes (29): DOM, DOM.Iterable, ESNext, node, resources/js/**/*.d.ts, resources/js/**/*.ts, resources/js/**/*.tsx, resources/js/**/*.vue (+21 more)

### Community 42 - "Community 42"
Cohesion: 0.10
Nodes (20): { appearance, updateAppearance }, { t }, tabs, appearance, getStoredAppearance(), handleSystemThemeChange(), initializeTheme(), mediaQuery() (+12 more)

### Community 43 - "Community 43"
Cohesion: 0.09
Nodes (21): agendaGroups, calendarDays, CalendarProject, CalendarTodo, CalendarView, { copy, formatDate, formatNumber }, currentDate, currentPeriodLabel (+13 more)

### Community 44 - "Community 44"
Cohesion: 0.12
Nodes (9): CancelWorkspaceInvitation, InviteToWorkspace, ResendWorkspaceInvitation, WorkspaceInvitationController, WorkspaceInvitationController, CancelWorkspaceInvitationRequest, InviteMemberRequest, WorkspaceRole (+1 more)

### Community 45 - "Community 45"
Cohesion: 0.11
Nodes (4): ManageTaskStatus, DeleteTaskStatusRequest, StoreTaskStatusRequest, TaskStatus

### Community 46 - "Community 46"
Cohesion: 0.10
Nodes (8): RemoveFromWorkspace, WorkspaceRole, UpdateWorkspaceMemberRole, WorkspaceMemberController, RemoveWorkspaceMemberRequest, WorkspaceRole, UpdateWorkspaceMemberRoleRequest, createMembershipManagementWorkspace()

### Community 47 - "Community 47"
Cohesion: 0.10
Nodes (24): busyKey, checklistDrafts, checklistRequest, confirmDelete(), createChecklist(), createItem(), DeleteTarget, emit (+16 more)

### Community 48 - "Community 48"
Cohesion: 0.11
Nodes (24): createDefinition(), createForm, Definition, deleteDefinition(), deleteRequest, deleting, editForm, editing (+16 more)

### Community 49 - "Community 49"
Cohesion: 0.09
Nodes (16): emits, forwarded, props, props, delegatedProps, props, props, props (+8 more)

### Community 50 - "Community 50"
Cohesion: 0.10
Nodes (21): emits, isMobile, open, openMobile, props, setOpen(), setOpenMobile(), state (+13 more)

### Community 51 - "Community 51"
Cohesion: 0.15
Nodes (7): CreateComment, DeleteComment, UpdateComment, CommentController, CommentController, CommentIndexRequest, StoreCommentRequest

### Community 52 - "Community 52"
Cohesion: 0.11
Nodes (9): ActivityLogSeeder, CommentSeeder, DatabaseSeeder, NotificationSeeder, ReminderSeeder, TagSeeder, UserSeeder, WorkspaceSeeder (+1 more)

### Community 53 - "Community 53"
Cohesion: 0.09
Nodes (17): busyKey, commentRequest, comments, commentTotal, deleteRequest, deletingComment, editDrafts, editingId (+9 more)

### Community 54 - "Community 54"
Cohesion: 0.10
Nodes (7): Checklist, WorkspaceMemberFactory, ChecklistSeeder, Laravel\Sanctum\PersonalAccessToken, createVersionedApiActor(), WorkspaceRole, taskDetailActor()

### Community 55 - "Community 55"
Cohesion: 0.13
Nodes (6): NotificationIndexQuery, TodoIndexQuery, TodoFilterService, TodoSortService, Illuminate\Contracts\Pagination\LengthAwarePaginator, Illuminate\Notifications\DatabaseNotification

### Community 56 - "Community 56"
Cohesion: 0.19
Nodes (3): ImportController, ImportWorkspaceRequest, ImportService

### Community 57 - "Community 57"
Cohesion: 0.06
Nodes (34): deleted(), emit, { t }, completionRequest, deleteRequest, deleteTodo(), emit, { formatDate, t } (+26 more)

### Community 58 - "Community 58"
Cohesion: 0.17
Nodes (8): ConfigureTodoRecurrence, GenerateRecurringTodoOccurrence, UpdateTodo, CarbonImmutable, RecurrenceSchedule, Carbon\CarbonImmutable, Carbon\CarbonInterface, LogicException

### Community 59 - "Community 59"
Cohesion: 0.15
Nodes (5): FavoriteTodo, PinTodo, ReorderTodos, TodoController, Illuminate\Http\RedirectResponse

### Community 60 - "Community 60"
Cohesion: 0.14
Nodes (5): BulkDeleteTodos, BulkUpdateTodos, DuplicateTodo, ResolveWorkspaceTodos, TransitionTodoDefinitions

### Community 61 - "Community 61"
Cohesion: 0.14
Nodes (17): { copy, formatDate, formatNumber }, filtering, markAllRead(), markingAll, markRead(), notificationBody(), notificationIcon(), NotificationItem (+9 more)

### Community 62 - "Community 62"
Cohesion: 0.14
Nodes (4): DashboardQuery, CarbonImmutable, Illuminate\Database\Eloquent\Builder, Illuminate\Support\Collection

### Community 63 - "Community 63"
Cohesion: 0.11
Nodes (14): delegatedProps, emits, forwarded, props, delegatedProps, forwarded, props, forwarded (+6 more)

### Community 64 - "Community 64"
Cohesion: 0.15
Nodes (7): AttachmentFactory, LabelFactory, TagFactory, TaskPriorityFactory, TaskStatusFactory, UserPreferenceFactory, Illuminate\Database\Eloquent\Factories\Factory

### Community 65 - "Community 65"
Cohesion: 0.19
Nodes (4): CancelReminder, DeliverClaimedReminder, Reminder, ReminderPolicy

### Community 66 - "Community 66"
Cohesion: 0.15
Nodes (4): CreateChecklist, ManageChecklist, ChecklistController, StoreChecklistRequest

### Community 67 - "Community 67"
Cohesion: 0.23
Nodes (4): ManageChecklistItem, ToggleChecklistItem, ChecklistController, ChecklistItem

### Community 68 - "Community 68"
Cohesion: 0.18
Nodes (3): WorkspaceInvitationIssue, WorkspaceInvitation, WorkspaceInvitationPolicy

### Community 69 - "Community 69"
Cohesion: 0.15
Nodes (4): MarkAllNotificationsRead, MarkNotificationRead, NotificationController, NotificationIndexRequest

### Community 70 - "Community 70"
Cohesion: 0.19
Nodes (3): ProjectDetailQuery, WorkspaceManagementQuery, Illuminate\Database\Eloquent\Collection

### Community 71 - "Community 71"
Cohesion: 0.12
Nodes (15): aliases, components, composables, lib, ui, utils, iconLibrary, $schema (+7 more)

### Community 72 - "Community 72"
Cohesion: 0.15
Nodes (4): ReorderChecklistItems, ReorderChecklists, ReorderChecklistItemsRequest, ReorderChecklistsRequest

### Community 73 - "Community 73"
Cohesion: 0.17
Nodes (7): CleanupActivities, DatabaseHealthCommand, ProcessRecurringTasks, RunBackup, Illuminate\Console\Attributes\Description, Illuminate\Console\Attributes\Signature, Illuminate\Console\Command

### Community 74 - "Community 74"
Cohesion: 0.20
Nodes (4): ReminderNotification, WorkspaceInvitationNotification, Illuminate\Notifications\Messages\MailMessage, Illuminate\Notifications\Notification

### Community 75 - "Community 75"
Cohesion: 0.13
Nodes (15): scripts, lint, lint:check, post-autoload-dump, post-update-cmd, pre-package-uninstall, types:check, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump (+7 more)

### Community 76 - "Community 76"
Cohesion: 0.14
Nodes (13): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+5 more)

### Community 77 - "Community 77"
Cohesion: 0.14
Nodes (7): props, { t }, props, props, props, props, props

### Community 78 - "Community 78"
Cohesion: 0.14
Nodes (9): emits, forwarded, props, delegatedProps, emits, forwarded, props, props (+1 more)

### Community 79 - "Community 79"
Cohesion: 0.23
Nodes (6): FailReminderDelivery, DeliverReminder, Illuminate\Contracts\Queue\ShouldBeUnique, Illuminate\Contracts\Queue\ShouldQueue, Illuminate\Foundation\Queue\Queueable, Throwable

### Community 80 - "Community 80"
Cohesion: 0.15
Nodes (13): lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, optionalDependencies, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu (+5 more)

### Community 81 - "Community 81"
Cohesion: 0.15
Nodes (13): scripts, build, build:android, build:ios, build:ssr, dev, format, format:check (+5 more)

### Community 82 - "Community 82"
Cohesion: 0.17
Nodes (9): CommandItem, commands, filteredCommands, groupedCommands, inputRef, query, { t }, ui (+1 more)

### Community 83 - "Community 83"
Cohesion: 0.19
Nodes (13): busyKey, deleteRequest, emit, hasLabel(), hasTag(), labelRequest, props, { t } (+5 more)

### Community 84 - "Community 84"
Cohesion: 0.17
Nodes (12): require-dev, fakerphp/faker, larastan/larastan, laravel/boost, laravel/pail, laravel/pao, laravel/pint, laravel/sail (+4 more)

### Community 85 - "Community 85"
Cohesion: 0.20
Nodes (4): EnsureWorkspaceTaskDefinitions, ProjectFactory, static, WorkspaceFactory

### Community 87 - "Community 87"
Cohesion: 0.31
Nodes (3): TransferWorkspaceOwnership, WorkspaceOwnershipController, TransferWorkspaceOwnershipRequest

### Community 88 - "Community 88"
Cohesion: 0.20
Nodes (10): require, inertiajs/inertia-laravel, laravel/chisel, laravel/fortify, laravel/framework, laravel/sanctum, laravel/tinker, laravel/wayfinder (+2 more)

### Community 93 - "Community 93"
Cohesion: 0.20
Nodes (9): command, enabled, type, mcp, laravel-boost, $schema, artisan, boost:mcp (+1 more)

### Community 94 - "Community 94"
Cohesion: 0.20
Nodes (8): delegatedProps, emits, forwarded, props, SheetContentProps, { t }, delegatedProps, props

### Community 95 - "Community 95"
Cohesion: 0.28
Nodes (3): AcceptWorkspaceInvitation, WorkspaceInvitationAcceptanceController, AcceptWorkspaceInvitationRequest

### Community 96 - "Community 96"
Cohesion: 0.10
Nodes (15): Props, Props, { t }, emit, handleDelete(), isDeleting, props, { t } (+7 more)

### Community 97 - "Community 97"
Cohesion: 0.33
Nodes (3): HandleInertiaRequests, Inertia\Middleware, validateDatabase()

### Community 99 - "Community 99"
Cohesion: 0.22
Nodes (9): ci:check, dev, Composer\\Config::disableProcessTimeout, npm run format:check, npm run lint:check, npm run test:frontend, npm run types:check, @php artisan dev (+1 more)

### Community 100 - "Community 100"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 101 - "Community 101"
Cohesion: 0.22
Nodes (7): delegatedProps, emits, forwarded, props, { t }, delegatedProps, props

### Community 103 - "Community 103"
Cohesion: 0.25
Nodes (8): post-root-package-install, setup, composer install, npm install, npm run build, @php artisan key:generate, @php artisan migrate --force, @php -r \"file_exists('.env') || copy('.env.example', '.env');\

### Community 105 - "Community 105"
Cohesion: 0.25
Nodes (4): props, delegatedProps, props, props

### Community 106 - "Community 106"
Cohesion: 0.29
Nodes (5): handleKeyDown(), registeredShortcuts, Shortcut, ShortcutHandler, useKeyboardShortcuts()

### Community 107 - "Community 107"
Cohesion: 0.38
Nodes (3): UpdateUserPreferences, UserPreferenceController, UpdateUserPreferenceRequest

### Community 108 - "Community 108"
Cohesion: 0.09
Nodes (19): emit, form, props, { statuses, priorities, defaultStatus, defaultPriority }, submit(), { t }, toast, emit (+11 more)

### Community 109 - "Community 109"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 111 - "Community 111"
Cohesion: 0.57
Nodes (6): assertExistingRelationsAreValid(), createParentWorkspaceTriggers(), createTaskDefinitionTriggers(), down(), todoParentForeignKeyExists(), up()

### Community 112 - "Community 112"
Cohesion: 0.29
Nodes (4): props, width, props, SkeletonProps

### Community 113 - "Community 113"
Cohesion: 0.12
Nodes (4): SyncTodoLabel, DetachLabelRequest, Label, LabelSeeder

### Community 114 - "Community 114"
Cohesion: 0.33
Nodes (4): handleKeyDown(), KeyHandler, shortcuts, useKeyboard()

### Community 117 - "Community 117"
Cohesion: 0.33
Nodes (5): imports, #nativephp, private, $schema, type

### Community 119 - "Community 119"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 120 - "Community 120"
Cohesion: 0.40
Nodes (5): extra, laravel, post-create-project, dont-discover, installer

### Community 121 - "Community 121"
Cohesion: 0.40
Nodes (5): test, @lint:check, @php artisan config:clear --ansi, @php artisan test, @types:check

### Community 122 - "Community 122"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 123 - "Community 123"
Cohesion: 0.40
Nodes (4): Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Foundation\Http\Middleware\ValidateCsrfToken, Laravel\Sanctum\Http\Middleware\AuthenticateSession, Laravel\Sanctum\Sanctum

### Community 125 - "Community 125"
Cohesion: 0.40
Nodes (4): createDialog, overviewPanel, taskIndex, taskList

### Community 126 - "Community 126"
Cohesion: 0.40
Nodes (4): createDialog, overviewPanel, taskIndex, taskList

### Community 129 - "Community 129"
Cohesion: 0.50
Nodes (4): post-create-project-cmd, @php artisan key:generate --ansi, @php artisan migrate --graceful --ansi, @php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\

### Community 132 - "Community 132"
Cohesion: 0.50
Nodes (3): delegatedProps, forwardedProps, props

### Community 216 - "Community 216"
Cohesion: 0.15
Nodes (11): Illuminate\Auth\AuthenticationException, Illuminate\Database\Eloquent\ModelNotFoundException, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware, Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets, Laravel\Sanctum\Http\Middleware\CheckAbilities, Symfony\Component\HttpKernel\Exception\HttpExceptionInterface (+3 more)

### Community 217 - "Community 217"
Cohesion: 0.18
Nodes (5): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\Hash, Illuminate\Support\Facades\URL, Laravel\Fortify\Features

### Community 219 - "Community 219"
Cohesion: 0.32
Nodes (4): LegacyBackedEnumOrString, BackedEnum, Illuminate\Contracts\Database\Eloquent\CastsAttributes, InvalidArgumentException

### Community 220 - "Community 220"
Cohesion: 0.29
Nodes (3): AppServiceProvider, AuthServiceProvider, Illuminate\Foundation\Support\Providers\AuthServiceProvider

## Knowledge Gaps
- **911 isolated node(s):** `Props`, `Props`, `ProfileLabels`, `DeleteTarget`, `TodoStatus` (+906 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **32 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Workspace` connect `Community 5` to `Community 3`, `Community 6`, `Community 8`, `Community 9`, `Community 10`, `Community 11`, `Community 12`, `Community 15`, `Community 19`, `Community 23`, `Community 25`, `Community 29`, `Community 30`, `Community 33`, `Community 38`, `Community 40`, `Community 44`, `Community 45`, `Community 46`, `Community 52`, `Community 54`, `Community 55`, `Community 56`, `Community 59`, `Community 60`, `Community 62`, `Community 70`, `Community 85`, `Community 87`, `Community 104`, `Community 110`, `Community 113`?**
  _High betweenness centrality (0.069) - this node is a cross-community bridge._
- **Why does `User` connect `Community 15` to `Community 3`, `Community 5`, `Community 6`, `Community 8`, `Community 9`, `Community 10`, `Community 11`, `Community 12`, `Community 19`, `Community 23`, `Community 24`, `Community 29`, `Community 30`, `Community 46`, `Community 52`, `Community 54`, `Community 55`, `Community 65`, `Community 68`, `Community 69`, `Community 70`, `Community 87`, `Community 217`, `Community 218`, `Community 102`, `Community 110`, `Community 111`, `Community 113`?**
  _High betweenness centrality (0.061) - this node is a cross-community bridge._
- **Why does `Todo` connect `Community 3` to `Community 5`, `Community 6`, `Community 8`, `Community 9`, `Community 10`, `Community 11`, `Community 12`, `Community 15`, `Community 19`, `Community 29`, `Community 30`, `Community 33`, `Community 40`, `Community 51`, `Community 52`, `Community 54`, `Community 55`, `Community 58`, `Community 59`, `Community 60`, `Community 62`, `Community 66`, `Community 67`, `Community 70`, `Community 72`, `Community 73`, `Community 86`, `Community 97`, `Community 102`, `Community 113`?**
  _High betweenness centrality (0.050) - this node is a cross-community bridge._
- **What connects `Props`, `Props`, `ProfileLabels` to the rest of the system?**
  _911 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Community 0` be split into smaller, more focused modules?**
  _Cohesion score 0.04104938271604938 - nodes in this community are weakly interconnected._
- **Should `Community 1` be split into smaller, more focused modules?**
  _Cohesion score 0.05143191116306254 - nodes in this community are weakly interconnected._
- **Should `Community 2` be split into smaller, more focused modules?**
  _Cohesion score 0.03893557422969188 - nodes in this community are weakly interconnected._