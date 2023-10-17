import { defineStore } from 'pinia';

export const useLogsStore = defineStore('logs-store', {
	state: () => ({
		filters: {
			search: null,
			sort: {
				column: null,
				sort: null,
			},
			limit: 10,
			page: 1,
		},
		logs: [],
	}),

	actions: {
		setLogs(payload) {
			this.logs = payload;
		},
	},
});
