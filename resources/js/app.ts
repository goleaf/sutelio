import { axiosAdapter } from '@inertiajs/core';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { createApp, h } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Sutelio';

createInertiaApp({
    http: axiosAdapter(),
    title: (title) => (title ? `${title} - ${appName}` : appName),
    setup({ el, App, props, plugin }) {
        if (!el) {
            throw new Error('Inertia root element was not found.');
        }

        const pinia = createPinia();
        const app = createApp({ render: () => h(App, props) });
        app.use(plugin);
        app.use(pinia);
        app.mount(el);
    },
    layout: (name) => {
        switch (true) {
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#FF6038',
    },
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
