<template>
	<!-- <button class="main-button w-fit" >Add New User</button> -->
	<i
		class="pi pi-file-edit cursor-pointer text-xl"
		@click="
			editDialog = true;
			programsStore.setSelectedProgram(props.program);
		"
	></i>

	<Dialog
		v-model:visible="editDialog"
		modal
		:header="'Edit Program: ' + programsStore.selectedProgram.title"
		class="w-full md:w-[50vw]"
	>
		<form @submit.prevent="editPrograms()">
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
			</div>
			<button :disabled="loading" type="submit" class="main-button indigo w-full">
				{{ loading ? 'Loading...' : 'Update Program' }}
			</button>
		</form>
	</Dialog>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import Dialog from 'primevue/dialog';
import MultiSelect from 'primevue/multiselect';
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
	edit: {
		item_id: null,
		title: null,
	},
});
const $externalResults = reactive({
	edit: {},
});

watch(
	() => programsStore.selectedProgram,
	(program) => {
		inputs.edit.item_id = program.id;
		inputs.edit.title = program.title;
	}
);

const rules = computed(() => ({
	edit: {
		title: {
			required: helpers.withMessage('Title Is Required', required),
		},
	},
}));

const validate = useVuelidate(rules, inputs, { $externalResults });

const editPrograms = () => {
	validate.value.$clearExternalResults();
	validate.value.edit.$touch();
	if (!validate.value.edit.$error) {
		$externalResults.edit = {};
		loading.value = true;

		programsApi
			.editPrograms(inputs.edit)
			.then((response) => {
				console.log(response);
				toast.add({
					severity: 'success',
					detail: response.message,
					life: 3000,
				});
				emit('success');
				editDialog.value = false;
				inputs.edit.title = null;

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
