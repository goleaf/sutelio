<script setup lang="ts">
import { CheckSquare2, X } from '@lucide/vue';
import { taskPluralForm } from '@/components/task/task-focus';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { useUi } from '@/composables/useUi';
import type { PaginatedResponse } from '@/types/api';
import type { Todo } from '@/types/models';

defineProps<{
    activeFilterCount: number;
    allSelected: boolean;
    pagination: PaginatedResponse<Todo>;
    processing: boolean;
    selectedCount: number;
    selectionMode: boolean;
    view: 'board' | 'list';
}>();

const emit = defineEmits<{
    selectPage: [selected: boolean];
    updateSelectionMode: [enabled: boolean];
}>();
const { formatNumber, locale, t } = useUi();

function activeFilterSummary(count: number): string {
    return t(
        `tasks.filters.active_count_${taskPluralForm(count, locale.value)}`,
        { count: formatNumber(count) },
    );
}
</script>

<template>
    <div
        class="mt-5 flex flex-col gap-3 border-b border-border/70 pb-4 sm:flex-row sm:items-center sm:justify-between"
    >
        <div class="min-w-0">
            <p class="text-sm font-semibold tabular-nums" aria-live="polite">
                {{
                    t('tasks.index.result_summary', {
                        from: formatNumber(pagination.meta.from ?? 0),
                        to: formatNumber(pagination.meta.to ?? 0),
                        total: formatNumber(pagination.meta.total),
                    })
                }}
            </p>
            <p
                v-if="activeFilterCount"
                class="mt-0.5 text-xs text-muted-foreground tabular-nums"
            >
                {{ activeFilterSummary(activeFilterCount) }}
            </p>
        </div>

        <div
            v-if="view === 'list'"
            class="flex min-h-11 flex-wrap items-center gap-2"
        >
            <template v-if="selectionMode">
                <label
                    class="flex min-h-11 cursor-pointer items-center gap-2 rounded-lg px-2 text-sm font-medium"
                >
                    <Checkbox
                        :model-value="allSelected"
                        :aria-label="t('tasks.index.select_page')"
                        :disabled="processing"
                        @update:model-value="
                            emit('selectPage', Boolean($event))
                        "
                    />
                    <span>{{ t('tasks.index.select_page') }}</span>
                </label>
                <span
                    v-if="selectedCount"
                    class="text-xs text-muted-foreground tabular-nums"
                >
                    {{
                        t('common.states.selected', {
                            count: formatNumber(selectedCount),
                        })
                    }}
                </span>
                <Button
                    type="button"
                    variant="ghost"
                    class="min-h-11"
                    :disabled="processing"
                    @click="emit('updateSelectionMode', false)"
                >
                    <X class="size-4" aria-hidden="true" />
                    {{ t('tasks.index.exit_selection') }}
                </Button>
            </template>
            <Button
                v-else
                type="button"
                variant="outline"
                class="min-h-11"
                :disabled="processing"
                @click="emit('updateSelectionMode', true)"
            >
                <CheckSquare2 class="size-4" aria-hidden="true" />
                {{ t('tasks.index.enter_selection') }}
            </Button>
        </div>
    </div>
</template>
