import { defineStore } from 'pinia';

export const useGlobalStore = defineStore('global-store', {
	state: () => ({
		home: [],
	}),

	actions: {
		setHome(payload) {
			this.home = payload;
		},
	},
});
