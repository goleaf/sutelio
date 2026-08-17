<script setup lang="ts">
import type { LinkPrefetchOption, UrlMethodPair } from '@inertiajs/core';
import { Link } from '@inertiajs/vue3';
import { Check, ChevronsUpDown } from '@lucide/vue';
import type { LucideIcon } from '@lucide/vue';
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';
import IconTile from '@/components/shared/IconTile.vue';
import WorkspaceSegmentedControl from '@/components/shared/WorkspaceSegmentedControl.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { cn } from '@/lib/utils';

interface ResponsiveSectionNavigationItem {
    active: boolean;
    href: string | UrlMethodPair;
    icon: LucideIcon;
    key: string;
    label: string;
    tone?: 'default' | 'danger';
}

const props = withDefaults(
    defineProps<{
        class?: HTMLAttributes['class'];
        currentLabel: string;
        desktopMode?: 'list' | 'segmented';
        items: ResponsiveSectionNavigationItem[];
        label: string;
        openLabel: string;
        prefetch?: boolean | LinkPrefetchOption | LinkPrefetchOption[];
    }>(),
    {
        desktopMode: 'list',
        prefetch: false,
    },
);

const activeItem = computed(
    () => props.items.find((item) => item.active) ?? props.items[0],
);

const linkClasses = (item: ResponsiveSectionNavigationItem): string =>
    cn(
        'flex min-h-11 shrink-0 items-center gap-2 rounded-lg px-3 py-2 text-sm whitespace-nowrap transition-[color,background-color,border-color,box-shadow,transform] duration-[var(--motion-feedback)] ease-[var(--ease-emphasized)] focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:outline-none motion-reduce:transition-none',
        item.active
            ? item.tone === 'danger'
                ? 'bg-card font-medium text-destructive shadow-sm'
                : 'bg-card font-medium text-orange-800 shadow-sm'
            : item.tone === 'danger'
              ? 'text-destructive/80 hover:bg-card/70 hover:text-destructive'
              : 'text-muted-foreground hover:bg-card/70 hover:text-foreground',
    );
</script>

<template>
    <nav
        data-slot="responsive-section-navigation"
        :aria-label="props.label"
        :class="
            cn(
                props.desktopMode === 'list'
                    ? 'lg:w-52 lg:shrink-0 lg:self-start'
                    : '',
                props.class,
            )
        "
    >
        <div class="lg:hidden">
            <DropdownMenu>
                <DropdownMenuTrigger :as-child="true">
                    <Button
                        type="button"
                        variant="outline"
                        class="h-auto min-h-11 w-full justify-between rounded-xl border-border/80 bg-muted/55 px-3 py-2 text-left whitespace-normal shadow-none transition-colors hover:bg-muted focus-visible:ring-orange-500 motion-reduce:transition-none"
                    >
                        <span class="sr-only">{{ props.openLabel }}</span>
                        <span class="flex min-w-0 items-center gap-3">
                            <IconTile
                                :tone="
                                    activeItem?.tone === 'danger'
                                        ? 'destructive'
                                        : 'brand'
                                "
                                size="sm"
                            >
                                <component
                                    v-if="activeItem"
                                    :is="activeItem.icon"
                                />
                            </IconTile>
                            <span class="min-w-0">
                                <span
                                    class="block text-[10px] leading-none font-semibold tracking-[0.14em] text-muted-foreground uppercase"
                                >
                                    {{ props.currentLabel }}
                                </span>
                                <span
                                    class="mt-1 block text-sm font-medium break-words whitespace-normal"
                                >
                                    {{ activeItem?.label }}
                                </span>
                            </span>
                        </span>
                        <ChevronsUpDown
                            class="size-4 shrink-0 text-muted-foreground"
                            aria-hidden="true"
                        />
                    </Button>
                </DropdownMenuTrigger>

                <DropdownMenuContent
                    align="start"
                    :side-offset="6"
                    class="w-(--reka-dropdown-menu-trigger-width) rounded-xl p-1.5"
                >
                    <DropdownMenuItem
                        v-for="item in props.items"
                        :key="item.key"
                        :as-child="true"
                    >
                        <Link
                            :href="item.href"
                            :prefetch="props.prefetch"
                            :aria-current="item.active ? 'page' : undefined"
                            class="flex h-auto min-h-11 w-full items-center gap-3 rounded-lg px-3 py-2 whitespace-normal transition-colors motion-reduce:transition-none"
                            :class="
                                item.tone === 'danger'
                                    ? 'text-destructive focus:text-destructive'
                                    : ''
                            "
                        >
                            <component
                                :is="item.icon"
                                class="size-4 shrink-0"
                                aria-hidden="true"
                            />
                            <span class="min-w-0 flex-1 break-words">
                                {{ item.label }}
                            </span>
                            <Check
                                v-if="item.active"
                                class="size-4 shrink-0 text-orange-700"
                                aria-hidden="true"
                            />
                        </Link>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>

        <div
            v-if="props.desktopMode === 'list'"
            class="hidden flex-col gap-1 rounded-xl bg-muted/55 p-1 lg:flex"
        >
            <Link
                v-for="item in props.items"
                :key="item.key"
                :href="item.href"
                :prefetch="props.prefetch"
                :aria-current="item.active ? 'page' : undefined"
                :class="linkClasses(item)"
            >
                <component :is="item.icon" class="size-4" aria-hidden="true" />
                {{ item.label }}
            </Link>
        </div>

        <WorkspaceSegmentedControl
            v-else
            :label="props.label"
            role="group"
            class="hidden lg:flex"
        >
            <Link
                v-for="item in props.items"
                :key="item.key"
                :href="item.href"
                :prefetch="props.prefetch"
                :aria-current="item.active ? 'page' : undefined"
                :class="linkClasses(item)"
            >
                <component :is="item.icon" class="size-4" aria-hidden="true" />
                {{ item.label }}
            </Link>
        </WorkspaceSegmentedControl>
    </nav>
</template>
