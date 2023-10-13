import { defineStore } from 'pinia';

export const useUsersStore = defineStore('users-store', {
	state: () => ({
		filters: {
			search: null,
			permission: null,
			leader: null,
			trash: null,
			sort: {
				column: null,
				sort: null,
			},
			limit: 10,
			page: 1,
		},
		formInit: {},
		selectedUser: {},
		users: [],
	}),

	actions: {
		setUsers(payload) {
			this.users = payload;
		},
		setFormInit(payload) {
			this.formInit = payload;
		},
		setSelectedUser(payload) {
			this.selectedUser = payload;
		},
	},
});
