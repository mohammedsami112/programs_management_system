import Api from '@/Api';
import { usePermissionsStore } from '@/stores/permissions';
const permissionsStore = usePermissionsStore();

export default {
	getPermissions() {
		let limit = permissionsStore.filters.limit,
			search = permissionsStore.filters.search,
			trash = permissionsStore.filters.trash,
			sort = { column: permissionsStore.filters.sort.column, sort: permissionsStore.filters.sort.sort },
			page = permissionsStore.filters.page;
		return Api.get('/permissions', {
			params: {
				limit: limit,
				search: search,
				trash: trash,
				sort_column: sort.column,
				sort: sort.sort,
				page: page,
			},
		}).then((response) => response.data);
	},
	createPermission(inputs) {
		let formData = new FormData();

		for (const key in inputs) {
			if (key == 'permissions') {
				inputs[key].join(',');
			}
			formData.append(key, inputs[key]);
		}

		return Api.post('/permissions/create', formData, { headers: { 'Content-Type': 'multipart/form-data' } }).then(
			(response) => response.data
		);
	},
	editPermission(inputs) {
		let formData = new FormData();

		for (const key in inputs) {
			if (key == 'permissions') {
				inputs[key].join(',');
			}
			formData.append(key, inputs[key]);
		}

		return Api.post('/permissions/update', formData, { headers: { 'Content-Type': 'multipart/form-data' } }).then(
			(response) => response.data
		);
	},

	deletePermission(permissionId) {
		return Api.post(`/permissions/delete/${permissionId}`).then((response) => response.data);
	},
	forceDeletePermission(permissionId) {
		return Api.post(`/permissions/force_delete/${permissionId}`).then((response) => response.data);
	},
	restorePermission(permissionId) {
		return Api.post(`/permissions/restore/${permissionId}`).then((response) => response.data);
	},
};
