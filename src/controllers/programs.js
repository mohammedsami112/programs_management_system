import Api from '@/Api';
import { useProgramsStore } from '@/stores/programs';
const programsStore = useProgramsStore();

export default {
	getPrograms() {
		let limit = programsStore.filters.limit,
			search = programsStore.filters.search,
			trash = programsStore.filters.trash,
			sort = { column: programsStore.filters.sort.column, sort: programsStore.filters.sort.sort },
			page = programsStore.filters.page;
		return Api.get('/programs', {
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
	getUsers() {
		return Api.get('/programs/users-list').then((response) => response.data);
	},
	createProgram(inputs) {
		let formData = new FormData();

		for (const key in inputs) {
			formData.append(key, inputs[key]);
		}

		return Api.post('/programs/create', formData, { headers: { 'Content-Type': 'multipart/form-data' } }).then(
			(response) => response.data
		);
	},
	editPrograms(inputs) {
		let formData = new FormData();

		for (const key in inputs) {
			formData.append(key, inputs[key]);
		}

		return Api.post('/programs/update', formData, { headers: { 'Content-Type': 'multipart/form-data' } }).then(
			(response) => response.data
		);
	},
	regenerateKeys(programId) {
		return Api.post('/programs/keys/regenerate', { item_id: programId }).then((response) => response.data);
	},

	addUsers(inputs) {
		return Api.post('/programs/users/add', inputs).then((response) => response.data);
	},
	deleteUsers(inputs) {
		return Api.post('/programs/users/delete', inputs).then((response) => response.data);
	},

	deleteProgram(programId) {
		return Api.post(`/programs/delete/${programId}`).then((response) => response.data);
	},
	forceDeleteProgram(programId) {
		return Api.post(`/programs/force_delete/${programId}`).then((response) => response.data);
	},
	restoreProgram(programId) {
		return Api.post(`/programs/restore/${programId}`).then((response) => response.data);
	},
	uploadFiles(inputs) {
		let formData = new FormData();

		for (const key in inputs) {
			formData.append(key, inputs[key]);
		}

		return Api.post('/programs/upload-files', formData).then((response) => response.data);
	},
	getGeneralKeys() {
		return Api.get('/programs/general-keys').then((response) => response.data);
	},
};
