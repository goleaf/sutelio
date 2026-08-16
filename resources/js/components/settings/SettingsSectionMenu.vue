<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Check, ChevronsUpDown } from '@lucide/vue';
import type { LucideIcon } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

type SettingsSectionItem = {
    active: boolean;
    href: string;
    icon: LucideIcon;
    label: string;
};

const props = defineProps<{
    currentLabel: string;
    items: SettingsSectionItem[];
    openLabel: string;
}>();

const activeItem = computed(
    () => props.items.find((item) => item.active) ?? props.items[0],
);
</script>

<template>
    <div class="lg:hidden">
        <DropdownMenu>
            <DropdownMenuTrigger :as-child="true">
                <Button
                    type="button"
                    variant="outline"
                    class="h-auto min-h-11 w-full justify-between rounded-xl border-border/80 bg-muted/55 px-3 py-2 text-left whitespace-normal shadow-none transition-colors hover:bg-muted focus-visible:ring-orange-500 motion-reduce:transition-none"
                >
                    <span class="sr-only">{{ openLabel }}</span>
                    <span class="flex min-w-0 items-center gap-3">
                        <span
                            class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-card text-orange-700 shadow-sm ring-1 ring-border/70 dark:text-orange-300"
                        >
                            <component
                                v-if="activeItem"
                                :is="activeItem.icon"
                                class="size-4"
                                aria-hidden="true"
                            />
                        </span>
                        <span class="min-w-0">
                            <span
                                class="block text-[10px] leading-none font-semibold tracking-[0.14em] text-muted-foreground uppercase"
                            >
                                {{ currentLabel }}
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
                    v-for="item in items"
                    :key="item.href"
                    :as-child="true"
                >
                    <Link
                        :href="item.href"
                        :aria-current="item.active ? 'page' : undefined"
                        class="flex h-auto min-h-11 w-full items-center gap-3 rounded-lg px-3 py-2 whitespace-normal transition-colors motion-reduce:transition-none"
                    >
                        <component
                            :is="item.icon"
                            class="size-4"
                            aria-hidden="true"
                        />
                        <span class="min-w-0 flex-1 break-words">{{
                            item.label
                        }}</span>
                        <Check
                            v-if="item.active"
                            class="size-4 text-orange-700 dark:text-orange-300"
                            aria-hidden="true"
                        />
                    </Link>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    </div>
</template>
