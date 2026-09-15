import { createApp } from 'vue';
import { VueQueryPlugin } from '@tanstack/vue-query';

import App from '@/App.vue';
import { queryClient } from '@/lib/query-client';
import router from '@/router';

const app = createApp(App);

app.use(VueQueryPlugin, { queryClient });
app.use(router);
app.mount('#app');
