import Api from '@/Api';

export default {
	login(inputs) {
		let formData = new FormData();
		for (const key in inputs) {
			formData.append(key, inputs[key]);
		}

		return Api.post('/auth/login', formData).then((response) => response.data);
	},
	logout() {
		return Api.post('/auth/logout').then((response) => response.data);
	},
};
