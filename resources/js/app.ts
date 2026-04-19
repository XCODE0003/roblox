import '../css/app.css';
import { createApp, h, type DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

const appName = import.meta.env.VITE_APP_NAME || 'CopyHelper';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => {
        const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue');
        const importPage = pages[`./pages/${name}.vue`];

        if (!importPage) {
            throw new Error(`Unknown page: ${name}. Expected resources/js/pages/${name}.vue`);
        }

        return importPage();
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
