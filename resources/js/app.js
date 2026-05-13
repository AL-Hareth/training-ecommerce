import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import Layout from "./Layout.vue";
import AdminDashboardLayout from "./AdminDashboardLayout.vue";

createInertiaApp({
    layout:  name => {
        if (name.startsWith('Admin/')) {
            return AdminDashboardLayout;
        }

        return Layout;
    },
    resolve: name => {
        // This tells Vite where to find your Vue page components
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        return pages[`./Pages/${name}.vue`]
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el)
    },
})
