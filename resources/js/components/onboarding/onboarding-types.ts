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
export type OnboardingPluralForm =
    'zero' | 'one' | 'two' | 'few' | 'many' | 'other';

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

export function onboardingPercent(step: OnboardingStep): number {
    const position = orderedOnboardingSteps.indexOf(step) + 1;

    return Math.round((position / orderedOnboardingSteps.length) * 100);
}

export function onboardingPluralForm(
    count: number,
    locale: string,
): OnboardingPluralForm {
    return new Intl.PluralRules(locale).select(count);
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
