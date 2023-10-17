import Api from '@/Api';
import { useLogsStore } from '@/stores/logs';
const logsStore = useLogsStore();

export default {
	getLogs() {
		let limit = logsStore.filters.limit,
			search = logsStore.filters.search,
			sort = { column: logsStore.filters.sort.column, sort: logsStore.filters.sort.sort },
			page = logsStore.filters.page;
		return Api.get('/logs', {
			params: {
				limit: limit,
				search: search,
				sort_column: sort.column,
				sort: sort.sort,
				page: page,
			},
		}).then((response) => response.data);
	},
};
