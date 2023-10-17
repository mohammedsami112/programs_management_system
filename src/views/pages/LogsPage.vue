<template>
	<div class="logs-page">
		<DataTable
			:loading="loading"
			:value="logsStore.logs.data"
			lazy
			paginator
			:rows="logsStore.filters.limit"
			:rowsPerPageOptions="[10, 20, 50]"
			:totalRecords="logsStore.logs.total"
			@page="onPage"
			@sort="onSort"
			tableStyle="min-width: 50rem"
		>
			<template #header>
				<div class="table-actions flex justify-between items-center">
					<div class="filters">
						<div class="flex items-center mb-3">
							<div class="input-group w-[250px]">
								<input
									:disabled="loading"
									type="text"
									id="search"
									placeholder="Search"
									v-model="logsStore.filters.search"
								/>
								<i
									@click="getPrograms()"
									class="pi pi-search flex items-center justify-center absolute cursor-pointer w-[40px] h-[40px] right-[5px] top-[5px]"
								></i>
							</div>
						</div>
					</div>
				</div>
			</template>

			<Column
				v-for="header in headers"
				:key="header"
				:sortable="header.sortable"
				:field="header.field"
				:header="header.title"
			>
				<template v-if="header.field == 'created_at'" #body="{ data }">
					{{ moment(data.created_at).format('YYYY-MM-DD h:m:s A') }}
				</template>
			</Column>
		</DataTable>
	</div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

import { useLogsStore } from '@/stores/logs';
import logsApi from '@/controllers/logs';
import moment from 'moment';

const logsStore = useLogsStore();
const loading = ref(false);

const getLogs = () => {
	loading.value = true;
	logsApi
		.getLogs()
		.then((response) => {
			logsStore.setLogs(response.data);
		})
		.finally(() => {
			loading.value = false;
		});
};

const headers = reactive([
	{
		title: 'id',
		field: 'id',
		sortable: false,
	},
	{
		title: 'User',
		field: 'user.username',
		sortable: false,
	},
	{
		title: 'Program',
		field: 'program.title',
		sortable: false,
	},
	{
		title: 'Device Name',
		field: 'device_name',
		sortable: false,
	},

	{
		title: 'Address',
		field: 'address',
		sortable: false,
	},
	{
		title: 'File',
		field: 'file',
		sortable: false,
	},
	{
		title: 'Mac Address',
		field: 'mac_address',
		sortable: false,
	},
	{
		title: 'Motherboard',
		field: 'motherboard',
		sortable: false,
	},
	{
		title: 'Description',
		field: 'description',
		sortable: false,
	},

	{
		title: 'Added At',
		field: 'created_at',
		sortable: false,
	},
]);

// Change Page And Limit
const onPage = (event) => {
	console.log(event);
	let page = 1 + event.page;
	let limit = event.rows;

	programsStore.filters.limit = limit;
	programsStore.filters.page = page;

	getLogs();
};

// Sorting
const onSort = (event) => {
	logsStore.filters.sort.column = event.sortField;
	logsStore.filters.sort.sort = event.sortOrder == -1 ? 'ASC' : 'DESC';
	getLogs();
};

onMounted(() => {
	getLogs();
});
</script>
