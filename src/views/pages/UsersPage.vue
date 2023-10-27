<template>
	<div class="users-page">
		<DataTable
			:loading="loading"
			:value="usersStore.users.data"
			lazy
			paginator
			scrollable
			:rows="usersStore.filters.limit"
			:rowsPerPageOptions="[10, 20, 50]"
			:totalRecords="usersStore.users.total"
			@page="onPage"
			@sort="onSort"
			tableStyle="min-width: 50rem"
		>
			<template #header>
				<div class="table-actions flex justify-between items-center flex-col md:flex-row">
					<div class="actions mb-4" v-if="$canAccess('users_create')">
						<createUser @success="getUsers()"></createUser>
					</div>
					<div class="filters">
						<div class="flex items-center flex-col md:flex-row mb-3">
							<div class="input-group w-[250px]">
								<input
									:disabled="loading"
									type="text"
									id="search"
									placeholder="Search"
									v-model="usersStore.filters.search"
								/>
								<i
									@click="getUsers()"
									class="pi pi-search flex items-center justify-center absolute cursor-pointer w-[40px] h-[40px] right-[5px] top-[5px]"
								></i>
							</div>
							<Dropdown
								v-model="usersStore.filters.permission"
								@change="getUsers()"
								:options="usersStore.formInit.permissions"
								:loading="formInitLoading"
								showClear
								optionLabel="title"
								optionValue="id"
								placeholder="Filter By Permission"
								class="w-[250px] mt-3 md:mt-0 md:ml-3"
							/>
						</div>
						<div class="flex items-center justify-center">
							<InputSwitch :falseValue="null" v-model="usersStore.filters.trash" @change="getUsers()" />
							<span class="ml-3">Deleted Users</span>
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
				headerClass="w-[200px]"
				style="width: 200px"
			>
				<template v-if="header.field == 'permission'" #body="{ data }">
					{{ data.user_permission.title }}
				</template>
				<template v-if="header.field == 'created_at'" #body="{ data }">
					{{ moment(data.created_at).format('YYYY-MM-DD h:m:s A') }}
				</template>
			</Column>
			<Column
				header="Actions"
				v-if="
					$canAccess('users_delete') ||
					$canAccess('users_restore') ||
					$canAccess('users_update') ||
					$canAccess('users_force_delete')
				"
			>
				<template #body="{ data }">
					<div class="actions flex items-center justify-around">
						<i
							:class="{ 'pi-trash': !deleteLoading, 'pi-spin pi-spinner': deleteLoading }"
							class="pi cursor-pointer text-xl"
							@click="deleteRecord(data.id)"
							v-if="data.deleted_at == null && $canAccess('users_delete')"
						></i>
						<i
							:class="{ 'pi-trash': !deleteLoading, 'pi-spin pi-spinner': deleteLoading }"
							class="pi cursor-pointer text-xl"
							@click="forceDeleteRecord(data.id)"
							v-if="data.deleted_at != null && $canAccess('users_force_delete')"
						></i>
						<i
							v-if="data.deleted_at != null && $canAccess('users_restore')"
							:class="{ 'pi-replay': !deleteLoading, 'pi-span pi-spinner': deleteLoading }"
							class="pi cursor-pointer text-xl"
							@click="restoreRecord(data.id)"
						></i>
						<editUser
							:user="data"
							@success="getUsers()"
							v-if="data.deleted_at == null && $canAccess('users_update')"
						></editUser>
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
import createUser from '@/components/users/create.vue';
import editUser from '@/components/users/edit.vue';
import { useUsersStore } from '@/stores/users';
import usersApi from '@/controllers/users';
import moment from 'moment';

const usersStore = useUsersStore();
const toast = useToast();
const confirm = useConfirm();
const loading = ref(false);
const deleteLoading = ref(false);
const formInitLoading = ref(true);

const getUsers = () => {
	loading.value = true;
	usersApi
		.getUsers()
		.then((response) => {
			console.log(response);
			usersStore.setUsers(response.data);
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
		title: 'Name',
		field: 'name',
		sortable: true,
	},
	{
		title: 'Username',
		field: 'username',
		sortable: true,
	},
	{
		title: 'Email',
		field: 'email',
		sortable: false,
	},
	{
		title: 'Permission',
		field: 'permission',
		sortable: false,
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

	usersStore.filters.limit = limit;
	usersStore.filters.page = page;

	getUsers();
};

// Sorting
const onSort = (event) => {
	usersStore.filters.sort.column = event.sortField;
	usersStore.filters.sort.sort = event.sortOrder == -1 ? 'ASC' : 'DESC';
	getUsers();
};

// Delete
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
				usersApi
					.deleteUser(id)
					.then((response) => {
						toast.add({ severity: 'success', detail: response.message, life: 3000 });
						getUsers();
					})
					.finally(() => {
						deleteLoading.value = false;
					});
			},
		});
	}
};

// Force
const forceDeleteRecord = (id) => {
	if (deleteLoading.value == false) {
		confirm.require({
			message: 'Do You Want To Delete This Record?',
			header: 'Delete Confirmation',
			icon: 'pi pi-info-circle',
			acceptClass: 'main-button danger',
			rejectClass: 'main-button default mr-3',
			accept: () => {
				deleteLoading.value = true;
				usersApi
					.forceDeleteUser(id)
					.then((response) => {
						toast.add({ severity: 'success', detail: response.message, life: 3000 });
						getUsers();
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
				usersApi
					.restoreUser(id)
					.then((response) => {
						toast.add({ severity: 'success', detail: response.message, life: 3000 });
						getUsers();
					})
					.finally(() => {
						deleteLoading.value = false;
					});
			},
		});
	}
};

onMounted(() => {
	getUsers();

	usersApi
		.getUsersFormInit()
		.then((response) => {
			usersStore.setFormInit(response.data);
		})
		.finally(() => {
			formInitLoading.value = false;
		});
});
</script>
