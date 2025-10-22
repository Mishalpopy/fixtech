import '../css/app.css';
import './bootstrap';

import '@/primevue/assets/styles.scss';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import { ToastService } from 'primevue';
import ConfirmationService from 'primevue/confirmationservice';
import Tooltip from 'primevue/tooltip';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob('./Pages/**/*.vue'),
    ),
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(PrimeVue, {
        theme: {
          preset: Aura,
          options: {
            darkModeSelector: '.app-dark',
            cssLayer: {
              name: 'primevue',
              order: 'tailwind-base, primevue, tailwind-utilities'
            }
          }
        },
      })
      .directive('click-outside', {
        beforeMount(el, binding) {
          el.clickOutsideEvent = function (event) {
            if (!(el === event.target || el.contains(event.target))) {
              if (typeof binding.value === 'function') {
                binding.value(event);
              } else {
                console.warn('v-click-outside expects a function as the binding value.');
              }
            }
          };
          document.addEventListener('click', el.clickOutsideEvent);
        },
        unmounted(el) {
          document.removeEventListener('click', el.clickOutsideEvent);
        },
      })
      .directive('tooltip', Tooltip)
      .use(ToastService)
      .use(ConfirmationService)
      .use(ZiggyVue)
      .mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});
