import "./bootstrap";
import { createInertiaApp } from "@inertiajs/vue3";
import { createApp, h } from "vue";
import "../css/app.css";
import "vue-select/dist/vue-select.css";

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob("./Pages/**/*.vue", { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});