<script setup lang="ts">
import { Building2 } from '@lucide/vue';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import type {
    OnboardingCopy,
    OnboardingMode,
    OnboardingWorkspace,
} from '@/components/onboarding/onboarding-types';
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
    copy: OnboardingCopy['workspace'];
    workspaces: OnboardingWorkspace[];
    mode: OnboardingMode;
    selectedId: string;
    name: string;
    description: string;
    errors: Record<string, string>;
    processing: boolean;
}>();

const emit = defineEmits<{
    'update:mode': [mode: OnboardingMode];
    'update:selectedId': [id: string];
    'update:name': [name: string];
    'update:description': [description: string];
}>();

const selected = computed(() =>
    props.workspaces.find((item) => item.id === props.selectedId),
);
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
</script>

<template>
    <div class="space-y-5">
        <p class="text-sm leading-6 text-muted-foreground">
            {{ copy.description }}
        </p>
        <div
            class="grid grid-cols-2 gap-2 rounded-2xl bg-muted/55 p-1.5"
            role="group"
        >
            <Button
                type="button"
                variant="ghost"
                class="min-h-11"
                :aria-pressed="mode === 'select'"
                :disabled="processing || workspaces.length === 0"
                :class="mode === 'select' ? 'bg-background shadow-sm' : ''"
                @click="emit('update:mode', 'select')"
            >
                {{ copy.choose_existing }}
            </Button>
            <Button
                type="button"
                variant="ghost"
                class="min-h-11"
                :aria-pressed="mode === 'create'"
                :disabled="processing"
                :class="mode === 'create' ? 'bg-background shadow-sm' : ''"
                @click="emit('update:mode', 'create')"
            >
                {{ copy.create_new }}
            </Button>
        </div>

        <div
            v-if="mode === 'select' && workspaces.length"
            class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(14rem,0.45fr)]"
        >
            <div class="space-y-2">
                <Label for="workspace_id">{{ copy.existing_label }}</Label>
                <Select v-model="selectedId" :disabled="processing">
                    <SelectTrigger
                        id="workspace_id"
                        :aria-invalid="Boolean(errors.workspace_id)"
                        ><SelectValue
                    /></SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="workspace in workspaces"
                            :key="workspace.id"
                            :value="workspace.id"
                            >{{ workspace.name }}</SelectItem
                        >
                    </SelectContent>
                </Select>
                <InputError :message="errors.workspace_id" />
            </div>
            <aside
                class="rounded-2xl border border-orange-500/15 bg-orange-500/[0.055] p-5"
            >
                <Building2
                    class="size-5 text-orange-600 dark:text-orange-300"
                    aria-hidden="true"
                />
                <h2 class="mt-4 font-semibold">{{ copy.preview_title }}</h2>
                <p class="mt-2 text-sm font-medium break-words">
                    {{ selected?.name }}
                </p>
                <p
                    v-if="selected?.role"
                    class="mt-1 text-xs text-muted-foreground"
                >
                    {{ copy.role.replace(':role', selected.role) }}
                </p>
            </aside>
        </div>

        <div
            v-else-if="mode === 'create'"
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
                        autocomplete="organization"
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
                        class="min-h-28 w-full resize-y rounded-xl border border-input bg-background px-3.5 py-3 text-sm transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-orange-500 focus-visible:ring-3 focus-visible:ring-orange-500/20 disabled:cursor-not-allowed disabled:opacity-50 motion-reduce:transition-none"
                    />
                    <InputError :message="errors.description" />
                </div>
            </div>
            <aside
                class="rounded-2xl border border-orange-500/15 bg-orange-500/[0.055] p-5"
            >
                <Building2
                    class="size-5 text-orange-600 dark:text-orange-300"
                    aria-hidden="true"
                />
                <h2 class="mt-4 font-semibold">{{ copy.preview_title }}</h2>
                <p class="mt-2 text-sm font-medium break-words">
                    {{ name || copy.name_placeholder }}
                </p>
                <p
                    class="mt-2 text-xs leading-5 break-words text-muted-foreground"
                >
                    {{ description || copy.details_placeholder }}
                </p>
            </aside>
        </div>

        <div
            v-else
            class="rounded-2xl border border-dashed border-border p-6 text-center"
        >
            <h2 class="font-semibold">{{ copy.empty_title }}</h2>
            <p class="mt-2 text-sm leading-6 text-muted-foreground">
                {{ copy.empty_description }}
            </p>
        </div>
    </div>
</template>
