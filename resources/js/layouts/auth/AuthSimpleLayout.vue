<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import LanguageSwitcher from '@/components/localization/LanguageSwitcher.vue';
import { useUi } from '@/composables/useUi';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();

const { t } = useUi();
</script>

<template>
    <main
        class="ui-page-surface relative flex min-h-svh flex-col items-center justify-center overflow-hidden bg-muted/20 p-4 sm:p-6 md:p-10"
    >
        <div
            class="pointer-events-none absolute -top-32 -right-24 size-96 rounded-full border-[72px] border-orange-500/[0.055]"
            aria-hidden="true"
        />
        <div
            class="pointer-events-none absolute -bottom-40 -left-32 size-[30rem] rounded-full border-[88px] border-orange-500/[0.035]"
            aria-hidden="true"
        />

        <div class="absolute top-4 right-4 z-20 sm:top-6 sm:right-6">
            <LanguageSwitcher />
        </div>

        <div class="relative w-full max-w-md">
            <div
                class="overflow-hidden rounded-feature border border-border/80 bg-card shadow-[0_32px_90px_-54px_rgba(15,23,42,0.65)]"
            >
                <div
                    class="relative overflow-hidden border-b border-border/70 bg-muted/20 px-6 py-7 sm:px-9 sm:py-8"
                >
                    <span
                        class="absolute inset-y-0 left-0 w-1.5 bg-orange-500"
                        aria-hidden="true"
                    />
                    <span
                        class="absolute -right-9 -bottom-16 size-36 rounded-full border-[18px] border-orange-500/20 bg-orange-500/[0.05]"
                        aria-hidden="true"
                    />
                    <div class="relative flex items-start gap-4">
                        <Link
                            :href="home()"
                            aria-label="Sutelio"
                            class="flex size-12 shrink-0 items-center justify-center rounded-2xl focus-visible:ring-2 focus-visible:ring-brand-cobalt focus-visible:ring-offset-4 focus-visible:outline-hidden"
                        >
                            <AppLogoIcon class-name="size-12" />
                        </Link>
                        <div class="min-w-0 space-y-1.5 text-left">
                            <p
                                class="text-[0.7rem] font-semibold tracking-[0.16em] text-orange-700 uppercase"
                            >
                                {{ t('auth.common.eyebrow') }}
                            </p>
                            <h1
                                class="text-2xl font-semibold tracking-[-0.035em]"
                            >
                                {{ title }}
                            </h1>
                            <p
                                class="max-w-sm text-sm leading-6 text-muted-foreground"
                            >
                                {{ description }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-7 sm:px-9 sm:py-8">
                    <slot />
                </div>
            </div>
        </div>
    </main>
</template>
