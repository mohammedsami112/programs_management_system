import 'primeicons/primeicons.css';
import 'primevue/resources/primevue.min.css';
import 'primevue/resources/themes/tailwind-light/theme.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { useUserStore } from '@/stores/user';
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';

import App from './App.vue';
import router from './router';

const app = createApp(App);

app.config.globalProperties.$canAccess = (permission) => {
	// let user = JSON.parse(localStorage.getItem('user_data'));
	// let userPermissions = user.permission.permissions.split(',');
	const userStore = useUserStore();
	return permission == null
		? true
		: userStore.permissions.filter((accessPermission) => accessPermission == permission).length > 0;
};

app.use(createPinia());
app.use(router);
app.use(PrimeVue);
app.use(ToastService);
app.use(ConfirmationService);

app.mount('#app');
