<template>
	<button class="main-button indigo w-fit" @click="createDialog = true">Add New Permission</button>

	<Dialog v-model:visible="createDialog" modal header="Create New Permission" class="w-full md:w-[50vw]">
		<form @submit.prevent="createPermission()">
			<div class="grid grid-cols-1 mb-5 mt-5">
				<div class="input-group">
					<label for="title">Title</label>
					<input
						:disabled="loading"
						:class="{ invalid: validate.create.title.$error }"
						type="text"
						id="title"
						placeholder="Enter The Title"
						v-model="inputs.create.title"
					/>
					<template v-if="validate.create.title.$errors">
						<span class="error-msg" v-for="(error, index) in validate.create.title.$errors" :key="index">
							{{ error.$message }}
						</span>
					</template>
				</div>
				<div class="input-group">
					<label for="permissions">Permissions</label>

					<MultiSelect
						filter
						id="permissions"
						:disabled="loading"
						v-model="inputs.create.permissions"
						:options="permissionsStore.permissions_list"
						optionLabel="label"
						optionValue="value"
						optionGroupLabel="label"
						optionGroupChildren="items"
						placeholder="Select Permissions"
						class="w-full"
						:class="{ 'p-invalid': validate.create.permissions.$error }"
					>
					</MultiSelect>
					<template v-if="validate.create.permissions.$errors">
						<span
							class="error-msg"
							v-for="(error, index) in validate.create.permissions.$errors"
							:key="index"
						>
							{{ error.$message }}
						</span>
					</template>
				</div>
			</div>
			<button :disabled="loading" type="submit" class="main-button indigo w-full">
				{{ loading ? 'Loading...' : 'Create New Permission' }}
			</button>
		</form>
	</Dialog>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import Dialog from 'primevue/dialog';
import MultiSelect from 'primevue/multiselect';
import { useVuelidate } from '@vuelidate/core';
import { required, helpers, requiredIf } from '@vuelidate/validators';
import { usePermissionsStore } from '@/stores/permissions';
import { useToast } from 'primevue/usetoast';
import permissionsApi from '@/controllers/permissions';

const emit = defineEmits(['success']);
const createDialog = ref(false);
const permissionsStore = usePermissionsStore();
const toast = useToast();
const loading = ref(false);

const inputs = reactive({
	create: {
		title: null,
		permissions: null,
	},
});
const $externalResults = reactive({
	create: {},
});

const rules = computed(() => ({
	create: {
		title: {
			required: helpers.withMessage('Title Is Required', required),
		},
		permissions: {
			required: helpers.withMessage('Permissions Is Required', required),
		},
	},
}));

const validate = useVuelidate(rules, inputs, { $externalResults });

const createPermission = () => {
	validate.value.$clearExternalResults();
	validate.value.create.$touch();
	if (!validate.value.create.$error) {
		$externalResults.create = {};
		loading.value = true;
		// let permissions = [];
		// inputs.create.permissions.forEach((permission) => {
		// 	if (permission.includes('specific')) {
		// 		permission = `${permission}-${inputs.create.users.join('+')}`;
		// 	}
		// 	permissions.push(permission);
		// });
		// inputs.create.permissions = permissions;
		permissionsApi
			.createPermission(inputs.create)
			.then((response) => {
				console.log(response);
				toast.add({
					severity: 'success',
					detail: response.message,
					life: 3000,
				});
				emit('success');
				createDialog.value = false;
				inputs.create.title = inputs.create.permissions = null;

				validate.value.create.$reset();
			})
			.catch((error) => {
				console.log(error);
				toast.add({
					severity: 'error',
					detail: error.response.data.message,
					life: 3000,
				});
				$externalResults.create = error.response.data.data;
			})
			.finally(() => {
				loading.value = false;
			});
	}
};
</script>
