import { defineStore } from 'pinia';

export const useUserStore = defineStore('user-store', {
	state: () => ({
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
			this.user = {};
		},
		setUserData() {
			this.user = JSON.parse(localStorage.getItem('user_data'));
		},
	},
});
