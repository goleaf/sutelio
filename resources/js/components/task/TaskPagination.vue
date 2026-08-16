<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { useUi } from '@/composables/useUi';
import type { PaginatedResponse } from '@/types/api';
import type { Todo } from '@/types/models';

const props = defineProps<{
    pagination: PaginatedResponse<Todo>;
    processing: boolean;
}>();
const emit = defineEmits<{ navigate: [] }>();
const { formatNumber, t } = useUi();

function navigate(event: MouseEvent): void {
    if (props.processing) {
        event.preventDefault();

        return;
    }

    emit('navigate');
}
</script>

<template>
    <nav
        v-if="pagination.meta.last_page > 1"
        class="mt-5 flex flex-col gap-3 border-t border-border/70 pt-4 sm:flex-row sm:items-center sm:justify-between"
        :aria-label="t('tasks.pagination.label')"
    >
        <p class="text-sm text-muted-foreground">
            {{
                t('tasks.pagination.range', {
                    from: formatNumber(pagination.meta.from ?? 0),
                    to: formatNumber(pagination.meta.to ?? 0),
                    total: formatNumber(pagination.meta.total),
                })
            }}
        </p>
        <div class="flex gap-2">
            <Button
                v-if="pagination.links.prev"
                as-child
                variant="outline"
                size="sm"
                class="min-h-11"
            >
                <Link
                    :href="pagination.links.prev"
                    :only="['todos', 'filters', 'stats']"
                    preserve-scroll
                    preserve-state
                    :aria-disabled="processing"
                    @click="navigate"
                >
                    <ChevronLeft class="size-4" aria-hidden="true" />
                    {{ t('tasks.pagination.previous') }}
                </Link>
            </Button>
            <Button
                v-else
                variant="outline"
                size="sm"
                class="min-h-11"
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
                class="min-h-11"
            >
                <Link
                    :href="pagination.links.next"
                    :only="['todos', 'filters', 'stats']"
                    preserve-scroll
                    preserve-state
                    :aria-disabled="processing"
                    @click="navigate"
                >
                    {{ t('tasks.pagination.next') }}
                    <ChevronRight class="size-4" aria-hidden="true" />
                </Link>
            </Button>
            <Button
                v-else
                variant="outline"
                size="sm"
                class="min-h-11"
                disabled
            >
                {{ t('tasks.pagination.next') }}
                <ChevronRight class="size-4" aria-hidden="true" />
            </Button>
        </div>
    </nav>
</template>
