<template>
	<div class="programs-page">
		<DataTable
			:loading="loading"
			:value="programsStore.programs.data"
			lazy
			paginator
			:rows="programsStore.filters.limit"
			:rowsPerPageOptions="[10, 20, 50]"
			:totalRecords="programsStore.programs.total"
			@page="onPage"
			@sort="onSort"
			tableStyle="min-width: 50rem"
		>
			<template #header>
				<div class="table-actions flex justify-between items-center flex-col md:flex-row">
					<div class="actions mb-4" v-if="$canAccess('programs_create')">
						<createProgram @success="getPrograms()"></createProgram>
					</div>
					<div class="filters">
						<div class="flex items-center mb-3">
							<div class="input-group w-[250px]">
								<input
									:disabled="loading"
									type="text"
									id="search"
									placeholder="Search"
									v-model="programsStore.filters.search"
								/>
								<i
									@click="getPrograms()"
									class="pi pi-search flex items-center justify-center absolute cursor-pointer w-[40px] h-[40px] right-[5px] top-[5px]"
								></i>
							</div>
						</div>
						<div class="flex items-center justify-center">
							<InputSwitch
								:falseValue="null"
								v-model="programsStore.filters.trash"
								@change="getPrograms()"
							/>
							<span class="ml-3">Deleted Programs</span>
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
				<template v-if="header.field == 'users'" #body="{ data }">
					<Dropdown
						v-model="deleteUserId"
						:options="data.users"
						@update:modelValue="deleteUser(data.id)"
						optionLabel="user.name"
						optionValue="user.id"
						placeholder="Select User To Delete"
						class="w-full"
					/>
				</template>

				<template v-if="header.field == 'created_at'" #body="{ data }">
					{{ moment(data.created_at).format('YYYY-MM-DD h:m:s A') }}
				</template>
			</Column>
			<Column
				header="Actions"
				v-if="
					$canAccess('programs_delete') ||
					$canAccess('programs_restore') ||
					$canAccess('programs_update') ||
					$canAccess('programs_add_users') ||
					$canAccess('programs_access_keys')
				"
			>
				<template #body="{ data }">
					<div class="actions flex items-center justify-around">
						<accessKeys
							:program="data"
							v-if="data.deleted_at == null && $canAccess('programs_access_keys')"
						></accessKeys>
						<i
							:class="{ 'pi-trash': !deleteLoading, 'pi-spin pi-spinner': deleteLoading }"
							class="pi cursor-pointer text-xl"
							@click="deleteRecord(data.id)"
							v-if="data.deleted_at == null && $canAccess('programs_delete')"
						></i>
						<i
							v-else-if="data.deleted_at != null && $canAccess('programs_restore')"
							:class="{ 'pi-replay': !deleteLoading, 'pi-span pi-spinner': deleteLoading }"
							class="pi cursor-pointer text-xl"
							@click="restoreRecord(data.id)"
						></i>
						<editProgram
							:program="data"
							@success="getPrograms()"
							v-if="data.deleted_at == null && $canAccess('programs_update')"
						></editProgram>
						<addUser
							:program="data"
							@success="getPrograms()"
							v-if="data.deleted_at == null && $canAccess('programs_add_users')"
						></addUser>
					</div>
				</template>
			</Column>
		</DataTable>
	</div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dropdown from 'primevue/dropdown';
import InputSwitch from 'primevue/inputswitch';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';
import createProgram from '@/components/programs/create.vue';
import editProgram from '@/components/programs/edit.vue';
import addUser from '@/components/programs/addUser.vue';
import accessKeys from '@/components/programs/keys.vue';
import { useProgramsStore } from '@/stores/programs';
import programsApi from '@/controllers/programs';
import moment from 'moment';

const programsStore = useProgramsStore();
const toast = useToast();
const confirm = useConfirm();
const loading = ref(false);
const deleteLoading = ref(false);

const getPrograms = () => {
	loading.value = true;
	programsApi
		.getPrograms()
		.then((response) => {
			programsStore.setPrograms(response.data);
		})
		.finally(() => {
			loading.value = false;
		});
};

const headers = reactive([
	{
		title: 'id',
		field: 'id',
		sortable: true,
	},
	{
		title: 'Title',
		field: 'title',
		sortable: true,
	},
	{
		title: 'Users',
		field: 'users',
		sortable: false,
	},
	{
		title: 'Users Count',
		field: 'users_count',
		sortable: true,
	},
	{
		title: 'Added At',
		field: 'created_at',
		sortable: true,
	},
]);

// Change Page And Limit
const onPage = (event) => {
	console.log(event);
	let page = 1 + event.page;
	let limit = event.rows;

	programsStore.filters.limit = limit;
	programsStore.filters.page = page;

	getPrograms();
};

// Sorting
const onSort = (event) => {
	programsStore.filters.sort.column = event.sortField;
	programsStore.filters.sort.sort = event.sortOrder == -1 ? 'ASC' : 'DESC';
	getPrograms();
};

// Delete Program
const deleteRecord = (id) => {
	if (deleteLoading.value == false) {
		confirm.require({
			message: 'Do You Want To Delete This Record?',
			header: 'Delete Confirmation',
			icon: 'pi pi-info-circle',
			acceptClass: 'main-button danger',
			rejectClass: 'main-button default mr-3',
			accept: () => {
				deleteLoading.value = true;
				programsApi
					.deleteProgram(id)
					.then((response) => {
						toast.add({ severity: 'success', detail: response.message, life: 3000 });
						getPrograms();
					})
					.finally(() => {
						deleteLoading.value = false;
					});
			},
		});
	}
};

// Restore
const restoreRecord = (id) => {
	if (deleteLoading.value == false) {
		confirm.require({
			message: 'Do You Want To Restore This Record?',
			header: 'Restore Confirmation',
			icon: 'pi pi-info-circle',
			acceptClass: 'main-button indigo',
			rejectClass: 'main-button default mr-3',
			accept: () => {
				deleteLoading.value = true;
				programsApi
					.restoreProgram(id)
					.then((response) => {
						toast.add({ severity: 'success', detail: response.message, life: 3000 });
						getPrograms();
					})
					.finally(() => {
						deleteLoading.value = false;
					});
			},
		});
	}
};

// Delete Program
const deleteUserId = ref();
const deleteUser = (program_id) => {
	if (deleteLoading.value == false) {
		confirm.require({
			message: 'Do You Want To Delete This Record?',
			header: 'Delete Confirmation',
			icon: 'pi pi-info-circle',
			acceptClass: 'main-button danger',
			rejectClass: 'main-button default mr-3',
			accept: () => {
				deleteLoading.value = true;
				programsApi
					.deleteUsers({ program_id: program_id, user_id: deleteUserId.value })
					.then((response) => {
						toast.add({ severity: 'success', detail: response.message, life: 3000 });
						getPrograms();
					})
					.finally(() => {
						deleteLoading.value = false;
					});
			},
		});
	}
};

onMounted(() => {
	getPrograms();

	programsApi.getUsers().then((response) => {
		programsStore.setUsersList(response.data);
	});
});
</script>
