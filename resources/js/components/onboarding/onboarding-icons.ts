import {
    Building2,
    FolderKanban,
    ListChecks,
    MapPinned,
    PartyPopper,
    Settings2,
    ShieldCheck,
    Sparkles,
} from '@lucide/vue';
import type { Component } from 'vue';
import type { OnboardingStep } from '@/components/onboarding/onboarding-types';

export const onboardingStepIcons = {
    welcome: Sparkles,
    preferences: Settings2,
    workspace: Building2,
    project: FolderKanban,
    task: ListChecks,
    product_map: MapPinned,
    safety: ShieldCheck,
    results: PartyPopper,
} satisfies Record<OnboardingStep, Component>;

export function resolveOnboardingStepIcon(step: OnboardingStep): Component {
    return onboardingStepIcons[step];
}
