import { createApp } from 'vue';
import OtcApp from './components/Otc/OtcApp.vue';

import Quasar from 'quasar/dist/quasar.umd.prod';
import 'quasar/dist/quasar.prod.css';

import Highcharts from 'highcharts';
import stockInit from 'highcharts/modules/stock';
stockInit(Highcharts)

const quasarOptions = {
    config: {
        loadingBar: {
            // disable loading bar on top of page
            skipHijack: true
        }
    }
};

const app = createApp(OtcApp);
app.use(Quasar, quasarOptions);
app.mount("#otc-app");