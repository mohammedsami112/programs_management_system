<template>
	<div class="permissions-page">
		<DataTable
			:loading="loading"
			:value="permissionsStore.permissions.data"
			lazy
			paginator
			:rows="permissionsStore.filters.limit"
			:rowsPerPageOptions="[10, 20, 50]"
			:totalRecords="permissionsStore.permissions.total"
			@page="onPage"
			@sort="onSort"
			tableStyle="min-width: 50rem"
		>
			<template #header>
				<div class="table-actions flex justify-between items-center flex-col md:flex-row">
					<div class="actions mb-4" v-if="$canAccess('permissions_create')">
						<createPermissions @success="getPermissions()"></createPermissions>
					</div>
					<div class="filters">
						<div class="flex items-center mb-3">
							<div class="input-group w-[250px]">
								<input
									:disabled="loading"
									type="text"
									id="search"
									placeholder="Search"
									v-model="permissionsStore.filters.search"
								/>
								<i
									@click="getPermissions()"
									class="pi pi-search flex items-center justify-center absolute cursor-pointer w-[40px] h-[40px] right-[5px] top-[5px]"
								></i>
							</div>
						</div>
						<div class="flex items-center justify-center">
							<InputSwitch
								:falseValue="null"
								v-model="permissionsStore.filters.trash"
								@change="getPermissions()"
							/>
							<span class="ml-3">Deleted Permissions</span>
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
			<Column
				header="Actions"
				v-if="
					$canAccess('permissions_delete') ||
					$canAccess('permissions_restore') ||
					$canAccess('permissions_update')
				"
			>
				<template #body="{ data }">
					<div class="actions flex items-center justify-around">
						<i
							:class="{ 'pi-trash': !deleteLoading, 'pi-spin pi-spinner': deleteLoading }"
							class="pi cursor-pointer text-xl"
							@click="deleteRecord(data.id)"
							v-if="data.deleted_at == null && $canAccess('permissions_delete')"
						></i>
						<i
							v-else-if="data.deleted_at != null && $canAccess('permissions_restore')"
							:class="{ 'pi-replay': !deleteLoading, 'pi-span pi-spinner': deleteLoading }"
							class="pi cursor-pointer text-xl"
							@click="restoreRecord(data.id)"
						></i>
						<editPermission
							:permission="data"
							@success="getPermissions()"
							v-if="data.deleted_at == null && $canAccess('permissions_update')"
						></editPermission>
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
import createPermissions from '@/components/permissions/create.vue';
import editPermission from '@/components/permissions/edit.vue';
import { usePermissionsStore } from '@/stores/permissions';
import permissionsApi from '@/controllers/permissions';
import moment from 'moment';

const permissionsStore = usePermissionsStore();
const toast = useToast();
const confirm = useConfirm();
const loading = ref(false);
const deleteLoading = ref(false);

const getPermissions = () => {
	loading.value = true;
	permissionsApi
		.getPermissions()
		.then((response) => {
			permissionsStore.setPermissions(response.data);
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

	permissionsStore.filters.limit = limit;
	permissionsStore.filters.page = page;

	getPermissions();
};

// Sorting
const onSort = (event) => {
	permissionsStore.filters.sort.column = event.sortField;
	permissionsStore.filters.sort.sort = event.sortOrder == -1 ? 'ASC' : 'DESC';
	getPermissions();
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
				permissionsApi
					.deletePermission(id)
					.then((response) => {
						toast.add({ severity: 'success', detail: response.message, life: 3000 });
						getPermissions();
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
				permissionsApi
					.restorePermission(id)
					.then((response) => {
						toast.add({ severity: 'success', detail: response.message, life: 3000 });
						getPermissions();
					})
					.finally(() => {
						deleteLoading.value = false;
					});
			},
		});
	}
};

onMounted(() => {
	getPermissions();
});
</script>
