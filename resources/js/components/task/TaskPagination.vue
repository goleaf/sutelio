<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { computed } from 'vue';
import PaginationBar from '@/components/shared/PaginationBar.vue';
import { Button } from '@/components/ui/button';
import { useUi } from '@/composables/useUi';
import type { PaginatedResponse } from '@/types/api';
import type { Todo } from '@/types/models';

const props = defineProps<{
    pagination: PaginatedResponse<Todo>;
    processing: boolean;
}>();
const emit = defineEmits<{ navigate: [processing: boolean] }>();
const { formatNumber, t } = useUi();
const rangeSummary = computed(() =>
    t('tasks.pagination.range', {
        from: formatNumber(props.pagination.meta.from ?? 0),
        to: formatNumber(props.pagination.meta.to ?? 0),
        total: formatNumber(props.pagination.meta.total),
    }),
);

function preventWhileProcessing(): boolean {
    return !props.processing;
}
</script>

<template>
    <PaginationBar
        v-if="pagination.meta.last_page > 1"
        class="mt-5 border-t border-border/70 pt-4"
        :label="t('tasks.pagination.label')"
        :summary="rangeSummary"
    >
        <Button
            v-if="pagination.links.prev"
            as-child
            variant="outline"
            size="sm"
            class="min-h-12 pointer-coarse:min-h-13"
        >
            <Link
                :href="pagination.links.prev"
                :only="['todos', 'filters', 'stats']"
                preserve-scroll
                preserve-state
                :aria-disabled="processing"
                @before="preventWhileProcessing"
                @start="emit('navigate', true)"
                @finish="emit('navigate', false)"
            >
                <ChevronLeft class="size-4" aria-hidden="true" />
                {{ t('tasks.pagination.previous') }}
            </Link>
        </Button>
        <Button
            v-else
            variant="outline"
            size="sm"
            class="min-h-12 pointer-coarse:min-h-13"
            disabled
        >
            <ChevronLeft class="size-4" aria-hidden="true" />
            {{ t('tasks.pagination.previous') }}
        </Button>
        <Button
            v-if="pagination.links.next"
            as-child
            variant="outline"
            size="sm"
            class="min-h-12 pointer-coarse:min-h-13"
        >
            <Link
                :href="pagination.links.next"
                :only="['todos', 'filters', 'stats']"
                preserve-scroll
                preserve-state
                :aria-disabled="processing"
                @before="preventWhileProcessing"
                @start="emit('navigate', true)"
                @finish="emit('navigate', false)"
            >
                {{ t('tasks.pagination.next') }}
                <ChevronRight class="size-4" aria-hidden="true" />
            </Link>
        </Button>
        <Button
            v-else
            variant="outline"
            size="sm"
            class="min-h-12 pointer-coarse:min-h-13"
            disabled
        >
            {{ t('tasks.pagination.next') }}
            <ChevronRight class="size-4" aria-hidden="true" />
        </Button>
    </PaginationBar>
</template>
