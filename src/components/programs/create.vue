<template>
	<button class="main-button indigo w-fit" @click="createDialog = true">Add New Program</button>

	<Dialog v-model:visible="createDialog" modal header="Create New Program" :style="{ width: '50vw' }">
		<form @submit.prevent="createProgram()">
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
			</div>
			<button :disabled="loading" type="submit" class="main-button indigo w-full">
				{{ loading ? 'Loading...' : 'Create New Program' }}
			</button>
		</form>
	</Dialog>
</template>

<script setup>
import { ref, reactive, computed, defineEmits } from 'vue';
import Dialog from 'primevue/dialog';
import MultiSelect from 'primevue/multiselect';
import { useVuelidate } from '@vuelidate/core';
import { required, helpers, email, sameAs } from '@vuelidate/validators';
import { useToast } from 'primevue/usetoast';
import programsApi from '@/controllers/programs';

const emit = defineEmits(['success']);
const createDialog = ref(false);
const toast = useToast();
const loading = ref(false);

const inputs = reactive({
	create: {
		title: null,
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
	},
}));

const validate = useVuelidate(rules, inputs, { $externalResults });

const createProgram = () => {
	validate.value.$clearExternalResults();
	validate.value.create.$touch();
	if (!validate.value.create.$error) {
		$externalResults.create = {};
		loading.value = true;
		programsApi
			.createProgram(inputs.create)
			.then((response) => {
				console.log(response);
				toast.add({
					severity: 'success',
					detail: response.message,
					life: 3000,
				});
				emit('success');
				createDialog.value = false;
				inputs.create.title = null;

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
