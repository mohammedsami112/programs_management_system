import Api from '@/Api';

export default {
	getHome() {
		return Api.get('/home').then((response) => response.data);
	},
};
