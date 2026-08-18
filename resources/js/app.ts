import { axiosAdapter } from '@inertiajs/core';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { createApp, Fragment, h } from 'vue';
import GlobalBusyOverlay from '@/components/shared/GlobalBusyOverlay.vue';
import NetworkStatusNotifier from '@/components/shared/NetworkStatusNotifier.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import OnboardingLayout from '@/layouts/OnboardingLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';
import {
    bindGlobalBusyToRouter,
    createGlobalBusyHttpClient,
    globalBusy,
} from '@/lib/globalBusy';

const appName = import.meta.env.VITE_APP_NAME || 'Sutelio';

bindGlobalBusyToRouter(router, globalBusy);

createInertiaApp({
    http: createGlobalBusyHttpClient(axiosAdapter(), globalBusy),
    title: (title) => (title ? `${title} - ${appName}` : appName),
    setup({ el, App, props, plugin }) {
        if (!el) {
            throw new Error('Inertia root element was not found.');
        }

        const pinia = createPinia();
        const app = createApp({
            render: () =>
                h(Fragment, null, [
                    h(App, props),
                    h(GlobalBusyOverlay),
                    h(NetworkStatusNotifier),
                ]),
        });
        app.use(plugin);
        app.use(pinia);
        app.mount(el);
    },
    layout: (name) => {
        switch (true) {
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('onboarding/'):
                return OnboardingLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: false,
});

initializeFlashToast();

router.on('success', (event) => {
    const currentLanguage = event.detail.page.props.localization.current;
    const language = ['en', 'lt', 'ru'].includes(currentLanguage)
        ? currentLanguage
        : 'en';

    document.documentElement.lang = language;
    document.documentElement.dir = 'ltr';
});
