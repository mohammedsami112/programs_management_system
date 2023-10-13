import Api from '@/Api';
import { useUsersStore } from '@/stores/users';
const usersStore = useUsersStore();

export default {
	getUsers() {
		let limit = usersStore.filters.limit,
			search = usersStore.filters.search,
			permission = usersStore.filters.permission,
			leader = usersStore.filters.leader,
			trash = usersStore.filters.trash,
			sort = { column: usersStore.filters.sort.column, sort: usersStore.filters.sort.sort },
			page = usersStore.filters.page;
		return Api.get('/users', {
			params: {
				limit: limit,
				search: search,
				permission: permission,
				leader: leader,
				trash: trash,
				sort_column: sort.column,
				sort: sort.sort,
				page: page,
			},
		}).then((response) => response.data);
	},
	getUsersFormInit() {
		return Api.get('/users/form-init').then((response) => response.data);
	},
	createUser(inputs) {
		let formData = new FormData();

		for (const key in inputs) {
			formData.append(key, inputs[key]);
		}

		return Api.post('/users/create', formData, { headers: { 'Content-Type': 'multipart/form-data' } }).then(
			(response) => response.data
		);
	},
	editUser(inputs) {
		let formData = new FormData();

		for (const key in inputs) {
			formData.append(key, inputs[key]);
		}

		return Api.post('/users/update', formData, { headers: { 'Content-Type': 'multipart/form-data' } }).then(
			(response) => response.data
		);
	},

	deleteUser(userId) {
		return Api.post(`/users/delete/${userId}`).then((response) => response.data);
	},
	restoreUser(userId) {
		return Api.post(`/users/restore/${userId}`).then((response) => response.data);
	},
};
