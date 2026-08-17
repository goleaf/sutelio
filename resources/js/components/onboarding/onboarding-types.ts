export const orderedOnboardingSteps = [
    'welcome',
    'preferences',
    'workspace',
    'project',
    'task',
    'product_map',
    'safety',
    'results',
] as const;

export type OnboardingStep = (typeof orderedOnboardingSteps)[number];
export type OnboardingMode = 'select' | 'create';
export type OnboardingPluralForm = 'one' | 'few' | 'many' | 'other';

export type OnboardingProgress = {
    step: OnboardingStep;
    position: number;
    total: number;
    percent: number;
    is_replay: boolean;
};

export type OnboardingPreferences = {
    language: 'en' | 'lt' | 'ru';
    timezone: string;
    date_format: 'Y-m-d' | 'd/m/Y' | 'm/d/Y' | 'd.m.Y';
    time_format: 'H:i' | 'h:i A';
    default_view: 'list' | 'board' | 'calendar';
    start_page: 'dashboard' | 'tasks' | 'projects' | 'calendar';
    week_start: 'sunday' | 'monday';
};

export type OnboardingState = {
    workspace_id?: string;
    workspace_name?: string;
    project_id?: string;
    project_name?: string;
    task_id?: string;
    task_title?: string;
};

export type OnboardingWorkspace = {
    id: string;
    name: string;
    role: string | null;
};

export type OnboardingProject = {
    id: string;
    name: string;
    color: string;
    icon: string;
};

export type OnboardingTask = {
    id: string;
    title: string;
    project_id: string | null;
    due_date: string | null;
};

export type OnboardingMember = {
    id: string;
    name: string;
    role: string | null;
};

export type OnboardingDefinition = {
    id: string;
    key: string;
    name: string;
    translation_key: string | null;
    color: string;
    is_default: boolean;
};

export type OnboardingOptions = {
    workspaces: OnboardingWorkspace[];
    projects: OnboardingProject[];
    tasks: OnboardingTask[];
    members: OnboardingMember[];
    statuses: OnboardingDefinition[];
    priorities: OnboardingDefinition[];
};

export type OnboardingRecovery =
    | 'workspace_unavailable'
    | 'workspace_required'
    | 'project_unavailable'
    | 'project_required'
    | 'task_unavailable'
    | 'task_required'
    | null;

export type OnboardingPreferencesPayload = OnboardingPreferences;

export type OnboardingWorkspacePayload = {
    mode: OnboardingMode;
    request_key: string;
    workspace_id?: string;
    name?: string;
    description?: string | null;
};

export type OnboardingProjectPayload = {
    mode: OnboardingMode;
    request_key: string;
    project_id?: string;
    name?: string;
    description?: string | null;
    color?: string;
    icon?: string;
};

export type OnboardingTaskPayload = {
    mode: OnboardingMode;
    request_key: string;
    task_id?: string;
    title?: string;
    description?: string | null;
    status_id?: string;
    priority_id?: string;
    assigned_to?: string | null;
    due_date?: string | null;
};

export type OnboardingStepCopy = {
    title: string;
    description: string;
};

export type OnboardingCopy = {
    meta: {
        eyebrow: string;
        title: string;
        description: string;
        replay_badge: string;
    };
    steps: Record<OnboardingStep, OnboardingStepCopy>;
    actions: {
        back: string;
        cancel: string;
        continue: string;
        finish: string;
        skip: string;
        skip_confirm: string;
        skip_title: string;
        skip_description: string;
        exit_replay: string;
        retry: string;
    };
    status: {
        step_count: string;
        percent_complete: string;
        idle: string;
        saving: string;
        saved: string;
        error: string;
        resumed: string;
    };
    errors: Record<string, string> & {
        validation_title: string;
        validation_description: string;
    };
    welcome: {
        intro: string;
        capture_title: string;
        capture_description: string;
        plan_title: string;
        plan_description: string;
        collaborate_title: string;
        collaborate_description: string;
        privacy: string;
    };
    preferences: {
        description: string;
        language: string;
        timezone: string;
        date_format: string;
        time_format: string;
        default_view: string;
        start_page: string;
        week_start: string;
        preview_title: string;
        preview_description: string;
        views: Record<OnboardingPreferences['default_view'], string>;
        start_pages: Record<OnboardingPreferences['start_page'], string>;
        week_starts: Record<OnboardingPreferences['week_start'], string>;
    };
    workspace: {
        description: string;
        choose_existing: string;
        create_new: string;
        existing_label: string;
        name: string;
        name_placeholder: string;
        details: string;
        details_placeholder: string;
        empty_title: string;
        empty_description: string;
        preview_title: string;
        role: string;
    };
    project: {
        description: string;
        choose_existing: string;
        create_new: string;
        existing_label: string;
        name: string;
        name_placeholder: string;
        details: string;
        details_placeholder: string;
        color: string;
        icon: string;
        empty_title: string;
        empty_description: string;
        preview_title: string;
    };
    task: {
        description: string;
        choose_existing: string;
        create_new: string;
        existing_label: string;
        title: string;
        title_placeholder: string;
        details: string;
        details_placeholder: string;
        status: string;
        priority: string;
        assignee: string;
        unassigned: string;
        due_date: string;
        empty_title: string;
        empty_description: string;
        preview_title: string;
    };
    product_map: Record<
        | 'dashboard_title'
        | 'dashboard_description'
        | 'tasks_title'
        | 'tasks_description'
        | 'projects_title'
        | 'projects_description'
        | 'calendar_title'
        | 'calendar_description'
        | 'activity_title'
        | 'activity_description'
        | 'notifications_title'
        | 'notifications_description',
        string
    >;
    safety: {
        team_title: string;
        team_description: string;
        security_title: string;
        security_description: string;
        backup_title: string;
        backup_description: string;
        manager_note: string;
        member_note: string;
    };
    results: {
        description: string;
        ready_title: string;
        preferences: string;
        workspace: string;
        project: string;
        task: string;
        next_title: string;
        next_description: string;
        entity_count_one: string;
        entity_count_few: string;
        entity_count_many: string;
        entity_count_other: string;
    };
};

export function onboardingPercent(step: OnboardingStep): number {
    const position = orderedOnboardingSteps.indexOf(step) + 1;

    return Math.round((position / orderedOnboardingSteps.length) * 100);
}

export function onboardingPluralForm(
    count: number,
    locale: string,
): OnboardingPluralForm {
    const selected = new Intl.PluralRules(locale).select(count);

    return selected === 'one' || selected === 'few' || selected === 'many'
        ? selected
        : 'other';
}

export function mergeOnboardingDraft(
    source: Readonly<OnboardingState>,
    patch: Readonly<Partial<OnboardingState>>,
): OnboardingState {
    const merged = { ...source, ...patch };

    if (
        Object.hasOwn(patch, 'workspace_id') &&
        patch.workspace_id !== source.workspace_id
    ) {
        delete merged.project_id;
        delete merged.project_name;
        delete merged.task_id;
        delete merged.task_title;
    } else if (
        Object.hasOwn(patch, 'project_id') &&
        patch.project_id !== source.project_id
    ) {
        delete merged.task_id;
        delete merged.task_title;
    }

    return merged;
}
