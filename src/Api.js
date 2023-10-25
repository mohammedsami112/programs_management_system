import axios from 'axios';

const Api = axios.create({
	baseURL: import.meta.env.VITE_API_URL,
	// withCredentials: true,
});

Api.interceptors.request.use(
	(config) => {
		config.headers['Access-Control-Allow-Origin'] = '*';
		config.headers['X-Requested-With'] = 'XMLHttpRequest';
		// config.headers['Content-Type'] = 'application/json';

		const access_token = localStorage.getItem('access_token');

		if (access_token) {
			config.headers['Authorization'] = `Bearer ${access_token}`;
		}

		// Remove Empty Params
		if (config.params != null) {
			for (const key in config.params) {
				if (config.params[key] == '' || config.params[key] == null) {
					config.params[key] == false ? '' : delete config.params[key];
				}
			}
		}

		// Remove Empty Payload
		if (
			config.data != null &&
			Array.from(config.data).filter(([key, value]) => value == '' || value == null || value == 'null').length > 0
		) {
			Array.from(config.data).filter(([key, value]) =>
				value == '' || value == null || value == 'null' ? config.data.delete(key) : ''
			);
		}

		return config;
	},
	(error) => Promise.reject(error)
);

Api.interceptors.response.use(
	(response) => {
		return response;
	},
	(error) => {
		if (error.response.status == 401 && error.config.url != '/auth/login') {
			localStorage.removeItem('access_token');
			localStorage.removeItem('user_data');

			window.location.href = '/';
		}

		if (error.response.status == 403) {
			window.location.href = '/dashboard';
		}

		return Promise.reject(error);
	}
);

export default Api;
