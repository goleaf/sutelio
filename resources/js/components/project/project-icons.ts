import {
    BookOpen,
    BriefcaseBusiness,
    Code2,
    Folder,
    Globe2,
    Palette,
    Rocket,
    Star,
} from '@lucide/vue';
import type { Component } from 'vue';

export const projectIconOptions = [
    {
        value: 'folder',
        labelKey: 'icon_folder',
        icon: Folder,
    },
    {
        value: 'briefcase',
        labelKey: 'icon_briefcase',
        icon: BriefcaseBusiness,
    },
    {
        value: 'code',
        labelKey: 'icon_code',
        icon: Code2,
    },
    {
        value: 'palette',
        labelKey: 'icon_palette',
        icon: Palette,
    },
    {
        value: 'book',
        labelKey: 'icon_book',
        icon: BookOpen,
    },
    {
        value: 'star',
        labelKey: 'icon_star',
        icon: Star,
    },
    {
        value: 'rocket',
        labelKey: 'icon_rocket',
        icon: Rocket,
    },
    {
        value: 'globe',
        labelKey: 'icon_globe',
        icon: Globe2,
    },
] as const satisfies ReadonlyArray<{
    value: string;
    labelKey: string;
    icon: Component;
}>;

const projectIconComponents = new Map<string, Component>(
    projectIconOptions.map((option) => [option.value, option.icon]),
);

export function resolveProjectIcon(value?: string | null): Component {
    return projectIconComponents.get(value ?? '') ?? Folder;
}
