import { defineStore } from 'pinia';
import authApi from '@/controllers/auth';
export const useUserStore = defineStore('user-store', {
	state: () => ({
		permissions: JSON.parse(localStorage.getItem('abilities')) || [],
		user: {},
	}),

	actions: {
		login(data) {
			localStorage.setItem('user_data', JSON.stringify(data.user));
			localStorage.setItem('access_token', data.token);
			this.user = data.user;
		},
		logout() {
			localStorage.removeItem('user_date');
			localStorage.removeItem('access_token');
			localStorage.removeItem('abilities');
			this.user = {};
		},
		setUserData() {
			this.user = JSON.parse(localStorage.getItem('user_data'));
		},
		getAbilities() {
			authApi
				.abilities()
				.then((response) => {
					localStorage.setItem('abilities', JSON.stringify(response.data));
					this.permissions = response.data;
				})
				.finally(() => {
					true;
				});
		},
	},
});
