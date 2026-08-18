<script setup lang="ts">
import { FolderKanban } from '@lucide/vue';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import type {
    OnboardingCopy,
    OnboardingMode,
    OnboardingProject,
} from '@/components/onboarding/onboarding-types';
import LeadingIconHeading from '@/components/shared/LeadingIconHeading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const props = defineProps<{
    copy: OnboardingCopy['project'];
    projects: OnboardingProject[];
    mode: OnboardingMode;
    selectedId: string;
    name: string;
    description: string;
    color: string;
    icon: string;
    errors: Record<string, string>;
    processing: boolean;
}>();

const emit = defineEmits<{
    'update:mode': [mode: OnboardingMode];
    'update:selectedId': [id: string];
    'update:name': [name: string];
    'update:description': [description: string];
    'update:color': [color: string];
    'update:icon': [icon: string];
}>();

const selected = computed(() =>
    props.projects.find((item) => item.id === props.selectedId),
);
const hasExistingOptions = computed(() => props.projects.length > 0);
const selectedId = computed({
    get: () => props.selectedId,
    set: (value: string) => emit('update:selectedId', value),
});
const name = computed({
    get: () => props.name,
    set: (value: string | number) => emit('update:name', String(value)),
});
const description = computed({
    get: () => props.description,
    set: (value: string) => emit('update:description', value),
});
const color = computed({
    get: () => props.color,
    set: (value: string | number) => emit('update:color', String(value)),
});
const icon = computed({
    get: () => props.icon,
    set: (value: string | number) => emit('update:icon', String(value)),
});
</script>

<template>
    <div class="space-y-5">
        <p class="text-base leading-7 text-muted-foreground">
            {{
                hasExistingOptions ? copy.description : copy.create_description
            }}
        </p>
        <div
            v-if="hasExistingOptions"
            class="grid grid-cols-2 gap-2 rounded-2xl bg-muted/55 p-1.5"
            role="group"
        >
            <Button
                type="button"
                variant="ghost"
                class="h-auto min-h-12 py-2 whitespace-normal pointer-coarse:min-h-13"
                :aria-pressed="mode === 'select'"
                :disabled="processing"
                :class="mode === 'select' ? 'bg-background shadow-sm' : ''"
                @click="emit('update:mode', 'select')"
                >{{ copy.choose_existing }}</Button
            >
            <Button
                type="button"
                variant="ghost"
                class="h-auto min-h-12 py-2 whitespace-normal pointer-coarse:min-h-13"
                :aria-pressed="mode === 'create'"
                :disabled="processing"
                :class="mode === 'create' ? 'bg-background shadow-sm' : ''"
                @click="emit('update:mode', 'create')"
                >{{ copy.create_new }}</Button
            >
        </div>

        <div
            v-if="mode === 'select' && hasExistingOptions"
            class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(14rem,0.45fr)]"
        >
            <div class="space-y-2">
                <Label for="project_id">{{ copy.existing_label }}</Label>
                <Select v-model="selectedId" :disabled="processing">
                    <SelectTrigger
                        id="project_id"
                        :aria-invalid="Boolean(errors.project_id)"
                        ><SelectValue
                    /></SelectTrigger>
                    <SelectContent
                        ><SelectItem
                            v-for="project in projects"
                            :key="project.id"
                            :value="project.id"
                            >{{ project.name }}</SelectItem
                        ></SelectContent
                    >
                </Select>
                <InputError :message="errors.project_id" />
            </div>
            <aside
                class="rounded-2xl border border-orange-500/15 bg-orange-500/[0.055] p-5"
            >
                <LeadingIconHeading
                    tile
                    tile-tone="brand"
                    content-class="gap-2"
                >
                    <template #icon>
                        <FolderKanban />
                    </template>

                    <h2 class="font-semibold">{{ copy.preview_title }}</h2>
                    <p
                        class="flex items-center gap-2 text-base font-medium break-words"
                    >
                        <span
                            class="size-2 shrink-0 rounded-full"
                            :style="{ backgroundColor: selected?.color }"
                            aria-hidden="true"
                        />
                        {{ selected?.name }}
                    </p>
                </LeadingIconHeading>
            </aside>
        </div>

        <div
            v-else-if="!hasExistingOptions || mode === 'create'"
            class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(14rem,0.45fr)]"
        >
            <div class="space-y-4">
                <div class="space-y-2">
                    <Label for="name">{{ copy.name }}</Label>
                    <Input
                        id="name"
                        v-model="name"
                        :placeholder="copy.name_placeholder"
                        :disabled="processing"
                        :aria-invalid="Boolean(errors.name)"
                    />
                    <InputError :message="errors.name" />
                </div>
                <div class="space-y-2">
                    <Label for="description">{{ copy.details }}</Label>
                    <textarea
                        id="description"
                        v-model="description"
                        rows="4"
                        :placeholder="copy.details_placeholder"
                        :disabled="processing"
                        :aria-invalid="Boolean(errors.description)"
                        class="min-h-28 w-full resize-y rounded-xl border border-input bg-linear-to-br from-background via-orange-50/45 to-orange-100/65 px-3.5 py-3 text-base transition-[color,box-shadow] outline-none placeholder:text-muted-foreground hover:border-orange-300/70 focus-visible:border-orange-500 focus-visible:ring-3 focus-visible:ring-orange-500/20 disabled:cursor-not-allowed disabled:opacity-50 motion-reduce:transition-none"
                    />
                    <InputError :message="errors.description" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="color">{{ copy.color }}</Label>
                        <Input
                            id="color"
                            v-model="color"
                            type="color"
                            :disabled="processing"
                            :aria-invalid="Boolean(errors.color)"
                            class="p-1.5"
                        />
                        <InputError :message="errors.color" />
                    </div>
                    <div class="space-y-2">
                        <Label for="icon">{{ copy.icon }}</Label>
                        <Input
                            id="icon"
                            v-model="icon"
                            :disabled="processing"
                            :aria-invalid="Boolean(errors.icon)"
                        />
                        <InputError :message="errors.icon" />
                    </div>
                </div>
            </div>
            <aside
                class="rounded-2xl border border-orange-500/15 bg-orange-500/[0.055] p-5"
            >
                <LeadingIconHeading
                    tile
                    tile-tone="brand"
                    content-class="gap-2"
                >
                    <template #icon>
                        <FolderKanban />
                    </template>

                    <h2 class="font-semibold">{{ copy.preview_title }}</h2>
                    <p
                        class="flex items-center gap-2 text-base font-medium break-words"
                    >
                        <span
                            class="size-2 shrink-0 rounded-full"
                            :style="{ backgroundColor: color }"
                            aria-hidden="true"
                        />
                        {{ name || copy.name_placeholder }}
                    </p>
                    <p
                        class="text-[0.9375rem] leading-6 break-words text-muted-foreground"
                    >
                        {{ description || copy.details_placeholder }}
                    </p>
                </LeadingIconHeading>
            </aside>
        </div>

        <div
            v-else
            class="rounded-2xl border border-dashed border-border p-6 text-center"
        >
            <h2 class="font-semibold">{{ copy.empty_title }}</h2>
            <p class="mt-2 text-base leading-7 text-muted-foreground">
                {{ copy.empty_description }}
            </p>
        </div>
    </div>
</template>
