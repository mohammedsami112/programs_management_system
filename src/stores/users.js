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
		specificationList: [
			{
				label: 'Specific Users',
				value: 'specific_users',
			},
			{
				label: 'Specific Users Logs',
				value: 'specific_logs_users',
			},
			{
				label: 'Specific Users Programs',
				value: 'specific_programs_users',
			},
		],
		formInit: {},
		selectedUser: {},
		usersList: [],
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
		setUsersList(payload) {
			this.usersList = payload;
		},
	},
});
