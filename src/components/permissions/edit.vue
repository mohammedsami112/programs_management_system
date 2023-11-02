<template>
	<!-- <button class="main-button w-fit" >Add New User</button> -->
	<i class="pi pi-file-edit cursor-pointer text-xl" @click="editDialog = true"></i>

	<Dialog
		v-model:visible="editDialog"
		modal
		:header="'Edit Permission: ' + props.permission.title"
		class="w-full md:w-[50vw]"
	>
		<form @submit.prevent="editPermission()">
			<div class="grid grid-cols-1 mb-5 mt-5">
				<div class="input-group">
					<label for="title">Title</label>
					<input
						:disabled="loading"
						:class="{ invalid: validate.edit.title.$error }"
						type="text"
						id="title"
						placeholder="Enter The Title"
						v-model="inputs.edit.title"
					/>
					<template v-if="validate.edit.title.$errors">
						<span class="error-msg" v-for="(error, index) in validate.edit.title.$errors" :key="index">
							{{ error.$message }}
						</span>
					</template>
				</div>
				<div class="input-group">
					<label for="permissions">Permissions</label>

					<MultiSelect
						id="permissions"
						:disabled="loading"
						v-model="inputs.edit.permissions"
						:options="permissionsStore.permissions_list"
						optionLabel="label"
						optionValue="value"
						optionGroupLabel="label"
						optionGroupChildren="items"
						placeholder="Select Permissions"
						class="w-full"
						:class="{ 'p-invalid': validate.edit.permissions.$error }"
					>
					</MultiSelect>
					<template v-if="validate.edit.permissions.$errors">
						<span
							class="error-msg"
							v-for="(error, index) in validate.edit.permissions.$errors"
							:key="index"
						>
							{{ error.$message }}
						</span>
					</template>
				</div>
			</div>
			<button :disabled="loading" type="submit" class="main-button indigo w-full">
				{{ loading ? 'Loading...' : 'Update Permission' }}
			</button>
		</form>
	</Dialog>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import Dialog from 'primevue/dialog';
import MultiSelect from 'primevue/multiselect';
import { useVuelidate } from '@vuelidate/core';
import { required, helpers, requiredIf } from '@vuelidate/validators';
import { usePermissionsStore } from '@/stores/permissions';
import { useUserStore } from '@/stores/user';
import { useToast } from 'primevue/usetoast';
import permissionsApi from '@/controllers/permissions';

const props = defineProps(['permission']);
const emit = defineEmits(['success']);
const editDialog = ref(false);
const permissionsStore = usePermissionsStore();
const userStore = useUserStore();
const toast = useToast();
const loading = ref(false);

const inputs = reactive({
	edit: {
		item_id: null,
		title: null,
		permissions: [],
	},
});
const $externalResults = reactive({
	edit: {},
});

watch(
	() => editDialog.value,
	(value) => {
		if (value == true) {
			inputs.edit.item_id = props.permission.id;
			inputs.edit.title = props.permission.title;
			inputs.edit.permissions = props.permission.permissions.split(',');
		}
	}
);

const rules = computed(() => ({
	edit: {
		title: {
			required: helpers.withMessage('Title Is Required', required),
		},
		permissions: {
			required: helpers.withMessage('Permissions Is Required', required),
		},
	},
}));

const validate = useVuelidate(rules, inputs, { $externalResults });

const editPermission = () => {
	validate.value.$clearExternalResults();
	validate.value.edit.$touch();
	if (!validate.value.edit.$error) {
		$externalResults.edit = {};
		loading.value = true;
		// let permissions = [];
		// inputs.edit.permissions.forEach((permission) => {
		// 	if (permission.includes('specific')) {
		// 		permission = `${permission}-${inputs.edit.users.join('+')}`;
		// 	}
		// 	permissions.push(permission);
		// });
		// inputs.edit.permissions = permissions;

		permissionsApi
			.editPermission(inputs.edit)
			.then((response) => {
				console.log(response);
				toast.add({
					severity: 'success',
					detail: response.message,
					life: 3000,
				});
				userStore.getAbilities();
				emit('success');
				editDialog.value = false;
				inputs.edit.title = inputs.edit.permissions = null;

				validate.value.edit.$reset();
			})
			.catch((error) => {
				console.log(error);
				toast.add({
					severity: 'error',
					detail: error.response.data.message,
					life: 3000,
				});
				$externalResults.edit = error.response.data.data;
			})
			.finally(() => {
				loading.value = false;
			});
	}
};
</script>
