<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { TriangleAlert } from '@lucide/vue';
import { ref, useTemplateRef } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import PageConfirmPanel from '@/components/shared/PageConfirmPanel.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';

defineProps<{
    labels: {
        title: string;
        description: string;
        warning_title: string;
        warning_description: string;
        trigger: string;
        dialog_title: string;
        dialog_description: string;
        password: string;
        password_placeholder: string;
        cancel: string;
        confirm: string;
    };
}>();

const passwordInput = useTemplateRef('passwordInput');
const showDeleteConfirmation = ref(false);
</script>

<template>
    <div class="space-y-6">
        <Heading
            variant="small"
            :title="labels.title"
            :description="labels.description"
        />
        <Alert variant="destructive">
            <TriangleAlert aria-hidden="true" />
            <AlertTitle>{{ labels.warning_title }}</AlertTitle>
            <AlertDescription class="space-y-4">
                <p>{{ labels.warning_description }}</p>
                <Button
                    variant="destructive"
                    data-test="delete-user-button"
                    @click="showDeleteConfirmation = true"
                >
                    {{ labels.trigger }}
                </Button>
            </AlertDescription>
        </Alert>

        <Form
            v-bind="ProfileController.destroy.form()"
            reset-on-success
            disable-while-processing
            :options="{ preserveScroll: true }"
            v-slot="{ errors, processing, reset, clearErrors }"
            @error="() => passwordInput?.focus()"
        >
            <PageConfirmPanel
                :open="showDeleteConfirmation"
                :title="labels.dialog_title"
                :description="labels.dialog_description"
                :confirm-label="labels.confirm"
                :cancel-label="labels.cancel"
                :processing="processing"
                confirm-type="submit"
                destructive
                @update:open="
                    (open) => {
                        showDeleteConfirmation = open;
                        if (!open) {
                            clearErrors();
                            reset();
                        }
                    }
                "
            >
                <div class="grid max-w-xl gap-2">
                    <Label for="password" class="sr-only">
                        {{ labels.password }}
                    </Label>
                    <PasswordInput
                        id="password"
                        ref="passwordInput"
                        name="password"
                        :placeholder="labels.password_placeholder"
                        :aria-invalid="Boolean(errors.password)"
                    />
                    <InputError :message="errors.password" />
                </div>
            </PageConfirmPanel>
        </Form>
    </div>
</template>
