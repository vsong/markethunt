import { createApp } from 'vue';
import ItemSelector from './components/Chart/ItemSelector.vue';

import Quasar from 'quasar/dist/quasar.umd.prod';
import 'quasar/dist/quasar.prod.css';

const quasarOptions = {
    config: {
        loadingBar: {
            // disable loading bar on top of page
            skipHijack: true
        }
    }
};

const app = createApp(ItemSelector);
app.use(Quasar, quasarOptions);
app.mount("#item-selector-app");