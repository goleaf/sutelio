<script setup lang="ts">
import FirstRunLanguageDialog from '@/components/localization/FirstRunLanguageDialog.vue';
import { Toaster } from '@/components/ui/sonner';
</script>

<template>
    <div class="min-h-screen min-w-0 overflow-x-hidden bg-background">
        <main class="min-h-screen min-w-0">
            <slot />
        </main>

        <FirstRunLanguageDialog />
        <Toaster />
    </div>
</template>
