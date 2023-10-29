<template>
	<!-- <button class="main-button w-fit" >Add New User</button> -->
	<i
		class="pi pi-user-plus cursor-pointer text-xl"
		@click="
			editDialog = true;
			programsStore.setSelectedProgram(props.program);
		"
	></i>

	<Dialog
		v-model:visible="editDialog"
		modal
		:header="'Add User To: ' + programsStore.selectedProgram.title"
		class="w-full md:w-[50vw]"
	>
		<form @submit.prevent="addUser()">
			<div class="grid grid-cols-1 mb-5 mt-5">
				<div class="input-group">
					<label for="user">User</label>

					<Dropdown
						:disabled="loading"
						:class="{ 'p-invalid': validate.add.user_id.$error }"
						v-model="inputs.add.user_id"
						:options="programsStore.usersList"
						optionLabel="name"
						optionValue="id"
						placeholder="Select User To Add"
						class="w-full"
					/>
					<template v-if="validate.add.user_id.$errors">
						<span class="error-msg" v-for="(error, index) in validate.add.user_id.$errors" :key="index">
							{{ error.$message }}
						</span>
					</template>
				</div>
			</div>
			<button :disabled="loading" type="submit" class="main-button indigo w-full">
				{{ loading ? 'Loading...' : 'Add User' }}
			</button>
		</form>
	</Dialog>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import { useVuelidate } from '@vuelidate/core';
import { required, helpers, email, sameAs } from '@vuelidate/validators';
import { useProgramsStore } from '@/stores/programs';
import { useToast } from 'primevue/usetoast';
import programsApi from '@/controllers/programs';

const props = defineProps(['program']);
const emit = defineEmits(['success']);
const editDialog = ref(false);
const programsStore = useProgramsStore();
const toast = useToast();
const loading = ref(false);

const inputs = reactive({
	add: {
		program_id: null,
		user_id: null,
	},
});
const $externalResults = reactive({
	add: {},
});

watch(
	() => programsStore.selectedProgram,
	(program) => {
		inputs.add.program_id = program.id;
	}
);

const rules = computed(() => ({
	add: {
		user_id: {
			required: helpers.withMessage('User Is Required', required),
		},
	},
}));

const validate = useVuelidate(rules, inputs, { $externalResults });

const addUser = () => {
	validate.value.$clearExternalResults();
	validate.value.add.$touch();
	if (!validate.value.add.$error) {
		$externalResults.add = {};
		loading.value = true;

		programsApi
			.addUsers(inputs.add)
			.then((response) => {
				console.log(response);
				toast.add({
					severity: 'success',
					detail: response.message,
					life: 3000,
				});
				emit('success');
				editDialog.value = false;
				inputs.edit.user_id = null;

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
